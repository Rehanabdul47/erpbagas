<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\Account;
use App\Traits\BranchScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    use BranchScope;
    public function index()
    {
        $query = PurchaseOrder::with(['supplier', 'user'])->latest();
        $this->scopeByUserBranch($query);
        $purchaseOrders = $query->get();
        return view('pages.purchase-orders.index', compact('purchaseOrders'))->with('activePage', 'purchase-orders');
    }

    public function create()
    {
        $suppliers = Supplier::all();
        // Removed products query, products will be fetched dynamically via AJAX based on selected supplier
        return view('pages.purchase-orders.create', compact('suppliers'))->with('activePage', 'purchase-orders');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.supplier_product_id' => 'required|exists:supplier_products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.cost_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Generate PO Number (PO-YYYYMMDD-XXXX)
            $dateStr = date('Ymd');
            $count = PurchaseOrder::whereDate('created_at', date('Y-m-d'))->count() + 1;
            $poNumber = 'PO-' . $dateStr . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'user_id' => Auth::id(),
                'date' => $validated['date'],
                'notes' => $validated['notes'],
                'status' => 'pending',
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['cost_price'];
                $po->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'supplier_product_id' => $item['supplier_product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'subtotal' => $subtotal,
                ]);
                $totalAmount += $subtotal;
            }

            $po->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'items.supplierProduct', 'items.product']);
        return view('pages.purchase-orders.show', compact('purchaseOrder'))->with('activePage', 'purchase-orders');
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate(['status' => 'required|in:ordered,received,cancelled']);
        
        // If status changing to received, update product stock and COGS
        if ($purchaseOrder->status !== 'received' && $request->status === 'received') {
            DB::transaction(function() use ($purchaseOrder) {
                foreach ($purchaseOrder->items as $item) {
                    $product = $item->product;
                    
                    if ($product) {
                        // Simple weighted average for COGS or just update to latest?
                        // For now, let's just update stock
                        $product->increment('stock', $item->quantity);
                        
                        // Record stock movement
                        \App\Models\StockMovement::create([
                            'product_id' => $product->id,
                            'user_id' => Auth::id(),
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'reference' => 'Purchase Order ' . $purchaseOrder->po_number,
                            'method' => 'Purchase Order',
                        ]);
                    }
                }
                $purchaseOrder->update(['status' => 'received']);

                // Record Finance Automation
                // 1. Cash Out
                CashTransaction::create([
                    'date' => Carbon::now(),
                    'type' => 'out',
                    'category' => 'Purchase',
                    'amount' => $purchaseOrder->total_amount,
                    'description' => "Pembelian PO #{$purchaseOrder->po_number}",
                    'reference' => $purchaseOrder->po_number,
                    'user_id' => Auth::id(),
                ]);

                // 2. Journal Entry
                $persediaanAccount = Account::where('code', '1-104')->first();
                $kasAccount = Account::where('code', '1-101')->first();

                if ($persediaanAccount && $kasAccount) {
                    $journalEntry = JournalEntry::create([
                        'date' => Carbon::now(),
                        'reference' => $purchaseOrder->po_number,
                        'description' => "Pembelian PO #{$purchaseOrder->po_number}",
                    ]);

                    // Debit: Persediaan (1201), Credit: Kas (1001)
                    $journalEntry->items()->createMany([
                        [
                            'account_id' => $persediaanAccount->id,
                            'debit' => $purchaseOrder->total_amount,
                            'credit' => 0,
                        ],
                        [
                            'account_id' => $kasAccount->id,
                            'debit' => 0,
                            'credit' => $purchaseOrder->total_amount,
                        ]
                    ]);
                }
            });
        } else {
            $purchaseOrder->update(['status' => $request->status]);
        }

        return back()->with('success', 'Status Purchase Order berhasil diperbarui.');
    }
}

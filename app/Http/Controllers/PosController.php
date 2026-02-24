<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\CashTransaction;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\RedeemCode;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::where('stock', '>', 0)->with('category')->get();
        $customers = Customer::all();
        $posHistory = Sale::where('invoice_no', 'like', 'POS-%')
            ->with(['customer', 'items.product'])
            ->latest()
            ->limit(20)
            ->get();
        
        return view('pages.pos.index', compact('products', 'customers', 'posHistory'))
            ->with('activePage', 'pos');
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'amount_paid' => 'required|numeric|min:0',
            'redeem_code' => 'nullable|string'
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $totalAmount = 0;
                $invoiceNo = 'POS-' . strtoupper(uniqid());
                
                $redeemCode = null;
                $discountAmount = 0;

                // Validate Redeem Code if provided
                if (!empty($validated['redeem_code'])) {
                    $customer = Customer::findOrFail($validated['customer_id']);
                    if (!$customer->hasCompletedPurchase()) {
                        throw new \Exception('This redeem code is only available for returning customers with previous purchases.');
                    }

                    $redeemCode = RedeemCode::where('code', $validated['redeem_code'])->lockForUpdate()->first();
                    if (!$redeemCode || !$redeemCode->isValid($validated['customer_id'])) {
                        throw new \Exception('Invalid, expired, or fully used redeem code.');
                    }
                }

                // 1. Create Sale
                $sale = Sale::create([
                    'customer_id' => $validated['customer_id'],
                    'invoice_no' => $invoiceNo,
                    'sale_date' => Carbon::now(),
                    'total_amount' => 0, // Update later
                    'discount_amount' => 0, // Update later
                    'status' => 'paid',
                    'notes' => 'POS Transaction via ' . $validated['payment_method'],
                    'redeem_code_id' => $redeemCode ? $redeemCode->id : null,
                ]);

                foreach ($validated['items'] as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                    
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok tidak mencukupi untuk produk: {$product->name}");
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $totalAmount += $subtotal;

                    // 2. Create Sale Item
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ]);

                    // 3. Decrement Inventory
                    $product->decrement('stock', $item['quantity']);

                    // 4. Log Stock Movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'type' => 'out',
                        'quantity' => $item['quantity'],
                        'reference' => "POS Invoice: {$invoiceNo}",
                        'method' => 'POS',
                    ]);
                }

                // Apply Discount
                if ($redeemCode) {
                    if ($redeemCode->type === 'percentage') {
                        $discountAmount = ($totalAmount * $redeemCode->value) / 100;
                    } else {
                        $discountAmount = $redeemCode->value;
                    }
                    
                    // Ensure discount doesn't exceed total amount
                    $discountAmount = min($discountAmount, $totalAmount);
                    $totalAmount -= $discountAmount;

                    // Increment usage
                    $redeemCode->increment('used_count');
                }

                // Update Sale Total
                $sale->update([
                    'total_amount' => $totalAmount,
                    'discount_amount' => $discountAmount
                ]);

                // 5. Record Cash Transaction
                CashTransaction::create([
                    'date' => Carbon::now(),
                    'type' => 'in',
                    'category' => 'Sales (POS)',
                    'amount' => $totalAmount,
                    'description' => "Penjualan POS #{$invoiceNo}",
                    'reference' => $invoiceNo,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil diproses',
                    'invoice_no' => $invoiceNo,
                    'total_amount' => $totalAmount,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}

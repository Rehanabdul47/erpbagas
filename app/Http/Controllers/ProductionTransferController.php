<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\Product;
use App\Models\Branch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductionTransferController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isExecutive = $user->hasRole('Executive');
        
        $query = StockTransfer::with(['sourceBranch', 'destinationBranch', 'product', 'user'])->latest();
        
        if (!$isExecutive) {
            $branchId = $user->branch_id ?? 1;
            $query->where(function($q) use ($branchId) {
                $q->where('source_branch_id', $branchId)
                  ->orWhere('destination_branch_id', $branchId);
            });
        }
        
        $transfers = $query->paginate(20);
        
        return view('pages.production.transfers.index', compact('transfers', 'isExecutive'))
            ->with('activePage', 'production-transfers');
    }

    public function create()
    {
        $user = Auth::user();
        $isExecutive = $user->hasRole('Executive');
        $branchId = $user->branch_id ?? 1;
        
        $productsQuery = Product::where('stock', '>', 0);
        
        if (!$isExecutive) {
            $productsQuery->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
            $branches = Branch::where('is_active', true)->where('id', '!=', $branchId)->get();
            $sourceBranches = Branch::where('id', $branchId)->get();
            // Fallback if branch_id is null/1 and no sourceBranch exists
            if ($sourceBranches->isEmpty()) {
                 $sourceBranches = Branch::where('id', 1)->get();
            }
        } else {
            $branches = Branch::where('is_active', true)->get();
            $sourceBranches = Branch::where('is_active', true)->get();
        }
        
        $products = $productsQuery->get();
        
        return view('pages.production.transfers.create', compact('products', 'branches', 'sourceBranches', 'isExecutive'))
            ->with('activePage', 'production-transfers');
    }

    public function store(Request $request)
    {
        $request->validate([
            'source_branch_id' => 'required|exists:branches,id',
            'destination_branch_id' => 'required|exists:branches,id|different:source_branch_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);

        $sourceProduct = Product::findOrFail($request->product_id);

        if ($sourceProduct->stock < $request->quantity) {
            return back()->with('error', 'Stok tidak mencukupi untuk transfer.');
        }

        DB::beginTransaction();
        try {
            // Deduct stock from source
            $sourceProduct->decrement('stock', $request->quantity);

            StockMovement::create([
                'product_id' => $sourceProduct->id,
                'user_id' => Auth::id(),
                'type' => 'out',
                'quantity' => $request->quantity,
                'reference' => 'Transfer Out to Branch ' . Branch::find($request->destination_branch_id)->name,
                'method' => 'Transfer',
            ]);

            // Find or create product in destination branch
            // We find by SKU and destination_branch_id
            $destinationProduct = Product::where('sku', $sourceProduct->sku)
                ->where('branch_id', $request->destination_branch_id)
                ->first();

            if (!$destinationProduct) {
                $destinationProduct = Product::create([
                    'sku' => $sourceProduct->sku,
                    'name' => $sourceProduct->name,
                    'category_id' => $sourceProduct->category_id,
                    'stock' => 0, // Will be incremented below
                    'safety_stock' => $sourceProduct->safety_stock,
                    'price' => $sourceProduct->price,
                    'cogs' => $sourceProduct->cogs,
                    'batch' => $sourceProduct->batch,
                    'location' => $sourceProduct->location,
                    'image' => $sourceProduct->image,
                    'type' => $sourceProduct->type,
                    'branch_id' => $request->destination_branch_id,
                ]);
            }

            // Record the transfer (initial status: pending)
            $reference = 'TRF-' . time() . '-' . rand(100, 999);
            StockTransfer::create([
                'reference' => $reference,
                'source_branch_id' => $request->source_branch_id,
                'destination_branch_id' => $request->destination_branch_id,
                'product_id' => $sourceProduct->id,
                'destination_product_id' => $destinationProduct->id,
                'quantity' => $request->quantity,
                'user_id' => Auth::id(),
                'notes' => $request->notes,
                'status' => StockTransfer::STATUS_PENDING,
            ]);

            DB::commit();

            return redirect()->route('production.transfers.index')->with('success', 'Transfer stok berhasil dibuat dan menunggu pengiriman.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat transfer: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, StockTransfer $transfer)
    {
        $request->validate([
            'status' => 'required|in:sending,received',
            'received_quantity' => 'nullable|integer|min:0',
            'arrival_notes' => 'nullable|string'
        ]);

        $newStatus = $request->status;

        DB::beginTransaction();
        try {
            if ($newStatus === StockTransfer::STATUS_SENDING && $transfer->status === StockTransfer::STATUS_PENDING) {
                $transfer->update(['status' => StockTransfer::STATUS_SENDING]);
            } elseif ($newStatus === StockTransfer::STATUS_RECEIVED && $transfer->status === StockTransfer::STATUS_SENDING) {
                // If quantity received is not provided, assume full quantity
                $actualReceived = $request->received_quantity ?? $transfer->quantity;
                
                // When received, increment destination stock by actual received quantity
                $destProduct = $transfer->destinationProduct;
                $destProduct->increment('stock', $actualReceived);

                // Create stock movement for destination (In)
                StockMovement::create([
                    'product_id' => $destProduct->id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $actualReceived,
                    'reference' => 'Transfer RECEIVED from Branch ' . $transfer->sourceBranch->name . ' (Ref: ' . $transfer->reference . ')',
                    'method' => 'Transfer',
                ]);

                // Track loss if applicable
                $lossQuantity = $transfer->quantity - $actualReceived;
                $updateData = [
                    'status' => StockTransfer::STATUS_RECEIVED,
                    'received_quantity' => $actualReceived,
                    'arrival_notes' => $request->arrival_notes,
                ];

                $transfer->update($updateData);
            }

            DB::commit();
            return back()->with('success', 'Status transfer berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with(['product.category', 'user', 'tailor'])->latest()->get();
        $tailors = \App\Models\Tailor::all();
        $branches = \App\Models\Branch::where('is_active', true)->get();
        
        // Final products for transfer options
        $transferProducts = Product::where('type', 'final_product')
            ->where('stock', '>', 0)
            ->where(function($q) {
                // Determine user's branch
                $branchId = auth()->user()->branch_id ?? 1;
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->get();

        return view('pages.production.index', compact('productions', 'tailors', 'branches', 'transferProducts'))
            ->with('activePage', 'production');
    }

    public function create()
    {
        $finalProducts = Product::where('type', 'final_product')->with('billOfMaterialsAsFinal.rawMaterial')->get();
        $tailors = \App\Models\Tailor::all();
        return view('pages.production.create', compact('finalProducts', 'tailors'))
            ->with('activePage', 'production');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
            'tailor_id' => 'nullable|exists:tailors,id',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $finalProduct = Product::with('billOfMaterialsAsFinal.rawMaterial')->findOrFail($validated['product_id']);
        
        // Validate if there are enough raw materials
        foreach ($finalProduct->billOfMaterialsAsFinal as $bom) {
            $requiredQuantity = $bom->quantity * $validated['quantity'];
            if ($bom->rawMaterial->stock < $requiredQuantity) {
                return back()->with('error', "Stok bahan baku {$bom->rawMaterial->name} tidak mencukupi. Butuh {$requiredQuantity}, tersedia {$bom->rawMaterial->stock}.")->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Deduct raw materials
            foreach ($finalProduct->billOfMaterialsAsFinal as $bom) {
                $requiredQuantity = $bom->quantity * $validated['quantity'];
                $bom->rawMaterial->decrement('stock', $requiredQuantity);
                
                StockMovement::create([
                    'product_id' => $bom->raw_material_id,
                    'user_id' => Auth::id(),
                    'type' => 'proses',
                    'quantity' => $requiredQuantity,
                    'reference' => 'Produksi ' . $finalProduct->name,
                    'method' => 'Production Use',
                ]);
            }

            // Create production record as 'processing'
            $production = Production::create([
                'product_id' => $finalProduct->id,
                'quantity' => $validated['quantity'],
                'date' => $validated['date'],
                'tailor_id' => $validated['tailor_id'] ?? null,
                'user_id' => Auth::id(),
                'status' => 'processing',
                'cost' => $validated['cost'] ?? 0,
                'notes' => $validated['notes'],
            ]);

            // Add 'proses' movement for final product to record what's being made
            StockMovement::create([
                'product_id' => $finalProduct->id,
                'user_id' => Auth::id(),
                'type' => 'proses',
                'quantity' => $validated['quantity'],
                'reference' => 'Dalam Produksi #' . $production->id,
                'method' => 'Work In Progress',
            ]);

            // Set the first stage dynamically
            $production->stage = $production->stages[0] ?? 'Proses Pembuatan';
            $production->save();

            DB::commit();
            return redirect()->route('production.index')->with('success', "Produksi {$finalProduct->name} berhasil dimulai.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }
    }

    public function updateStatus(Request $request, Production $production)
    {
        $validated = $request->validate([
            'status' => 'required|in:completed,cancelled',
        ]);

        if ($production->status !== 'processing') {
            return back()->with('error', 'Hanya produksi yang sedang diproses yang dapat diubah statusnya.');
        }

        DB::beginTransaction();
        try {
            $production->status = $validated['status'];
            $production->save();

            if ($validated['status'] === 'completed') {
                // Add final product to stock
                $production->product->increment('stock', $production->quantity);
                
                StockMovement::create([
                    'product_id' => $production->product_id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $production->quantity,
                    'reference' => 'Hasil Produksi #' . $production->id,
                    'method' => 'Production Result',
                ]);

                // Finance Synchronization: Deduct Production Cost
                $totalCost = $production->cost * $production->quantity;
                if ($totalCost > 0) {
                    $ref = 'PROD-COST-' . $production->id . '-' . time();
                    
                    CashTransaction::create([
                        'date' => Carbon::now(),
                        'type' => 'out',
                        'category' => 'Operational',
                        'amount' => $totalCost,
                        'description' => "Biaya Produksi/Jahit: {$production->product->name} (Qty: {$production->quantity} x Rp " . number_format($production->cost, 0, ',', '.') . ")",
                        'reference' => $ref,
                        'user_id' => Auth::id() ?? 1,
                    ]);

                    $bebanProduksi = Account::where('code', '5-108')->first(); // Beban Lain-Lain / Produksi
                    $kasAccount = Account::where('code', '1-101')->first();

                    if ($bebanProduksi && $kasAccount) {
                        $journalEntry = JournalEntry::create([
                            'date' => Carbon::now(),
                            'reference' => $ref,
                            'description' => "Biaya Produksi/Jahit: {$production->product->name}",
                        ]);

                        $journalEntry->items()->createMany([
                            [
                                'account_id' => $bebanProduksi->id,
                                'debit' => $totalCost,
                                'credit' => 0,
                            ],
                            [
                                'account_id' => $kasAccount->id,
                                'debit' => 0,
                                'credit' => $totalCost,
                            ]
                        ]);
                    }
                }
            } elseif ($validated['status'] === 'cancelled') {
                // Return raw materials
                foreach ($production->product->billOfMaterialsAsFinal as $bom) {
                    $requiredQuantity = $bom->quantity * $production->quantity;
                    $bom->rawMaterial->increment('stock', $requiredQuantity);
                    
                    StockMovement::create([
                        'product_id' => $bom->raw_material_id,
                        'user_id' => Auth::id(),
                        'type' => 'in',
                        'quantity' => $requiredQuantity,
                        'reference' => 'Pembatalan Produksi #' . $production->id,
                        'method' => 'Production Cancelled',
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('production.index')->with('success', "Status produksi berhasil diperbarui menjadi {$validated['status']}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function advanceStage(Request $request, Production $production)
    {
        if ($production->status !== 'processing') {
            return back()->with('error', 'Hanya produksi yang sedang diproses yang dapat dimajukan tahapannya.');
        }

        $nextStage = $production->getNextStage();

        if (!$nextStage) {
            return back()->with('error', 'Produksi sudah berada di tahap akhir atau tidak valid.');
        }

        DB::beginTransaction();
        try {
            if ($nextStage === 'completed') {
                $production->status = 'completed';
                $production->save();

                // Add final product to stock
                $production->product->increment('stock', $production->quantity);
                
                StockMovement::create([
                    'product_id' => $production->product_id,
                    'user_id' => Auth::id(),
                    'type' => 'in',
                    'quantity' => $production->quantity,
                    'reference' => 'Hasil Produksi #' . $production->id . ' (Selesai)',
                    'method' => 'Production Result',
                ]);

                // Finance Synchronization: Deduct Production Cost
                $totalCost = $production->cost * $production->quantity;
                if ($totalCost > 0) {
                    $ref = 'PROD-COST-' . $production->id . '-' . time();
                    
                    CashTransaction::create([
                        'date' => Carbon::now(),
                        'type' => 'out',
                        'category' => 'Operational',
                        'amount' => $totalCost,
                        'description' => "Biaya Produksi/Jahit: {$production->product->name} (Qty: {$production->quantity} x Rp " . number_format($production->cost, 0, ',', '.') . ")",
                        'reference' => $ref,
                        'user_id' => Auth::id() ?? 1,
                    ]);

                    $bebanProduksi = Account::where('code', '5-108')->first(); // Beban Lain-Lain / Produksi
                    $kasAccount = Account::where('code', '1-101')->first();

                    if ($bebanProduksi && $kasAccount) {
                        $journalEntry = JournalEntry::create([
                            'date' => Carbon::now(),
                            'reference' => $ref,
                            'description' => "Biaya Produksi/Jahit: {$production->product->name}",
                        ]);

                        $journalEntry->items()->createMany([
                            [
                                'account_id' => $bebanProduksi->id,
                                'debit' => $totalCost,
                                'credit' => 0,
                            ],
                            [
                                'account_id' => $kasAccount->id,
                                'debit' => 0,
                                'credit' => $totalCost,
                            ]
                        ]);
                    }
                }

                $message = "Produksi selesai. Barang jadi telah ditambahkan ke stok.";
            } else {
                $production->stage = $nextStage;
                $production->save();
                
                $message = "Tahapan produksi dilanjutkan ke: " . $production->stage_label;
            }

            DB::commit();
            return redirect()->route('production.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    public function assignTailor(Request $request, Production $production)
    {
        $validated = $request->validate([
            'tailor_id' => 'nullable|exists:tailors,id',
        ]);

        if ($production->status !== 'processing') {
            return back()->with('error', 'Hanya produksi berjalan yang dapat ditugaskan ke penjahit.');
        }

        $production->tailor_id = $validated['tailor_id'];
        $production->save();

        if ($validated['tailor_id']) {
            $tailor = \App\Models\Tailor::find($validated['tailor_id']);
            return back()->with('success', "Produksi ditugaskan ke vendor penjahit: {$tailor->name}.");
        } else {
            return back()->with('success', "Penugasan vendor penjahit dilepas dari produksi.");
        }
    }

    public function transferStock(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $sourceProduct = Product::findOrFail($validated['product_id']);
        
        // Ensure source product has enough stock
        if ($sourceProduct->stock < $validated['quantity']) {
            return back()->with('error', 'Stok tidak mencukupi untuk melakukan pengiriman.');
        }

        // Prevent transferring to the same branch
        $sourceBranchId = $sourceProduct->branch_id ?? 1; // Assuming null is Pusat (1)
        if ($sourceBranchId == $validated['branch_id']) {
            return back()->with('error', 'Cabang tujuan tidak boleh sama dengan cabang asal.');
        }

        DB::beginTransaction();
        try {
            // Deduct stock from source
            $sourceProduct->decrement('stock', $validated['quantity']);
            
            $destinationBranch = \App\Models\Branch::find($validated['branch_id']);
            $sourceBranchName = $sourceProduct->branch ? $sourceProduct->branch->name : 'Kantor Pusat';

            // Log source movement
            StockMovement::create([
                'product_id' => $sourceProduct->id,
                'user_id' => Auth::id(),
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'reference' => 'Kirim ke ' . $destinationBranch->name,
                'method' => 'Transfer Out',
                'notes' => $validated['notes']
            ]);

            // Find or create product in destination branch
            $destinationProduct = Product::where('sku', $sourceProduct->sku)
                ->where('branch_id', $validated['branch_id'])
                ->first();

            if (!$destinationProduct) {
                // Clone the product structure for the new branch
                $destinationProduct = $sourceProduct->replicate();
                $destinationProduct->branch_id = $validated['branch_id'];
                $destinationProduct->stock = 0;
                $destinationProduct->save();
            }

            // Increment stock at destination
            $destinationProduct->increment('stock', $validated['quantity']);

            // Log destination movement
            StockMovement::create([
                'product_id' => $destinationProduct->id,
                'user_id' => Auth::id(),
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'reference' => 'Terima dari ' . $sourceBranchName,
                'method' => 'Transfer In',
                'notes' => $validated['notes']
            ]);

            DB::commit();
            return redirect()->route('production.index')->with('success', "Berhasil mengirim {$validated['quantity']} unit {$sourceProduct->name} ke {$destinationBranch->name}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses pengiriman: ' . $e->getMessage());
        }
    }
}

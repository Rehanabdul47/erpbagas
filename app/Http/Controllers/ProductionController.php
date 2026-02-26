<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with(['product', 'user'])->latest()->get();
        return view('pages.production.index', compact('productions'))
            ->with('activePage', 'production');
    }

    public function create()
    {
        $finalProducts = Product::where('type', 'final_product')->with('billOfMaterialsAsFinal.rawMaterial')->get();
        return view('pages.production.create', compact('finalProducts'))
            ->with('activePage', 'production');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
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
                    'type' => 'out',
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
                'user_id' => Auth::id(),
                'status' => 'processing',
                'notes' => $validated['notes'],
            ]);

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
                    'type' => 'in',
                    'quantity' => $production->quantity,
                    'reference' => 'Hasil Produksi #' . $production->id,
                    'method' => 'Production Result',
                ]);
            } elseif ($validated['status'] === 'cancelled') {
                // Return raw materials
                foreach ($production->product->billOfMaterialsAsFinal as $bom) {
                    $requiredQuantity = $bom->quantity * $production->quantity;
                    $bom->rawMaterial->increment('stock', $requiredQuantity);
                    
                    StockMovement::create([
                        'product_id' => $bom->raw_material_id,
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
}

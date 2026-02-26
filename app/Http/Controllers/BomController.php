<?php

namespace App\Http\Controllers;

use App\Models\BillOfMaterial;
use App\Models\Product;
use Illuminate\Http\Request;

class BomController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'raw_material_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        if ($product->type !== 'final_product') {
            return back()->with('error', 'Bill of Materials hanya dapat ditambahkan pada produk final.');
        }

        if ($validated['raw_material_id'] == $product->id) {
            return back()->with('error', 'Produk tidak dapat menjadi bahan baku untuk dirinya sendiri.');
        }

        // Check if ingredient already exists
        $existing = BillOfMaterial::where('final_product_id', $product->id)
                                  ->where('raw_material_id', $validated['raw_material_id'])
                                  ->first();

        if ($existing) {
            $existing->increment('quantity', $validated['quantity']);
        } else {
            BillOfMaterial::create([
                'final_product_id' => $product->id,
                'raw_material_id' => $validated['raw_material_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        return back()->with('success', 'Bahan baku berhasil ditambahkan ke Bill of Materials.');
    }

    public function destroy(BillOfMaterial $bom)
    {
        $bom->delete();
        return back()->with('success', 'Bahan baku berhasil dihapus dari Bill of Materials.');
    }
}

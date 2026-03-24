<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Supplier;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with('supplierProducts.product')->latest()->get();
        $products = \App\Models\Product::where('type', '!=', 'Service')->where('branch_id', 1)->orderBy('name')->get();
        return view('pages.suppliers.index', compact('suppliers', 'products'))->with('activePage', 'suppliers');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        Supplier::create($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        $supplier->update($validated);
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier berhasil dihapus.');
    }

    // --- Supplier Products Catalog ---

    public function storeProduct(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $supplier->supplierProducts()->create($validated);
        return redirect()->route('suppliers.index')->with('success', "Barang {$validated['name']} berhasil ditambahkan ke katalog {$supplier->name}.");
    }

    public function updateProduct(Request $request, Supplier $supplier, \App\Models\SupplierProduct $supplierProduct)
    {
        if ($supplierProduct->supplier_id !== $supplier->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'price' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
        ]);

        $supplierProduct->update($validated);
        return redirect()->route('suppliers.index')->with('success', "Barang {$validated['name']} berhasil diperbarui.");
    }

    public function destroyProduct(Supplier $supplier, \App\Models\SupplierProduct $supplierProduct)
    {
        if ($supplierProduct->supplier_id !== $supplier->id) {
            abort(404);
        }

        $name = $supplierProduct->name;
        $supplierProduct->delete();
        return redirect()->route('suppliers.index')->with('success', "Barang {$name} berhasil dihapus dari katalog.");
    }

    // --- API Endpoint for Purchase Order ---
    
    public function apiProducts(Supplier $supplier)
    {
        return response()->json([
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
            ],
            'products' => $supplier->supplierProducts()->orderBy('name')->get()
        ]);
    }
}

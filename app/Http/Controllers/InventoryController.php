<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get();
        $categories = Category::all();
        $movements = StockMovement::with('product')->latest()->limit(20)->get();
        $opnameHistory = StockOpname::with(['product', 'user'])->latest()->limit(10)->get();

        $stats = [
            'totalSku' => $products->count(),
            'totalStock' => $products->sum('stock'),
            'lowStock' => $products->where('status', 'low')->count(),
            'outOfStock' => $products->where('status', 'out')->count(),
        ];

        return view('pages.inventory.index', compact('products', 'categories', 'movements', 'opnameHistory', 'stats'))
            ->with('activePage', 'inventory');
    }

    public function create()
    {
        $categories = Category::all();
        return view('pages.inventory.create', compact('categories'))
            ->with('activePage', 'inventory');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|integer|min:0',
            'safety_stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cogs' => 'required|numeric|min:0',
            'batch' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $validated['image'] = 'storage/' . $path;
        }

        $product = Product::create($validated);

        if ($product->stock > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => 'in',
                'quantity' => $product->stock,
                'reference' => 'Initial Stock',
                'method' => 'Manual',
            ]);
        }

        return redirect()->route('inventory.index')->with('success', "Produk {$product->name} berhasil ditambahkan");
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('pages.inventory.edit', compact('product', 'categories'))
            ->with('activePage', 'inventory');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'safety_stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cogs' => 'required|numeric|min:0',
            'batch' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $validated['image'] = 'storage/' . $path;
        }

        $product->update($validated);

        return redirect()->route('inventory.index')->with('success', "Produk {$product->name} berhasil diupdate");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        return redirect()->route('inventory.index')->with('success', "Produk {$name} berhasil dihapus");
    }

    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $product->increment('stock', $validated['quantity']);

        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => $validated['quantity'],
            'reference' => $validated['reference'] ?? 'Manual',
            'method' => 'Manual',
        ]);

        return redirect()->route('inventory.index')->with('success', "{$validated['quantity']} unit {$product->name} berhasil masuk");
    }

    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['quantity'] > $product->stock) {
            return back()->with('error', 'Jumlah melebihi stok tersedia');
        }

        $product->decrement('stock', $validated['quantity']);

        StockMovement::create([
            'product_id' => $product->id,
            'type' => 'out',
            'quantity' => $validated['quantity'],
            'reference' => $validated['reference'] ?? 'Manual',
            'method' => 'Manual',
        ]);

        return redirect()->route('inventory.index')->with('success', "{$validated['quantity']} unit {$product->name} berhasil keluar");
    }

    public function stockOpname(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'physical_stock' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $system_stock = $product->stock;
        $physical_stock = $validated['physical_stock'];
        $difference = $physical_stock - $system_stock;

        // Create Opname Record
        StockOpname::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'system_stock' => $system_stock,
            'physical_stock' => $physical_stock,
            'difference' => $difference,
            'notes' => $validated['notes'],
        ]);

        // Adjust Product Stock
        $product->stock = $physical_stock;
        $product->save();

        // Log Movement if there's a difference
        if ($difference != 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type' => $difference > 0 ? 'in' : 'out',
                'quantity' => abs($difference),
                'reference' => 'Stock Opname Adjustment',
                'method' => 'Opname',
            ]);
        }

        return redirect()->route('inventory.index', ['tab' => 'opname'])->with('success', "Stock Opname produk {$product->name} berhasil disimpan");
    }
}

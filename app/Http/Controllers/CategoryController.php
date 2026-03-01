<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->withSum('products', 'stock')
            ->get();

        return view('pages.categories.index', compact('categories'))
            ->with('activePage', 'categories');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'production_stages' => 'nullable|string'
        ]);

        if (!empty($validated['production_stages'])) {
            $validated['production_stages'] = array_values(array_filter(array_map('trim', explode(',', $validated['production_stages']))));
        } else {
            $validated['production_stages'] = null;
        }

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', "Kategori {$validated['name']} berhasil ditambahkan");
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'production_stages' => 'nullable|string'
        ]);

        if (!empty($validated['production_stages'])) {
            $validated['production_stages'] = array_values(array_filter(array_map('trim', explode(',', $validated['production_stages']))));
        } else {
            $validated['production_stages'] = null;
        }

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', "Kategori berhasil diupdate menjadi {$validated['name']}");
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', "Kategori {$category->name} tidak bisa dihapus karena masih memiliki {$category->products()->count()} produk");
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('categories.index')->with('success', "Kategori {$name} berhasil dihapus");
    }
}

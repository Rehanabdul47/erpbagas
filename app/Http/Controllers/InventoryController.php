<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class InventoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isExecutive = $user->hasRole('Executive');
        
        // Scope queries based on role
        $productQuery = Product::with(['category', 'branch']);
        $movementQuery = StockMovement::with(['product.branch', 'user'])->latest();
        $opnameQuery = StockOpname::with(['product.branch', 'user'])->latest();
        
        $branches = collect();
        if ($isExecutive) {
            $branches = \App\Models\Branch::where('is_active', true)->get();
            if (request('branch_id')) {
                $productQuery->where('branch_id', request('branch_id'));
                $movementQuery->whereHas('product', function($q) {
                    $q->where('branch_id', request('branch_id'));
                });
                $opnameQuery->whereHas('product', function($q) {
                    $q->where('branch_id', request('branch_id'));
                });
            }
        } else {
            // Normalize Pusat to 1 or null depending on data entry, assuming branch_id 1 is the default
            $branchId = $user->branch_id ?? 1;
            
            $productQuery->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
            $movementQuery->whereHas('product', function($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
            $opnameQuery->whereHas('product', function($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }

        $products = $productQuery->get();
        $categories = Category::all();
        $movements = $movementQuery->limit(20)->get();
        $opnameHistory = $opnameQuery->limit(10)->get();

        $stats = [
            'totalSku' => $products->count(),
            'totalStock' => $products->sum('stock'),
            'lowStock' => $products->where('status', 'low')->count(),
            'outOfStock' => $products->where('stock', 0)->count(),
        ];

        return view('pages.inventory.index', compact('products', 'categories', 'movements', 'opnameHistory', 'stats', 'branches'))
            ->with('activePage', 'inventory');
    }

    public function create()
    {
        $user = Auth::user();
        $isExecutive = $user->hasRole('Executive');
        $branchId = $user->branch_id ?? 1;

        $categories = Category::all();
        
        $rawMaterialsQuery = Product::where('type', 'raw_material');
        if (!$isExecutive) {
            $rawMaterialsQuery->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            });
        }
        $rawMaterials = $rawMaterialsQuery->get();
        
        $branches = $isExecutive ? \App\Models\Branch::where('is_active', true)->get() : collect();

        return view('pages.inventory.create', compact('categories', 'rawMaterials', 'branches', 'isExecutive'))
            ->with('activePage', 'inventory');
    }

    public function store(Request $request)
    {
        $branchId = Auth::user()->hasRole('Executive')
            ? $request->input('branch_id', 1)
            : (Auth::user()->branch_id ?? 1);

        $validated = $request->validate([
            'sku' => ['required', 'string', Rule::unique('products', 'sku')->where('branch_id', $branchId)],
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
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
            'type' => 'required|in:raw_material,final_product',
            'branch_id' => 'nullable|exists:branches,id',
            'boms' => 'nullable|array',
            'boms.*.raw_material_id' => 'nullable|exists:products,id',
            'boms.*.quantity' => 'nullable|numeric|min:0.01',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $validated['image'] = 'storage/' . $path;
        }

        if (!Auth::user()->hasRole('Executive')) {
            $validated['branch_id'] = Auth::user()->branch_id ?? 1;
        }

        $product = Product::create($validated);

        if ($product->type === 'final_product' && !empty($request->boms)) {
            foreach ($request->boms as $bom) {
                if (!empty($bom['raw_material_id']) && !empty($bom['quantity'])) {
                    \App\Models\BillOfMaterial::create([
                        'final_product_id' => $product->id,
                        'raw_material_id' => $bom['raw_material_id'],
                        'quantity' => $bom['quantity'],
                    ]);
                }
            }
        }

        if ($product->stock > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
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
        $user = Auth::user();
        $isExecutive = $user->hasRole('Executive');
        $branchId = $user->branch_id ?? 1;

        $categories = Category::all();
        
        $rawMaterialsQuery = Product::where('type', 'raw_material')
                                ->where('id', '!=', $product->id);
                                
        if (!$isExecutive) {
            // Include items in user's branch, or items with no branch, OR the items currently selected in the BOM (even if branch changed)
            $existingBomRawMaterialIds = $product->billOfMaterialsAsFinal->pluck('raw_material_id')->toArray();
            
            $rawMaterialsQuery->where(function($q) use ($branchId, $existingBomRawMaterialIds) {
                $q->where('branch_id', $branchId)
                  ->orWhereNull('branch_id')
                  ->orWhereIn('id', $existingBomRawMaterialIds);
            });
        }
        $rawMaterials = $rawMaterialsQuery->get();
        
        $branches = $isExecutive ? \App\Models\Branch::where('is_active', true)->get() : collect();

        $product->load('billOfMaterialsAsFinal.rawMaterial');

        return view('pages.inventory.edit', compact('product', 'categories', 'rawMaterials', 'branches', 'isExecutive'))
            ->with('activePage', 'inventory');
    }

    public function update(Request $request, Product $product)
    {
        $branchId = Auth::user()->hasRole('Executive')
            ? $request->input('branch_id', $product->branch_id ?? 1)
            : (Auth::user()->branch_id ?? 1);

        $validated = $request->validate([
            'sku' => ['required', 'string', Rule::unique('products', 'sku')->where('branch_id', $branchId)->ignore($product->id)],
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'safety_stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cogs' => 'required|numeric|min:0',
            'batch' => 'nullable|string',
            'location' => 'nullable|string',
            'image' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
            'type' => 'required|in:raw_material,final_product',
            'branch_id' => 'nullable|exists:branches,id',
            'boms' => 'nullable|array',
            'boms.*.raw_material_id' => 'nullable|exists:products,id',
            'boms.*.quantity' => 'nullable|numeric|min:0.01',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $validated['image'] = 'storage/' . $path;
        }

        if (!Auth::user()->hasRole('Executive')) {
             // Managers/Staff can't change branch info, lock it to theirs
            $validated['branch_id'] = Auth::user()->branch_id ?? 1;
        } elseif (!isset($validated['branch_id'])) {
             // In case executive clears it, fallback to 1 (Pusat) / null
            $validated['branch_id'] = 1;
        }

        $product->update($validated);

        \App\Models\BillOfMaterial::where('final_product_id', $product->id)->delete();
        if ($product->type === 'final_product' && !empty($request->boms)) {
            foreach ($request->boms as $bom) {
                if (!empty($bom['raw_material_id']) && !empty($bom['quantity'])) {
                    \App\Models\BillOfMaterial::create([
                        'final_product_id' => $product->id,
                        'raw_material_id' => $bom['raw_material_id'],
                        'quantity' => $bom['quantity'],
                    ]);
                }
            }
        }

        return redirect()->route('inventory.index')->with('success', "Produk {$product->name} berhasil diupdate");
    }

    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('inventory.index')->with('success', 'Produk berhasil dihapus');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return redirect()->route('inventory.index')->with('error', 'Produk gagal dihapus karena masih terkait dengan data transaksi atau riwayat inventaris.');
            }
            return redirect()->route('inventory.index')->with('error', 'Terjadi kesalahan saat menghapus produk: ' . $e->getMessage());
        }
    }

    public function barcode(Product $product)
    {
        return view('pages.inventory.barcode', compact('product'))
            ->with('activePage', 'inventory');
    }

    public function stockIn(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'method' => 'required|string',
            'reference' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $product->increment('stock', $validated['quantity']);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'type' => 'in',
            'quantity' => $validated['quantity'],
            'reference' => $validated['reference'] ?? 'Sistem',
            'method' => $validated['method'],
        ]);

        // Finance Synchronization for Customer Returns
        if ($validated['method'] === 'Retur dari Customer') {
            $amount = $product->price * $validated['quantity'];
            $totalCogs = $product->cogs * $validated['quantity'];
            $ref = 'RETUR-' . $product->id . '-' . time();
            
            // 1. Record Cash Out (Refund)
            CashTransaction::create([
                'date' => Carbon::now(),
                'type' => 'out',
                'category' => 'Returning Goods',
                'amount' => $amount,
                'description' => "Retur Penjualan Produk: {$product->name} ({$validated['quantity']} unit)",
                'reference' => $ref,
                'user_id' => Auth::id() ?? 1,
            ]);

            // 2. Journal Entry
            $returAccount = Account::where('code', '4-103')->first();      // Retur Penjualan
            $kasAccount = Account::where('code', '1-101')->first();        // Kas
            $persediaanAccount = Account::where('code', '1-104')->first(); // Persediaan Barang Dagang
            $hppAccount = Account::where('code', '5-101')->first();        // Harga Pokok Penjualan (HPP)

            if ($returAccount && $kasAccount) {
                $journalEntry = JournalEntry::create([
                    'date' => Carbon::now(),
                    'reference' => $ref,
                    'description' => "Retur Penjualan Produk: {$product->name}",
                ]);

                // Journal for Revenue Refund
                // Debit: Retur Penjualan (Revenue reduction), Credit: Kas (Asset reduction)
                $items = [
                    [
                        'account_id' => $returAccount->id,
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'account_id' => $kasAccount->id,
                        'debit' => 0,
                        'credit' => $amount,
                    ]
                ];

                // Journal for Inventory Reversal (If HPP and Inventory accounts exist)
                if ($persediaanAccount && $hppAccount && $totalCogs > 0) {
                    $items[] = [
                        'account_id' => $persediaanAccount->id,
                        'debit' => $totalCogs,
                        'credit' => 0,
                    ];
                    $items[] = [
                        'account_id' => $hppAccount->id,
                        'debit' => 0,
                        'credit' => $totalCogs,
                    ];
                }

                $journalEntry->items()->createMany($items);
            }
        }

        return redirect()->route('inventory.index')->with('success', "{$validated['quantity']} unit {$product->name} berhasil masuk");
    }

    public function stockOut(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'method' => 'required|string',
            'reference' => 'nullable|string',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['quantity'] > $product->stock) {
            return back()->with('error', 'Jumlah melebihi stok tersedia');
        }

        $product->decrement('stock', $validated['quantity']);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'type' => 'out',
            'quantity' => $validated['quantity'],
            'reference' => $validated['reference'] ?? 'Sistem',
            'method' => $validated['method'],
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
                'user_id' => Auth::id(),
                'type' => $difference > 0 ? 'in' : 'out',
                'quantity' => abs($difference),
                'reference' => 'Stock Opname Adjustment',
                'method' => 'Opname',
            ]);
        }

        return redirect()->route('inventory.index', ['tab' => 'opname'])->with('success', "Stock Opname produk {$product->name} berhasil disimpan");
    }
}

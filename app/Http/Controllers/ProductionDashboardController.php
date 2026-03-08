<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Production;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tailor;
use App\Models\Category;

class ProductionDashboardController extends Controller
{
    public function index()
    {
        // Calculate Metrics
        $totalSku = Product::count();
        $totalStock = Product::sum('stock');
        $lowStock = Product::whereColumn('stock', '<=', 'safety_stock')->where('stock', '>', 0)->count();
        $outOfStock = Product::where('stock', 0)->count();
        $activeProductionsCount = Production::whereIn('status', ['pending', 'in_progress', 'processing'])->count();
        $activePosCount = PurchaseOrder::whereIn('status', ['pending', 'ordered'])->count();
        
        // Additional Metrics for Production Division Elements
        $totalSuppliers = Supplier::count();
        $totalTailors = Tailor::count();
        $activeTailorsCount = Tailor::whereHas('productions', function($q) {
            $q->whereIn('status', ['pending', 'in_progress', 'processing']);
        })->count();
        $totalCategories = Category::count();

        // Fetch lists for widgets
        $activeProductions = Production::with(['product', 'tailor'])
            ->whereIn('status', ['pending', 'in_progress', 'processing'])
            ->latest()
            ->take(5)
            ->get();
            
        $criticalStockProducts = Product::where('stock', '<=', \DB::raw('safety_stock'))
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();
            
        $recentPos = PurchaseOrder::with('supplier')
            ->latest()
            ->take(5)
            ->get();
            
        $recentTransfers = \App\Models\StockMovement::with(['product.category', 'user'])
            ->where('type', 'out')
            ->where('reference', 'LIKE', 'Kirim ke%')
            ->latest()
            ->take(5)
            ->get();
            
        $branches = \App\Models\Branch::where('is_active', true)->get();
        $transferProducts = Product::where('type', 'final_product')
            ->where('stock', '>', 0)
            ->where(function($q) {
                $branchId = auth()->user()->branch_id ?? 1;
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->get();
            
        $activePage = 'production-dashboard';

        return view('pages.production-dashboard.index', compact(
            'totalSku', 
            'totalStock', 
            'lowStock', 
            'outOfStock',
            'activeProductionsCount',
            'activePosCount',
            'totalSuppliers',
            'totalTailors',
            'activeTailorsCount',
            'totalCategories',
            'activeProductions',
            'criticalStockProducts',
            'recentPos',
            'recentTransfers',
            'branches',
            'transferProducts',
            'activePage'
        ));
    }
}

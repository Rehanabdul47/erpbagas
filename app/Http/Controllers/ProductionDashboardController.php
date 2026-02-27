<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Production;
use App\Models\PurchaseOrder;

class ProductionDashboardController extends Controller
{
    public function index()
    {
        // Calculate Metrics
        $totalSku = Product::count();
        $totalStock = Product::sum('stock');
        $lowStock = Product::whereColumn('stock', '<=', 'safety_stock')->where('stock', '>', 0)->count();
        $outOfStock = Product::where('stock', 0)->count();
        $activeProductionsCount = Production::whereIn('status', ['pending', 'in_progress'])->count();
        $activePosCount = PurchaseOrder::whereIn('status', ['pending', 'ordered'])->count();
        
        // Fetch lists for widgets
        $activeProductions = Production::with('product')
            ->whereIn('status', ['pending', 'in_progress'])
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
            
        $activePage = 'production-dashboard';

        return view('pages.production-dashboard.index', compact(
            'totalSku', 
            'totalStock', 
            'lowStock', 
            'outOfStock',
            'activeProductionsCount',
            'activePosCount',
            'activeProductions',
            'criticalStockProducts',
            'recentPos',
            'activePage'
        ));
    }
}

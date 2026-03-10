<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Production;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Tailor;
use App\Models\Category;
use App\Models\StockTransfer;
use App\Traits\BranchScope;

class ProductionDashboardController extends Controller
{
    use BranchScope;

    public function index()
    {
        // Calculate Metrics - scoped by branch
        $totalSkuQuery = Product::query();
        $this->scopeByBranch($totalSkuQuery);
        $totalSku = $totalSkuQuery->count();

        $totalStockQuery = Product::query();
        $this->scopeByBranch($totalStockQuery);
        $totalStock = $totalStockQuery->sum('stock');

        $lowStockQuery = Product::whereColumn('stock', '<=', 'safety_stock')->where('stock', '>', 0);
        $this->scopeByBranch($lowStockQuery);
        $lowStock = $lowStockQuery->count();

        $outOfStockQuery = Product::where('stock', 0);
        $this->scopeByBranch($outOfStockQuery);
        $outOfStock = $outOfStockQuery->count();

        $activeProductionsQuery = Production::whereIn('status', ['pending', 'in_progress', 'processing']);
        $this->scopeByRelationBranch($activeProductionsQuery, 'product');
        $activeProductionsCount = $activeProductionsQuery->count();

        $activePosQuery = PurchaseOrder::whereIn('status', ['pending', 'ordered']);
        $this->scopeByUserBranch($activePosQuery);
        $activePosCount = $activePosQuery->count();
        
        // Additional Metrics for Production Division Elements
        $totalSuppliers = Supplier::count();
        $totalTailors = Tailor::count();
        $activeTailorsCount = Tailor::whereHas('productions', function($q) {
            $q->whereIn('status', ['pending', 'in_progress', 'processing']);
        })->count();
        $totalCategories = Category::count();

        $activeTransfersQuery = StockTransfer::whereIn('status', ['pending', 'sending']);
        if (!$this->isExecutive()) {
            $branchId = $this->getUserBranchId();
            if ($branchId) {
                $activeTransfersQuery->where(function($q) use ($branchId) {
                    $q->where('source_branch_id', $branchId)
                      ->orWhere('destination_branch_id', $branchId);
                });
            }
        }
        $activeTransfersCount = $activeTransfersQuery->count();

        // Fetch lists for widgets - scoped by branch
        $activeProductionsListQuery = Production::with(['product', 'tailor'])
            ->whereIn('status', ['pending', 'in_progress', 'processing'])
            ->latest();
        $this->scopeByRelationBranch($activeProductionsListQuery, 'product');
        $activeProductions = $activeProductionsListQuery->take(5)->get();
            
        $criticalStockQuery = Product::where('stock', '<=', \DB::raw('safety_stock'))
            ->orderBy('stock', 'asc');
        $this->scopeByBranch($criticalStockQuery);
        $criticalStockProducts = $criticalStockQuery->take(5)->get();
            
        $recentPosQuery = PurchaseOrder::with('supplier')->latest();
        $this->scopeByUserBranch($recentPosQuery);
        $recentPos = $recentPosQuery->take(5)->get();
            
        $recentTransfersQuery = StockTransfer::with(['sourceBranch', 'destinationBranch', 'product'])->latest();
        if (!$this->isExecutive()) {
            $branchId = $this->getUserBranchId();
            if ($branchId) {
                $recentTransfersQuery->where(function($q) use ($branchId) {
                    $q->where('source_branch_id', $branchId)
                      ->orWhere('destination_branch_id', $branchId);
                });
            }
        }
        $recentTransfers = $recentTransfersQuery->take(5)->get();
            
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
            'activeTransfersCount',
            'activeProductions',
            'criticalStockProducts',
            'recentPos',
            'recentTransfers',
            'activePage'
        ));
    }
}

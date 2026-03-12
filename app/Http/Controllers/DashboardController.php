<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\EcommerceOrder;
use App\Models\Product;
use App\Models\Production;
use App\Models\PurchaseOrder;
use App\Models\StockTransfer;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\CashTransaction;
use App\Models\Tailor;
use App\Traits\BranchScope;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use BranchScope;

    public function index()
    {
        $user = auth()->user();
        
        // Redirect non-management roles to their respective division dashboards
        if (!$user->isManager()) {
            switch ($user->department) {
                case 'Produksi':
                case 'Warehouse/Logistik':
                    return redirect()->route('production.dashboard');
                case 'Marketing':
                    return redirect()->route('marketing.dashboard');
                case 'Digital Marketing':
                    return redirect()->route('digital-marketing.dashboard');
                case 'Keuangan':
                    return redirect()->route('finance.dashboard');
                case 'HRD & GA':
                    return redirect()->route('hrm.dashboard');
            }
        }

        $today = Carbon::today();

        // =============================================
        // 1. MARKETING: Sales & E-commerce Revenue Today
        // =============================================
        $posRevenueTodayQuery = Sale::whereDate('created_at', $today);
        $this->scopeByBranch($posRevenueTodayQuery);
        $posRevenueToday = $posRevenueTodayQuery->sum('total_amount');

        // Ecommerce is considered central (Pusat). Only show if Executive or Branch 1
        $ecommerceRevenueToday = 0;
        if ($this->isExecutive() || $this->getUserBranchId() == 1) {
            $ecommerceRevenueToday = EcommerceOrder::whereDate('order_date', $today)->sum('total_amount');
        }
        $totalRevenueToday = $posRevenueToday + $ecommerceRevenueToday;

        $posRevenueYesterdayQuery = Sale::whereDate('created_at', $today->copy()->subDay());
        $this->scopeByBranch($posRevenueYesterdayQuery);
        $posRevenueYesterday = $posRevenueYesterdayQuery->sum('total_amount');

        $ecommerceRevenueYesterday = 0;
        if ($this->isExecutive() || $this->getUserBranchId() == 1) {
            $ecommerceRevenueYesterday = EcommerceOrder::whereDate('order_date', $today->copy()->subDay())->sum('total_amount');
        }
        $totalRevenueYesterday = $posRevenueYesterday + $ecommerceRevenueYesterday;
        
        $revenueGrowth = 0;
        if ($totalRevenueYesterday > 0) {
            $revenueGrowth = (($totalRevenueToday - $totalRevenueYesterday) / $totalRevenueYesterday) * 100;
        }

        // Orders Today
        $posOrdersTodayQuery = Sale::whereDate('created_at', $today);
        $this->scopeByBranch($posOrdersTodayQuery);
        $posOrdersToday = $posOrdersTodayQuery->count();

        $ecommerceOrdersToday = 0;
        if ($this->isExecutive() || $this->getUserBranchId() == 1) {
            $ecommerceOrdersToday = EcommerceOrder::whereDate('order_date', $today)->count();
        }
        $totalOrdersToday = $posOrdersToday + $ecommerceOrdersToday;

        $posOrdersYesterdayQuery = Sale::whereDate('created_at', $today->copy()->subDay());
        $this->scopeByBranch($posOrdersYesterdayQuery);
        $posOrdersYesterday = $posOrdersYesterdayQuery->count();

        $ecommerceOrdersYesterday = 0;
        if ($this->isExecutive() || $this->getUserBranchId() == 1) {
            $ecommerceOrdersYesterday = EcommerceOrder::whereDate('order_date', $today->copy()->subDay())->count();
        }
        $totalOrdersYesterday = $posOrdersYesterday + $ecommerceOrdersYesterday;

        $ordersGrowth = 0;
        if ($totalOrdersYesterday > 0) {
            $ordersGrowth = (($totalOrdersToday - $totalOrdersYesterday) / $totalOrdersYesterday) * 100;
        }

        // Pending Orders (E-commerce)
        $pendingOrders = 0;
        if ($this->isExecutive() || $this->getUserBranchId() == 1) {
            $pendingOrders = EcommerceOrder::where('status', 'pending')->count();
        }

        // =============================================
        // 2. PRODUKSI: Inventory & Production Metrics
        // =============================================
        $lowStockQuery = Product::whereColumn('stock', '<=', 'min_stock_level');
        $this->scopeByBranch($lowStockQuery);
        $lowStockCount = $lowStockQuery->count();

        $totalStockQuery = Product::query();
        $this->scopeByBranch($totalStockQuery);
        $totalStock = $totalStockQuery->sum('stock');

        $totalSkuQuery = Product::query();
        $this->scopeByBranch($totalSkuQuery);
        $totalSku = $totalSkuQuery->count();

        $activeProductionsQuery = Production::whereIn('status', ['pending', 'in_progress', 'processing']);
        $this->scopeByRelationBranch($activeProductionsQuery, 'product');
        $activeProductionsCount = $activeProductionsQuery->count();

        $activePosQuery = PurchaseOrder::whereIn('status', ['pending', 'ordered']);
        $this->scopeByUserBranch($activePosQuery);
        $activePosCount = $activePosQuery->count();

        $totalTailors = Tailor::count();
        $activeTailorsCount = Tailor::whereHas('productions', function($q) {
            $q->whereIn('status', ['pending', 'in_progress', 'processing']);
        })->count();

        // Active transfers
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

        // Production widgets
        $activeProductionsListQuery = Production::with(['product', 'tailor'])
            ->whereIn('status', ['pending', 'in_progress', 'processing'])
            ->latest();
        $this->scopeByRelationBranch($activeProductionsListQuery, 'product');
        $activeProductions = $activeProductionsListQuery->take(5)->get();

        $criticalStockQuery = Product::where('stock', '<=', DB::raw('safety_stock'))
            ->orderBy('stock', 'asc');
        $this->scopeByBranch($criticalStockQuery);
        $criticalStockProducts = $criticalStockQuery->take(5)->get();

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

        $productionSummary = [
            'total_stock' => $totalStock,
            'total_sku' => $totalSku,
            'active_productions' => $activeProductionsCount,
            'active_pos' => $activePosCount,
            'low_stock' => $lowStockCount,
            'total_tailors' => $totalTailors,
            'active_tailors' => $activeTailorsCount,
            'active_transfers' => $activeTransfersCount,
        ];

        // =============================================
        // 3. HRM: Employee Attendance Summary
        // =============================================
        $totalEmployeesQuery = Employee::query();
        $this->scopeByBranch($totalEmployeesQuery);
        $totalEmployees = $totalEmployeesQuery->count();

        $activeEmployeesQuery = Employee::where('status', 'active');
        $this->scopeByBranch($activeEmployeesQuery);
        $activeEmployees = $activeEmployeesQuery->count();

        $presentQuery = Attendance::whereDate('date', $today)->where('status', 'present');
        $this->scopeByRelationBranch($presentQuery, 'employee');
        $presentCount = $presentQuery->count();

        $sickQuery = Attendance::whereDate('date', $today)->where('status', 'Sick');
        $this->scopeByRelationBranch($sickQuery, 'employee');
        $sickCount = $sickQuery->count();

        $leaveQuery = Attendance::whereDate('date', $today)->where('status', 'Leave');
        $this->scopeByRelationBranch($leaveQuery, 'employee');
        $leaveCount = $leaveQuery->count();

        $absentCount = $totalEmployees - ($presentCount + $sickCount + $leaveCount);

        $attendanceSummary = [
            'total' => $totalEmployees,
            'active' => $activeEmployees,
            'present' => $presentCount,
            'sick' => $sickCount,
            'leave' => $leaveCount,
            'absent' => max(0, $absentCount)
        ];

        // Recent attendances for live list
        $recentAttendancesQuery = Attendance::with('employee.branch')
            ->whereDate('date', $today)
            ->latest('updated_at');
        $this->scopeByRelationBranch($recentAttendancesQuery, 'employee');
        $recentAttendances = $recentAttendancesQuery->take(10)->get();

        // =============================================
        // 4. KEUANGAN: Financial Summary
        // =============================================
        $startOfMonth = Carbon::now()->startOfMonth();
        
        $cashInQuery = CashTransaction::where('type', 'in')->whereDate('date', '>=', $startOfMonth);
        $this->scopeByUserBranch($cashInQuery);
        $cashIn = $cashInQuery->sum('amount');

        $cashOutQuery = CashTransaction::where('type', 'out')->whereDate('date', '>=', $startOfMonth);
        $this->scopeByUserBranch($cashOutQuery);
        $cashOut = $cashOutQuery->sum('amount');

        // Saldo Kas (all time)
        $totalInQuery = CashTransaction::where('type', 'in');
        $this->scopeByUserBranch($totalInQuery);
        $totalInAll = $totalInQuery->sum('amount');

        $totalOutQuery = CashTransaction::where('type', 'out');
        $this->scopeByUserBranch($totalOutQuery);
        $totalOutAll = $totalOutQuery->sum('amount');
        $currentBalance = $totalInAll - $totalOutAll;

        $financeSummary = [
            'income_this_month' => $cashIn,
            'expense_this_month' => $cashOut,
            'total_balance' => $currentBalance,
        ];

        // Recent cash transactions
        $recentCashQuery = CashTransaction::with('user')->latest('date');
        $this->scopeByUserBranch($recentCashQuery);
        $recentCashTransactions = $recentCashQuery->take(5)->get();

        // =============================================
        // 5. TOP PRODUCTS & RECENT ORDERS (Marketing)
        // =============================================
        try {
            $posSalesQuery = DB::table('sale_items')
                ->select('product_id', DB::raw('SUM(quantity) as total_sold'));
            
            if (!$this->isExecutive()) {
                $branchId = $this->getUserBranchId();
                if ($branchId) {
                    $posSalesQuery->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                        ->where('sales.branch_id', $branchId);
                }
            }
            $posSalesQuery->groupBy('product_id');

            $ecomSales = DB::table('ecommerce_order_items')
                ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
                ->groupBy('product_id');

            $combinedSalesSubquery = $posSalesQuery->unionAll($ecomSales);

            $topProducts = DB::table(DB::raw("({$combinedSalesSubquery->toSql()}) as combined_sales"))
                ->mergeBindings($combinedSalesSubquery)
                ->select('products.name', 'categories.name as category_name', 'products.price', DB::raw('SUM(combined_sales.total_sold) as total_sold'))
                ->join('products', 'combined_sales.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->groupBy('products.id', 'products.name', 'categories.name', 'products.price')
                ->orderByDesc('total_sold')
                ->take(5)
                ->get();

        } catch (\Exception $e) {
            $topProducts = Product::select('products.id', 'products.name', 'categories.name as category_name', 'products.price', 'products.stock')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->orderBy('products.stock', 'asc')
                ->take(5)
                ->get();
        }

        // Recent Orders (POS + E-Commerce merged)
        $recentEcommerce = collect();
        if ($this->isExecutive() || $this->getUserBranchId() == 1) {
            $recentEcommerce = EcommerceOrder::latest('order_date')->take(5)->get()->map(function($order) {
                return [
                    'id' => $order->order_number,
                    'channel' => 'E-Commerce (' . $order->platform . ')',
                    'customer' => $order->customer_name,
                    'total' => $order->total_amount,
                    'status' => $order->status,
                    'date' => $order->order_date
                ];
            });
        }

        $recentPosQuery = Sale::latest('created_at');
        $this->scopeByBranch($recentPosQuery);
        $recentPos = $recentPosQuery->take(5)->get()->map(function($sale) {
            return [
                'id' => $sale->invoice_no,
                'channel' => 'POS / Kasir',
                'customer' => $sale->customer ? $sale->customer->name : 'Walk-in',
                'total' => $sale->total_amount,
                'status' => 'completed',
                'date' => $sale->created_at
            ];
        });

        $recentOrders = $recentEcommerce->concat($recentPos)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        // =============================================
        // 6. NOTIFICATIONS (aggregated from all divisions)
        // =============================================
        $notifications = collect();
        
        if ($lowStockCount > 0) {
            $lowStockProductsQuery = Product::whereColumn('stock', '<=', 'min_stock_level');
            $this->scopeByBranch($lowStockProductsQuery);
            $products = $lowStockProductsQuery->take(2)->get();
            foreach ($products as $p) {
                $notifications->push([
                    'type' => 'warning',
                    'title' => "Stok {$p->name} Menipis",
                    'message' => "Sisa stok {$p->stock}, segera re-stock.",
                    'color' => 'amber'
                ]);
            }
        }

        if ($pendingOrders > 0) {
            $notifications->push([
                'type' => 'info',
                'title' => "Ada {$pendingOrders} Pesanan Tertunda",
                'message' => "Segera proses pesanan E-Commerce yang masuk.",
                'color' => 'indigo'
            ]);
        }

        if ($activeProductionsCount > 0) {
            $notifications->push([
                'type' => 'info',
                'title' => "{$activeProductionsCount} Produksi Berjalan",
                'message' => "Ada produksi yang sedang diproses.",
                'color' => 'emerald'
            ]);
        }

        if ($activeTransfersCount > 0) {
            $notifications->push([
                'type' => 'info',
                'title' => "{$activeTransfersCount} Transfer Stok Aktif",
                'message' => "Ada transfer stok antar cabang yang sedang berjalan.",
                'color' => 'blue'
            ]);
        }

        $metrics = [
            'revenue_today' => $totalRevenueToday,
            'revenue_growth' => $revenueGrowth,
            'orders_today' => $totalOrdersToday,
            'orders_growth' => $ordersGrowth,
            'pending_orders' => $pendingOrders,
            'low_stock_count' => $lowStockCount,
        ];

        // Performa Cabang (Management only)
        $branchRevenue = collect();
        if ($user->isExecutive()) {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $startOfPrevMonth = Carbon::now()->subMonth()->startOfMonth();
            $endOfPrevMonth = Carbon::now()->subMonth()->endOfMonth();

            $branchRevenue = DB::table('branches')
                ->leftJoin('sales', function($join) use ($startOfMonth, $endOfPrevMonth, $startOfPrevMonth) {
                    $join->on('branches.id', '=', 'sales.branch_id')
                         ->where('sales.created_at', '>=', $startOfPrevMonth)
                         ->where('sales.created_at', '<=', Carbon::now());
                })
                ->select(
                    'branches.name',
                    DB::raw("SUM(CASE WHEN sales.created_at >= '{$startOfMonth->toDateTimeString()}' THEN 1 ELSE 0 END) as total_transactions"),
                    DB::raw("SUM(CASE WHEN sales.created_at >= '{$startOfMonth->toDateTimeString()}' THEN sales.total_amount ELSE 0 END) as total_revenue"),
                    DB::raw("SUM(CASE WHEN sales.created_at >= '{$startOfPrevMonth->toDateTimeString()}' AND sales.created_at <= '{$endOfPrevMonth->toDateTimeString()}' THEN sales.total_amount ELSE 0 END) as last_month_revenue")
                )
                ->groupBy('branches.id', 'branches.name')
                ->orderByDesc('total_revenue')
                ->get();

            // Calculate growth vs last month
            foreach ($branchRevenue as $item) {
                $item->growth = 0;
                if ($item->last_month_revenue > 0) {
                    $item->growth = (($item->total_revenue - $item->last_month_revenue) / $item->last_month_revenue) * 100;
                }
            }
        }

        // Initial Chart Data (All Platforms)
        $chartData = $this->getChartData('all');

        return view('pages.dashboard', compact(
            'metrics', 
            'chartData', 
            'recentOrders', 
            'notifications', 
            'attendanceSummary',
            'recentAttendances',
            'financeSummary',
            'recentCashTransactions',
            'topProducts',
            'productionSummary',
            'activeProductions',
            'criticalStockProducts',
            'recentTransfers',
            'branchRevenue'
        ))->with('activePage', 'dashboard');
    }

    public function chartDataApi(Request $request)
    {
        $platform = $request->query('platform', 'all');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return response()->json($this->getChartData($platform, $startDate, $endDate));
    }

    private function getChartData($platform, $startDate = null, $endDate = null)
    {
        // Parse dates or default to last 7 days
        $end = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();
        $start = $startDate ? Carbon::parse($startDate)->startOfDay() : $end->copy()->subDays(6)->startOfDay();

        // If it's a single day, show hourly
        if ($start->isSameDay($end->copy()->startOfDay())) {
            return $this->getHourlyChartData($platform, $start);
        }

        // Limit max range to 90 days
        if ($start->diffInDays($end) > 90) {
            $start = $end->copy()->subDays(90)->startOfDay();
        }

        $labels = collect();
        $totalDays = (int)$start->diffInDays($end->copy()->startOfDay());

        // When "all": return separate datasets for POS + E-Commerce
        if ($platform === 'all') {
            $posData = collect();
            $ecomData = collect();

            for ($i = 0; $i <= $totalDays; $i++) {
                $date = $start->copy()->addDays($i);
                $labels->push($date->locale('id')->isoFormat('D MMM'));

                $posQuery = Sale::whereDate('created_at', $date);
                $this->scopeByBranch($posQuery);
                $posData->push($posQuery->sum('total_amount'));

                $ecomValue = 0;
                if ($this->isExecutive() || $this->getUserBranchId() == 1) {
                    $ecomValue = EcommerceOrder::whereDate('order_date', $date)->sum('total_amount');
                }
                $ecomData->push($ecomValue);
            }

            return [
                'labels' => $labels->toArray(),
                'datasets' => [
                    ['label' => 'POS / Kasir', 'data' => $posData->toArray()],
                    ['label' => 'E-Commerce', 'data' => $ecomData->toArray()],
                ],
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'multi' => true,
                'isHourly' => false
            ];
        }

        // When "ecommerce": return separate datasets per platform
        if ($platform === 'ecommerce') {
            if (!$this->isExecutive() && $this->getUserBranchId() != 1) {
                return [
                    'labels' => [],
                    'datasets' => [],
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                    'multi' => true,
                    'isHourly' => false,
                    'unauthorized' => true
                ];
            }
            $platforms = EcommerceOrder::select('platform')->distinct()->pluck('platform');
            $platformsData = [];
            foreach ($platforms as $pf) {
                $platformsData[$pf] = collect();
            }

            for ($i = 0; $i <= $totalDays; $i++) {
                $date = $start->copy()->addDays($i);
                $labels->push($date->locale('id')->isoFormat('D MMM'));

                foreach ($platforms as $pf) {
                    $platformsData[$pf]->push(
                        EcommerceOrder::whereDate('order_date', $date)
                            ->where('platform', $pf)
                            ->sum('total_amount')
                    );
                }
            }

            $datasets = [];
            foreach ($platforms as $pf) {
                $datasets[] = ['label' => $pf, 'data' => $platformsData[$pf]->toArray()];
            }

            return [
                'labels' => $labels->toArray(),
                'datasets' => $datasets,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'multi' => true,
                'isHourly' => false
            ];
        }

        // Single channel: POS or specific e-commerce platform
        $singleData = collect();
        $channelLabel = $platform === 'pos' ? 'POS / Kasir' : $platform;

        for ($i = 0; $i <= $totalDays; $i++) {
            $date = $start->copy()->addDays($i);
            $labels->push($date->locale('id')->isoFormat('D MMM'));

            if ($platform === 'pos') {
                $posQuery = Sale::whereDate('created_at', $date);
                $this->scopeByBranch($posQuery);
                $singleData->push($posQuery->sum('total_amount'));
            } else {
                $ecomValue = 0;
                if ($this->isExecutive() || $this->getUserBranchId() == 1) {
                    $ecomValue = EcommerceOrder::whereDate('order_date', $date)
                        ->where('platform', 'like', "%{$platform}%")
                        ->sum('total_amount');
                }
                $singleData->push($ecomValue);
            }
        }

        return [
            'labels' => $labels->toArray(),
            'datasets' => [
                ['label' => $channelLabel, 'data' => $singleData->toArray()],
            ],
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'multi' => false,
            'isHourly' => false
        ];
    }

    private function getHourlyChartData($platform, $date)
    {
        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
        }

        if ($platform === 'all') {
            $posData = [];
            $ecomData = [];
            for ($hour = 0; $hour < 24; $hour++) {
                $posQuery = Sale::whereDate('created_at', $date)->whereHour('created_at', $hour);
                $this->scopeByBranch($posQuery);
                $posData[] = $posQuery->sum('total_amount');

                $ecomValue = 0;
                if ($this->isExecutive() || $this->getUserBranchId() == 1) {
                    $ecomValue = EcommerceOrder::whereDate('order_date', $date)->whereHour('created_at', $hour)->sum('total_amount');
                }
                $ecomData[] = $ecomValue;
            }
            $datasets = [
                ['label' => 'POS / Kasir', 'data' => $posData],
                ['label' => 'E-Commerce', 'data' => $ecomData],
            ];
            $multi = true;
        } elseif ($platform === 'ecommerce') {
            if (!$this->isExecutive() && $this->getUserBranchId() != 1) {
                return ['labels' => $labels, 'datasets' => [], 'multi' => true, 'isHourly' => true];
            }
            $platforms = EcommerceOrder::select('platform')->distinct()->pluck('platform');
            $datasets = [];
            foreach ($platforms as $pf) {
                $pData = [];
                for ($hour = 0; $hour < 24; $hour++) {
                    $pData[] = EcommerceOrder::whereDate('order_date', $date)
                        ->where('platform', $pf)
                        ->whereHour('created_at', $hour)
                        ->sum('total_amount');
                }
                $datasets[] = ['label' => $pf, 'data' => $pData];
            }
            $multi = true;
        } else {
            $singleData = [];
            for ($hour = 0; $hour < 24; $hour++) {
                if ($platform === 'pos') {
                    $q = Sale::whereDate('created_at', $date)->whereHour('created_at', $hour);
                    $this->scopeByBranch($q);
                    $singleData[] = $q->sum('total_amount');
                } else {
                    $singleData[] = EcommerceOrder::whereDate('order_date', $date)
                        ->where('platform', 'like', "%{$platform}%")
                        ->whereHour('created_at', $hour)
                        ->sum('total_amount');
                }
            }
            $datasets = [['label' => $platform === 'pos' ? 'POS / Kasir' : $platform, 'data' => $singleData]];
            $multi = false;
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
            'start_date' => $date->format('Y-m-d'),
            'end_date' => $date->format('Y-m-d'),
            'multi' => $multi,
            'isHourly' => true
        ];
    }
}

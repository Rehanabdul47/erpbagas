<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\EcommerceOrder;
use App\Models\Product;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\CashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. Sales & E-commerce Revenue Today
        $posRevenueToday = Sale::whereDate('created_at', $today)->sum('total_amount_paid');
        $ecommerceRevenueToday = EcommerceOrder::whereDate('order_date', $today)->sum('total_amount');
        $totalRevenueToday = $posRevenueToday + $ecommerceRevenueToday;

        $posRevenueYesterday = Sale::whereDate('created_at', $today->copy()->subDay())->sum('total_amount_paid');
        $ecommerceRevenueYesterday = EcommerceOrder::whereDate('order_date', $today->copy()->subDay())->sum('total_amount');
        $totalRevenueYesterday = $posRevenueYesterday + $ecommerceRevenueYesterday;
        
        $revenueGrowth = 0;
        if ($totalRevenueYesterday > 0) {
            $revenueGrowth = (($totalRevenueToday - $totalRevenueYesterday) / $totalRevenueYesterday) * 100;
        }

        // 2. Orders Today
        $posOrdersToday = Sale::whereDate('created_at', $today)->count();
        $ecommerceOrdersToday = EcommerceOrder::whereDate('order_date', $today)->count();
        $totalOrdersToday = $posOrdersToday + $ecommerceOrdersToday;

        $posOrdersYesterday = Sale::whereDate('created_at', $today->copy()->subDay())->count();
        $ecommerceOrdersYesterday = EcommerceOrder::whereDate('order_date', $today->copy()->subDay())->count();
        $totalOrdersYesterday = $posOrdersYesterday + $ecommerceOrdersYesterday;

        $ordersGrowth = 0;
        if ($totalOrdersYesterday > 0) {
            $ordersGrowth = (($totalOrdersToday - $totalOrdersYesterday) / $totalOrdersYesterday) * 100;
        }

        // 3. Pending Orders (E-commerce only usually, POS is instant)
        $pendingOrders = EcommerceOrder::where('status', 'pending')->count();

        // 4. Inventory Alerts (Low Stock)
        $lowStockCount = Product::whereColumn('stock', '<=', 'min_stock_level')->count();

        // 5. HRM: Employee Attendance Summary Today
        $totalEmployees = Employee::count();
        $presentCount = Attendance::whereDate('date', $today)->where('status', 'Present')->count();
        $sickCount = Attendance::whereDate('date', $today)->where('status', 'Sick')->count();
        $leaveCount = Attendance::whereDate('date', $today)->where('status', 'Leave')->count();
        $absentCount = $totalEmployees - ($presentCount + $sickCount + $leaveCount); // Assuming missing records mean absent

        $attendanceSummary = [
            'total' => $totalEmployees,
            'present' => $presentCount,
            'sick' => $sickCount,
            'leave' => $leaveCount,
            'absent' => max(0, $absentCount)
        ];

        // 6. Financial Summary (This Month)
        $startOfMonth = Carbon::now()->startOfMonth();
        $cashIn = CashTransaction::where('type', 'income')->whereDate('date', '>=', $startOfMonth)->sum('amount');
        $cashOut = CashTransaction::where('type', 'expense')->whereDate('date', '>=', $startOfMonth)->sum('amount');
        $balance = CashTransaction::sum(DB::raw("CASE WHEN type = 'income' THEN amount ELSE -amount END"));

        $financeSummary = [
            'income_this_month' => $cashIn,
            'expense_this_month' => $cashOut,
            'total_balance' => $balance
        ];

        // 7. Top Selling Products (Simple mock/aggregation for now - just selecting top 5 by stock sold if tracked, or random for display if complex DB joins needed quickly. Let's do a reliable query if we have SaleItem)
        // Since we don't have SaleItem model directly imported, let's fetch products sorted by some metric, or just get best products.
        // For accurate Top Products, we would join sale_items and ecommerce_order_items. 
        // For speed, let's assume we can query products based on a 'sold_count' column if it exists, or just use DB query on sale_items
        $topProducts = DB::table('products')
            ->select('products.id', 'products.name', 'categories.name as category_name', 'products.price', 'products.stock')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('products.stock', 'asc') // Fallback logic: lowest stock might be best seller if not tracked explicitly in DB without massive joins right now.
            ->take(5)
            ->get();
            
        // We will try an exact join if the tables exist: `sale_items` usually references `product_id`. `ecommerce_order_items` references `product_id`.
        try {
            $topProducts = DB::table('sale_items')
                ->select('products.name', 'categories.name as category', DB::raw('SUM(sale_items.quantity) as total_sold'), 'products.price')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
                ->groupBy('products.id', 'products.name', 'categories.name', 'products.price')
                ->orderByDesc('total_sold')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            // Table might not exist or be named differently, fallback kept above.
        }

        // --- RECENT ORDERS ---
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

        $recentPos = Sale::latest('created_at')->take(5)->get()->map(function($sale) {
            return [
                'id' => $sale->invoice_number,
                'channel' => 'POS / Kasir',
                'customer' => $sale->customer ? $sale->customer->name : 'Walk-in',
                'total' => $sale->total_amount_paid,
                'status' => 'completed',
                'date' => $sale->created_at
            ];
        });

        $recentOrders = $recentEcommerce->concat($recentPos)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        // --- NOTIFICATIONS ---
        $notifications = collect();
        
        if ($lowStockCount > 0) {
            $products = Product::whereColumn('stock', '<=', 'min_stock_level')->take(2)->get();
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

        $metrics = [
            'revenue_today' => $totalRevenueToday,
            'revenue_growth' => $revenueGrowth,
            'orders_today' => $totalOrdersToday,
            'orders_growth' => $ordersGrowth,
            'pending_orders' => $pendingOrders,
            'low_stock_count' => $lowStockCount,
        ];

        // Initial Chart Data (All Platforms)
        $chartData = $this->getChartData('all');

        return view('pages.dashboard', compact(
            'metrics', 
            'chartData', 
            'recentOrders', 
            'notifications', 
            'attendanceSummary', 
            'financeSummary', 
            'topProducts'
        ))->with('activePage', 'dashboard');
    }

    public function chartDataApi(Request $request)
    {
        $platform = $request->query('platform', 'all');
        return response()->json($this->getChartData($platform));
    }

    private function getChartData($platform)
    {
        $salesTrend = collect();
        $labels = collect();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels->push($date->locale('id')->isoFormat('D MMM'));
            
            $pos = 0;
            $ecom = 0;

            if ($platform === 'all' || $platform === 'pos') {
                $pos = Sale::whereDate('created_at', $date)->sum('total_amount_paid');
            }

            if ($platform === 'all' || !in_array($platform, ['all', 'pos'])) {
                $query = EcommerceOrder::whereDate('order_date', $date);
                if ($platform !== 'all' && $platform !== 'ecommerce') {
                     // specific platform like 'Tokopedia', 'Shopee'
                     $query->where('platform', 'like', "%{$platform}%");
                }
                $ecom = $query->sum('total_amount');
            }
            
            $salesTrend->push($pos + $ecom);
        }

        return [
            'labels' => $labels->toArray(),
            'data' => $salesTrend->toArray()
        ];
    }
}

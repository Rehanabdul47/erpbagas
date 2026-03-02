<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\CashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DigitalMarketingDashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $selectedPlatform = $request->query('platform', 'all');

        // Get unique platforms for filter
        $platforms = EcommerceOrder::distinct()->pluck('platform')->filter()->values();

        $query = EcommerceOrder::whereMonth('order_date', $currentMonth)
            ->whereYear('order_date', $currentYear)
            ->where('status', '!=', 'cancelled');

        if ($selectedPlatform !== 'all') {
            $query->where('platform', $selectedPlatform);
        }

        // Metrik Bulan Ini
        $totalOrders = (clone $query)->count();
        $totalGrossRevenue = (clone $query)->sum('total_amount');
        $totalFees = (clone $query)->select(DB::raw('SUM(platform_fee + fixed_fee + service_fee) as total'))->first()->total ?? 0;
        
        // Total Pemasukan setelah dipotong fee platform
        $totalRevenue = $totalGrossRevenue - $totalFees;

        // Biaya Pengeluaran (Iklan & Marketing Online) dari Buku Kas
        $totalExpense = CashTransaction::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->where('type', 'out')
            ->where(function ($q) {
                $q->where('category', 'like', '%Iklan%')
                  ->orWhere('category', 'like', '%Marketing%')
                  ->orWhere('description', 'like', '%Ads%');
            })
            ->sum('amount');

        // Kalkulasi ROAS (Return on Ad Spend) - Menggunakan Gross Revenue untuk ROAS standar
        $roas = $totalExpense > 0 ? $totalGrossRevenue / $totalExpense : 0;

        // Net Revenue (Pemasukan Bersih setelah Fee & Biaya Iklan)
        $netRevenue = $totalRevenue - $totalExpense;

        // Kalkulasi AOV (Average Order Value)
        $aov = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Pelanggan Baru Bulan Ini
        $newCustomersQuery = EcommerceOrder::whereMonth('order_date', $currentMonth)
            ->whereYear('order_date', $currentYear)
            ->distinct('customer_email');

        if ($selectedPlatform !== 'all') {
            $newCustomersQuery->where('platform', $selectedPlatform);
        }

        $newCustomers = $newCustomersQuery->count('customer_email');

        // Pesanan Terbaru (Online Only)
        $recentOrdersQuery = EcommerceOrder::with('items.product')
            ->latest('order_date');

        if ($selectedPlatform !== 'all') {
            $recentOrdersQuery->where('platform', $selectedPlatform);
        }

        $recentOrders = $recentOrdersQuery->take(5)
            ->get()
            ->map(function ($order) {
                return (object)[
                    'id' => $order->id,
                    'reference' => '#' . $order->order_number,
                    'customer_name' => $order->customer_name,
                    'date' => $order->order_date,
                    'amount' => $order->total_amount,
                    'platform' => $order->platform ?: 'Online',
                    'status' => strtolower($order->status),
                    'route' => route('orders.show', $order->id),
                    'is_redeem' => false // Bisa di-expand jika e-commerce support redeem codes
                ];
            });

        // Produk Terlaris via Online (Bulan Ini)
        $topOnlineProducts = EcommerceOrderItem::with('product')
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function ($q) use ($currentMonth, $currentYear, $selectedPlatform) {
                $q->whereMonth('order_date', $currentMonth)
                  ->whereYear('order_date', $currentYear)
                  ->where('status', '!=', 'cancelled');
                
                if ($selectedPlatform !== 'all') {
                    $q->where('platform', $selectedPlatform);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // Data Grafik 30 Hari Terakhir
        $chartData = $this->getChartData($selectedPlatform, $platforms);

        return view('pages.digital-marketing-dashboard.index', compact(
            'totalOrders',
            'totalGrossRevenue',
            'totalRevenue',
            'totalFees',
            'totalExpense',
            'netRevenue',
            'roas',
            'aov',
            'newCustomers',
            'recentOrders',
            'topOnlineProducts',
            'chartData',
            'platforms',
            'selectedPlatform'
        ))->with('activePage', 'digital-marketing-dashboard');
    }

    private function getChartData($selectedPlatform, $allPlatforms)
    {
        $days = [];
        $datasets = [];

        // If 'all' is selected, we show multiple lines for comparison
        if ($selectedPlatform === 'all') {
            foreach ($allPlatforms as $platform) {
                $datasets[$platform] = [
                    'label' => $platform,
                    'data' => []
                ];
            }
            $datasets['Total'] = [
                'label' => 'Total Revenue',
                'data' => []
            ];
        } else {
            $datasets[$selectedPlatform] = [
                'label' => 'Revenue ' . $selectedPlatform,
                'data' => []
            ];
            $datasets['Orders'] = [
                'label' => 'Orders Count',
                'data' => []
            ];
        }

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('d M');

            if ($selectedPlatform === 'all') {
                $totalDayRevenue = 0;
                foreach ($allPlatforms as $platform) {
                    $rev = EcommerceOrder::whereDate('order_date', $date->toDateString())
                        ->where('platform', $platform)
                        ->where('status', '!=', 'cancelled')
                        ->sum('total_amount');
                    $datasets[$platform]['data'][] = $rev;
                    $totalDayRevenue += $rev;
                }
                $datasets['Total']['data'][] = $totalDayRevenue;
                
                // Add total daily expense for 'all' mode
                if (!isset($datasets['Expenses'])) {
                    $datasets['Expenses'] = [
                        'label' => 'Total Expenses',
                        'data' => []
                    ];
                }
                $datasets['Expenses']['data'][] = CashTransaction::whereDate('date', $date->toDateString())
                    ->where('type', 'out')
                    ->where(function ($q) {
                        $q->where('category', 'like', '%Iklan%')
                          ->orWhere('category', 'like', '%Marketing%')
                          ->orWhere('description', 'like', '%Ads%');
                    })
                    ->sum('amount');

            } else {
                $rev = EcommerceOrder::whereDate('order_date', $date->toDateString())
                    ->where('platform', $selectedPlatform)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount');
                $ord = EcommerceOrder::whereDate('order_date', $date->toDateString())
                    ->where('platform', $selectedPlatform)
                    ->where('status', '!=', 'cancelled')
                    ->count();
                
                $datasets[$selectedPlatform]['data'][] = $rev;
                $datasets['Orders']['data'][] = $ord;
            }
        }

        // Clean up datasets array to prevent indexed keys issue if needed, but array_values is already at end
        return [
            'labels' => $days,
            'datasets' => array_values($datasets),
            'isComparison' => ($selectedPlatform === 'all')
        ];
    }
}

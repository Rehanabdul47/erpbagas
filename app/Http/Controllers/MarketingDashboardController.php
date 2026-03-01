<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\RedeemCode;
use Carbon\Carbon;

class MarketingDashboardController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Metrics
        $totalSalesPos = Sale::whereMonth('sale_date', $currentMonth)
            ->whereYear('sale_date', $currentYear)
            ->where('status', 'completed')
            ->count();
            
        $totalSalesEcommerce = \App\Models\EcommerceOrder::whereMonth('order_date', $currentMonth)
            ->whereYear('order_date', $currentYear)
            ->where('status', '!=', 'cancelled') // Count non-cancelled orders
            ->count();
            
        $totalSalesMonth = $totalSalesPos + $totalSalesEcommerce;

        $totalRevenuePos = Sale::whereMonth('sale_date', $currentMonth)
            ->whereYear('sale_date', $currentYear)
            ->where('status', 'completed')
            ->sum('total_amount');
            
        $totalRevenueEcommerce = \App\Models\EcommerceOrder::whereMonth('order_date', $currentMonth)
            ->whereYear('order_date', $currentYear)
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
            
        $totalRevenueMonth = $totalRevenuePos + $totalRevenueEcommerce;

        $activeCustomers = Customer::count();

        $activeRedeemCodes = RedeemCode::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
            })->count();

        // Lists for widgets
        $recentPos = Sale::with(['customer', 'redeemCode'])
            ->latest('sale_date')
            ->take(5)
            ->get()
            ->map(function ($item) {
                // Normalize structure for view
                return (object)[
                    'id' => 'POS-'.$item->id,
                    'reference' => $item->invoice_no,
                    'customer_name' => $item->customer->name ?? 'Walk-in Customer',
                    'date' => $item->sale_date,
                    'amount' => $item->total_amount,
                    'platform' => 'Offline (POS)',
                    'status' => 'completed',
                    'route' => route('pos.invoice', $item->id),
                    'is_redeem' => $item->redeem_code_id !== null,
                ];
            });

        $recentEcommerce = \App\Models\EcommerceOrder::latest('order_date')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return (object)[
                    'id' => 'EC-'.$item->id,
                    'reference' => $item->order_number,
                    'customer_name' => $item->customer_name,
                    'date' => $item->order_date,
                    'amount' => $item->total_amount,
                    'platform' => $item->platform,
                    'status' => $item->status,
                    'route' => route('orders.show', $item->id),
                    'is_redeem' => false, // E-commerce handle their own coupons usually on platform
                ];
            });

        // Combine and sort recent transactions
        $recentTransactions = $recentPos->concat($recentEcommerce)
            ->sortByDesc('date')
            ->take(5)
            ->values();

        $newCustomers = Customer::latest()->take(5)->get();

        $activeCoupons = RedeemCode::where('is_active', true)
            ->withCount('sales')
            ->orderBy('sales_count', 'desc')
            ->take(5)
            ->get();
            
        // Combined Top Products
        $topPosProducts = \App\Models\SaleItem::with('product')
            ->select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('sale', function($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('sale_date', $currentMonth)
                  ->whereYear('sale_date', $currentYear)
                  ->where('status', 'completed');
            })
            ->groupBy('product_id')
            ->get();
            
        $topEcommerceProducts = \App\Models\EcommerceOrderItem::with('product')
            ->select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('order', function($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('order_date', $currentMonth)
                  ->whereYear('order_date', $currentYear)
                  ->where('status', '!=', 'cancelled');
            })
            ->groupBy('product_id')
            ->get();

        $topProductsMap = [];
        
        foreach ($topPosProducts as $item) {
            $topProductsMap[$item->product_id] = [
                'product' => $item->product,
                'total_sold' => (int)$item->total_sold
            ];
        }
        
        foreach ($topEcommerceProducts as $item) {
            if (isset($topProductsMap[$item->product_id])) {
                $topProductsMap[$item->product_id]['total_sold'] += (int)$item->total_sold;
            } else {
                $topProductsMap[$item->product_id] = [
                    'product' => $item->product,
                    'total_sold' => (int)$item->total_sold
                ];
            }
        }
        
        // Sort mapped array desc and take 5
        usort($topProductsMap, function($a, $b) {
            return $b['total_sold'] <=> $a['total_sold'];
        });
        
        $topProducts = collect(array_slice($topProductsMap, 0, 5))->map(function($item) {
            return (object)$item;
        });
            
        $activePage = 'marketing-dashboard';

        return view('pages.marketing-dashboard.index', compact(
            'totalSalesMonth', 
            'totalRevenueMonth', 
            'activeCustomers', 
            'activeRedeemCodes',
            'recentTransactions',
            'newCustomers',
            'activeCoupons',
            'topProducts',
            'activePage'
        ));
    }
}

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
        $totalSalesMonth = Sale::whereMonth('sale_date', $currentMonth)
            ->whereYear('sale_date', $currentYear)
            ->where('status', 'completed')
            ->count();

        $totalRevenueMonth = Sale::whereMonth('sale_date', $currentMonth)
            ->whereYear('sale_date', $currentYear)
            ->where('status', 'completed')
            ->sum('total_amount');

        $activeCustomers = Customer::count();

        $activeRedeemCodes = RedeemCode::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
            })->count();

        // Lists for widgets
        $recentTransactions = Sale::with(['customer', 'redeemCode'])
            ->latest()
            ->take(5)
            ->get();

        $newCustomers = Customer::latest()->take(5)->get();

        $activeCoupons = RedeemCode::where('is_active', true)
            ->withCount('sales')
            ->orderBy('sales_count', 'desc')
            ->take(5)
            ->get();
            
        $activePage = 'marketing-dashboard';

        return view('pages.marketing-dashboard.index', compact(
            'totalSalesMonth', 
            'totalRevenueMonth', 
            'activeCustomers', 
            'activeRedeemCodes',
            'recentTransactions',
            'newCustomers',
            'activeCoupons',
            'activePage'
        ));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DigitalMarketingDashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

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

        // Placeholder metrics for rebuild
        $totalOrders = 0;
        $totalGrossRevenue = 0;
        $totalRevenue = 0;
        $totalFees = 0;
        $netRevenue = $totalRevenue - $totalExpense;
        $roas = 0;
        $aov = 0;
        $newCustomers = 0;
        $recentOrders = collect();
        $topOnlineProducts = collect();
        $chartData = [
            'labels' => [],
            'datasets' => [],
            'isComparison' => false
        ];
        $platforms = collect();
        $selectedPlatform = 'all';

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
}

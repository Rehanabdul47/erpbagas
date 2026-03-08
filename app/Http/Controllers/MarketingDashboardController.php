<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\RedeemCode;
use App\Models\Branch;
use Carbon\Carbon;

class MarketingDashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $branchId = $request->input('branch_id', 'all');
        if (!auth()->user()->hasRole('Executive')) {
            $branchId = auth()->user()->branch_id;
            // Normalize so those with null branch id (Pusat) are treated as branch 1 (Pusat logic)
            if ($branchId === null) {
                $branchId = 1;
            }
        }
        $startDate = $request->input('start_date', Carbon::now()->subDays(29)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        $branches = Branch::latest()->get();

        // Metrics within date range
        $posQuery = Sale::whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->whereIn('status', ['paid', 'completed']);
            
        if ($branchId !== 'all') {
            if ($branchId == 1) { // Assuming Branch 1 is Main Store (Pusat)
                $posQuery->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            } else {
                $posQuery->where('branch_id', $branchId);
            }
        }

        $totalSalesMonth = (clone $posQuery)->count();
        $totalRevenueMonth = (clone $posQuery)->sum('total_amount');

        $activeCustomers = Customer::count();

        $activeRedeemCodes = RedeemCode::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('valid_until')
                      ->orWhere('valid_until', '>=', now());
            })->count();

        // Lists for widgets within date range
        $recentPosQuery = Sale::with(['customer', 'redeemCode', 'branch'])
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->latest('sale_date');
            
        if ($branchId !== 'all') {
            if ($branchId == 1) {
                $recentPosQuery->where(function($q) use ($branchId) {
                    $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                });
            } else {
                $recentPosQuery->where('branch_id', $branchId);
            }
        }
            
        $recentPos = $recentPosQuery->take(5)
            ->get()
            ->map(function ($item) {
                // Normalize structure for view
                return (object)[
                    'id' => 'POS-'.$item->id,
                    'reference' => $item->invoice_no,
                    'customer_name' => $item->customer->name ?? 'Walk-in Customer',
                    'date' => $item->sale_date,
                    'amount' => $item->total_amount,
                    'platform' => 'Offline' . ($item->branch ? ' - ' . $item->branch->name : ' (Pusat)'),
                    'status' => in_array($item->status, ['paid', 'completed']) ? 'completed' : $item->status,
                    'route' => route('pos.invoice', $item->id),
                    'is_redeem' => $item->redeem_code_id !== null,
                ];
            });

        // Recent transactions focusing only on POS
        $recentTransactions = $recentPos;

        $newCustomers = Customer::latest()->take(5)->get();

        $activeCoupons = RedeemCode::where('is_active', true)
            ->withCount('sales')
            ->orderBy('sales_count', 'desc')
            ->take(5)
            ->get();
            
        // Combined Top Products
        $topPosProducts = \App\Models\SaleItem::with('product')
            ->select('product_id', \Illuminate\Support\Facades\DB::raw('SUM(quantity) as total_sold'))
            ->whereHas('sale', function($q) use ($startDate, $endDate, $branchId) {
                $q->whereDate('sale_date', '>=', $startDate)
                  ->whereDate('sale_date', '<=', $endDate)
                  ->whereIn('status', ['paid', 'completed']);
                
                if ($branchId !== 'all') {
                    if ($branchId == 1) {
                        $q->where(function($sub) use ($branchId) {
                            $sub->where('branch_id', $branchId)->orWhereNull('branch_id');
                        });
                    } else {
                        $q->where('branch_id', $branchId);
                    }
                }
            })
            ->groupBy('product_id')
            ->get();
            
        $topProducts = $topPosProducts->map(function($item) {
            return (object)[
                'product' => $item->product,
                'total_sold' => (int)$item->total_sold
            ];
        })->take(5);

        // Chart Data
        $chartData = $this->getChartData($branchId, $startDate, $endDate);
            
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
            'activePage',
            'branches',
            'branchId',
            'startDate',
            'endDate',
            'chartData'
        ));
    }

    private function getChartData($branchId, $startDate, $endDate)
    {
        $days = [];
        $datasets = [];
        $isComparison = ($branchId === 'all');
        $branches = \App\Models\Branch::all();

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $diffInDays = $start->diffInDays($end);

        // Limit range to prevent performance issues (e.g., max 60 days)
        if ($diffInDays > 60) {
            $start = $end->copy()->subDays(60);
            $diffInDays = 60;
        }

        // Get only unique dates that have sales within the range
        $days = Sale::whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->whereIn('status', ['paid', 'completed'])
            ->select(\DB::raw('DATE(sale_date) as date'))
            ->distinct()
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('d M'))
            ->toArray();

        // If no data, show at least start and end date to prevent empty chart error
        if (empty($days)) {
            $days = [Carbon::parse($startDate)->format('d M'), Carbon::parse($endDate)->format('d M')];
        }

        if ($isComparison) {
            // Dataset for Total
            $totalData = [];
            foreach ($days as $dayLabel) {
                $date = Carbon::createFromFormat('d M', $dayLabel)->year(Carbon::now()->year);
                $totalData[] = Sale::whereDate('sale_date', $date->toDateString())
                    ->whereIn('status', ['paid', 'completed'])
                    ->sum('total_amount');
            }
            $datasets[] = [
                'label' => 'Total Penjualan POS',
                'data' => $totalData,
            ];

            // Datasets for each Branch
            foreach ($branches as $branch) {
                $branchData = [];
                foreach ($days as $dayLabel) {
                    $date = Carbon::createFromFormat('d M', $dayLabel)->year(Carbon::now()->year);
                    $query = Sale::whereDate('sale_date', $date->toDateString())
                        ->whereIn('status', ['paid', 'completed']);
                    
                    if ($branch->id == 1) {
                        $query->where(function($q) use ($branch) {
                            $q->where('branch_id', $branch->id)->orWhereNull('branch_id');
                        });
                    } else {
                        $query->where('branch_id', $branch->id);
                    }
                    
                    $branchData[] = $query->sum('total_amount');
                }
                $datasets[] = [
                    'label' => 'POS ' . $branch->name,
                    'data' => $branchData,
                ];
            }
        } else {
            // Specific Branch: Revenue & Orders Count
            $revenueData = [];
            $ordersData = [];
            foreach ($days as $dayLabel) {
                $date = Carbon::createFromFormat('d M', $dayLabel)->year(Carbon::now()->year);
                $query = Sale::whereDate('sale_date', $date->toDateString())
                    ->whereIn('status', ['paid', 'completed']);
                
                if ($branchId == 1) {
                    $query->where(function($q) use ($branchId) {
                        $q->where('branch_id', $branchId)->orWhereNull('branch_id');
                    });
                } else {
                    $query->where('branch_id', $branchId);
                }
                
                $revenueData[] = (clone $query)->sum('total_amount');
                $ordersData[] = (clone $query)->count();
            }

            $datasets[] = [
                'label' => 'Pendapatan POS',
                'data' => $revenueData,
            ];
            $datasets[] = [
                'label' => 'Jumlah Transaksi POS',
                'data' => $ordersData,
            ];
        }

        return [
            'labels' => $days,
            'datasets' => $datasets,
            'isComparison' => $isComparison
        ];
    }
}

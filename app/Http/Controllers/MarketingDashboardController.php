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

    public function chartApi(Request $request)
    {
        $branchId = $request->input('branch_id', 'all');
        $startDate = $request->input('start_date', Carbon::now()->subDays(29)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        return response()->json($this->getChartData($branchId, $startDate, $endDate));
    }

    private function getChartData($branchId, $startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        // If it's a single day, show hourly
        if ($start->isSameDay($end->copy()->startOfDay())) {
            return $this->getHourlyChartData($branchId, $start);
        }

        $labels = [];
        $diffInDays = (int)$start->diffInDays($end->copy()->startOfDay());
        
        // Limit range for performance
        if ($diffInDays > 90) {
            $start = $end->copy()->subDays(90)->startOfDay();
            $diffInDays = 90;
        }

        for ($i = 0; $i <= $diffInDays; $i++) {
            $day = $start->copy()->addDays($i);
            $labels[] = $day->format('d M');
        }

        $datasets = [];
        $isComparison = ($branchId === 'all');
        $branches = \App\Models\Branch::all();

        if ($isComparison) {
            // Total Dataset
            $totalData = [];
            foreach ($labels as $index => $label) {
                $date = $start->copy()->addDays($index);
                $totalData[] = Sale::whereDate('sale_date', $date->toDateString())
                    ->whereIn('status', ['paid', 'completed'])
                    ->sum('total_amount');
            }
            $datasets[] = [
                'label' => 'Total Penjualan POS',
                'data' => $totalData,
            ];

            // Branch Datasets
            foreach ($branches as $branch) {
                $branchData = [];
                foreach ($labels as $index => $label) {
                    $date = $start->copy()->addDays($index);
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
            $revenueData = [];
            $ordersData = [];
            foreach ($labels as $index => $label) {
                $date = $start->copy()->addDays($index);
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
            'labels' => $labels,
            'datasets' => $datasets,
            'isComparison' => $isComparison,
            'isHourly' => false
        ];
    }

    private function getHourlyChartData($branchId, $date)
    {
        $labels = [];
        for ($i = 0; $i < 24; $i++) {
            $labels[] = sprintf('%02d:00', $i);
        }

        $datasets = [];
        $isComparison = ($branchId === 'all');
        $branches = \App\Models\Branch::all();

        if ($isComparison) {
            $totalData = [];
            foreach (range(0, 23) as $hour) {
                $totalData[] = Sale::whereDate('sale_date', $date->toDateString())
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereHour('created_at', $hour)
                    ->sum('total_amount');
            }
            $datasets[] = ['label' => 'Total Penjualan POS', 'data' => $totalData];

            foreach ($branches as $branch) {
                $branchData = [];
                foreach (range(0, 23) as $hour) {
                    $query = Sale::whereDate('sale_date', $date->toDateString())
                        ->whereIn('status', ['paid', 'completed'])
                        ->whereHour('created_at', $hour);
                    
                    if ($branch->id == 1) {
                        $query->where(function($q) use ($branch) {
                            $q->where('branch_id', $branch->id)->orWhereNull('branch_id');
                        });
                    } else {
                        $query->where('branch_id', $branch->id);
                    }
                    $branchData[] = $query->sum('total_amount');
                }
                $datasets[] = ['label' => 'POS ' . $branch->name, 'data' => $branchData];
            }
        } else {
            $revenueData = [];
            $ordersData = [];
            foreach (range(0, 23) as $hour) {
                $query = Sale::whereDate('sale_date', $date->toDateString())
                    ->whereIn('status', ['paid', 'completed'])
                    ->whereHour('created_at', $hour);
                
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
            $datasets[] = ['label' => 'Pendapatan POS', 'data' => $revenueData];
            $datasets[] = ['label' => 'Jumlah Transaksi POS', 'data' => $ordersData];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
            'isComparison' => $isComparison,
            'isHourly' => true
        ];
    }
}

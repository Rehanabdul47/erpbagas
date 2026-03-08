<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->input('branch_id', 'all');
        $branches = \App\Models\Branch::latest()->get();

        $customers = Customer::withCount('sales')->get();
        
        $salesQuery = Sale::with('customer', 'branch')->latest();
        
        if (!auth()->user()->hasRole('Executive')) {
            $userBranchId = auth()->user()->branch_id;
            if ($userBranchId == 1) { // Pusat
                $salesQuery->where(function($q) use ($userBranchId) {
                    $q->where('branch_id', $userBranchId)->orWhereNull('branch_id');
                });
                $totalRevenueQuery = Sale::whereMonth('sale_date', Carbon::now()->month)
                    ->where(function($q) use ($userBranchId) {
                        $q->where('branch_id', $userBranchId)->orWhereNull('branch_id');
                    });
                $totalSalesQuery = Sale::where(function($q) use ($userBranchId) {
                    $q->where('branch_id', $userBranchId)->orWhereNull('branch_id');
                });
            } else {
                $salesQuery->where('branch_id', $userBranchId);
                $totalRevenueQuery = Sale::whereMonth('sale_date', Carbon::now()->month)->where('branch_id', $userBranchId);
                $totalSalesQuery = Sale::where('branch_id', $userBranchId);
            }
            $branchId = $userBranchId;
        } else if ($branchId !== 'all') {
            $salesQuery->where('branch_id', $branchId);
            $totalRevenueQuery = Sale::whereMonth('sale_date', Carbon::now()->month)->where('branch_id', $branchId);
            $totalSalesQuery = Sale::where('branch_id', $branchId);
        } else {
            $totalRevenueQuery = Sale::whereMonth('sale_date', Carbon::now()->month);
            $totalSalesQuery = Sale::query();
        }

        $sales = $salesQuery->limit(20)->get();
        
        $stats = [
            'totalRevenue' => $totalRevenueQuery->sum('total_amount'),
            'totalCustomers' => $customers->count(),
            'totalSales' => $totalSalesQuery->count(),
            'revenueStatus' => 'up', // Mocking trend for UI
        ];

        return view('pages.sales.index', compact('customers', 'sales', 'stats', 'branches', 'branchId'))
            ->with('activePage', 'sales');
    }

    public function storeCustomer(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,leads',
        ]);

        Customer::create($validated);

        return redirect()->route('sales.index', ['tab' => 'customers'])->with('success', 'Customer berhasil ditambahkan');
    }

    public function storeQuick(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $validated['status'] = 'active'; // Default for quick POS creation
        
        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function updateCustomer(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'status' => 'required|in:active,inactive,leads',
        ]);

        $customer->update($validated);

        return redirect()->route('sales.index', ['tab' => 'customers'])->with('success', 'Data customer berhasil diupdate');
    }

    public function destroyCustomer(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('sales.index', ['tab' => 'customers'])->with('success', 'Customer berhasil dihapus');
    }
}

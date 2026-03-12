<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Product;
use App\Traits\BranchScope;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesController extends Controller
{
    use BranchScope;

    public function index(Request $request)
    {
        $branchId = $request->input('branch_id', 'all');
        $branches = $this->getBranchesForFilter();

        if (!$this->isExecutive()) {
            $branchId = auth()->user()->branch_id ?? 1;
        }

        $customersQuery = Customer::withCount('sales');
        $this->scopeByBranch($customersQuery, $branchId);
        $customers = $customersQuery->get();
        
        $salesQuery = Sale::with('customer', 'branch')->latest();
        $this->scopeByBranch($salesQuery, $branchId);

        $totalRevenueQuery = Sale::whereMonth('sale_date', Carbon::now()->month);
        $this->scopeByBranch($totalRevenueQuery, $branchId);

        $totalSalesQuery = Sale::query();
        $this->scopeByBranch($totalSalesQuery, $branchId);

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

        $validated['branch_id'] = auth()->user()->branch_id;

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
        $validated['branch_id'] = auth()->user()->branch_id;
        
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

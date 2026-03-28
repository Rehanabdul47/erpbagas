<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalItem;
use App\Models\AuditTrail;
use App\Traits\BranchScope;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    use BranchScope;

    public function index()
    {
        $budgets = Budget::with(['fiscalPeriod', 'creator', 'items'])
            ->latest()
            ->get();

        return view('pages.finance.budgets.index', compact('budgets'))
            ->with('activePage', 'budgets');
    }

    public function create()
    {
        $periods = FiscalPeriod::orderBy('start_date', 'desc')->get();
        $accounts = Account::postable()->active()
            ->whereIn('type', ['expense', 'revenue'])
            ->orderBy('code')
            ->get();

        return view('pages.finance.budgets.create', compact('periods', 'accounts'))
            ->with('activePage', 'budgets');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'fiscal_period_id' => 'required|exists:fiscal_periods,id',
            'department' => 'nullable|string|max:255',
            'cost_center' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.planned_amount' => 'required|numeric|min:0',
        ]);

        $budget = Budget::create([
            'name' => $request->name,
            'fiscal_period_id' => $request->fiscal_period_id,
            'department' => $request->department,
            'cost_center' => $request->cost_center,
            'status' => 'draft',
            'created_by' => auth()->id(),
            'notes' => $request->notes,
        ]);

        foreach ($request->items as $item) {
            if (($item['planned_amount'] ?? 0) > 0) {
                $budget->items()->create([
                    'account_id' => $item['account_id'],
                    'planned_amount' => $item['planned_amount'],
                ]);
            }
        }

        AuditTrail::record($budget, 'created', null, $budget->toArray(), "Anggaran baru: {$budget->name}");

        return redirect()->route('finance.budgets.index')->with('success', "Anggaran '{$budget->name}' berhasil dibuat.");
    }

    public function report(Request $request)
    {
        $budgetId = $request->input('budget_id');
        $budget = null;
        $reportData = [];

        $budgets = Budget::with('fiscalPeriod')->orderBy('name')->get();

        if ($budgetId) {
            $budget = Budget::with(['items.account', 'fiscalPeriod'])->findOrFail($budgetId);
            $period = $budget->fiscalPeriod;

            foreach ($budget->items as $item) {
                $actualQuery = JournalItem::where('account_id', $item->account_id)
                    ->whereHas('journal', function ($q) use ($period) {
                        $q->where('status', '!=', 'void')
                          ->whereBetween('date', [$period->start_date, $period->end_date]);
                        $this->scopeByBranch($q);
                    });

                $actualDebit = (clone $actualQuery)->sum('debit');
                $actualCredit = (clone $actualQuery)->sum('credit');

                // For expense accounts, actual = debit side; for revenue, actual = credit side
                $actual = $item->account->isDebitNormal()
                    ? $actualDebit - $actualCredit
                    : $actualCredit - $actualDebit;

                $variance = $item->planned_amount - $actual;
                $variancePercent = $item->planned_amount > 0
                    ? round(($variance / $item->planned_amount) * 100, 1)
                    : 0;

                $reportData[] = [
                    'account' => $item->account,
                    'planned' => $item->planned_amount,
                    'actual' => $actual,
                    'variance' => $variance,
                    'variance_percent' => $variancePercent,
                    'status' => $variance >= 0 ? 'under' : 'over',
                ];
            }
        }

        return view('pages.finance.budgets.report', compact('budgets', 'budget', 'reportData', 'budgetId'))
            ->with('activePage', 'budgets');
    }
}

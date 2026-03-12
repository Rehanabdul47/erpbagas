<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Traits\BranchScope;
use Carbon\Carbon;

class FinanceReportController extends Controller
{
    use BranchScope;

    public function index(Request $request)
    {
        $month = $request->input('month', date('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 1. Laba Rugi (Income Statement) - for the period
        $revenues = $this->getAccountBalances('revenue', $startDate, $endDate);
        $expenses = $this->getAccountBalances('expense', $startDate, $endDate);

        $totalRevenue = $revenues->sum('period_balance');
        $totalExpense = $expenses->sum('period_balance');
        $netIncome = $totalRevenue - $totalExpense;

        // 2. Neraca (Balance Sheet) - as of end date
        $assets = $this->getAccountBalances('asset', null, $endDate);
        $liabilities = $this->getAccountBalances('liability', null, $endDate);
        $equities = $this->getAccountBalances('equity', null, $endDate);

        $totalAssets = $assets->sum('period_balance');
        $totalLiabilities = $liabilities->sum('period_balance');
        $totalEquityBase = $equities->sum('period_balance');

        // Laba Ditahan (Retained Earnings) percentage from settings, default 40%
        $rePercentage = \App\Models\Setting::where('key', 'finance_retained_earnings_percentage')->value('value') ?? 40;
        $rePercentage = (float) $rePercentage / 100;
        
        $retainedEarnings = $netIncome * $rePercentage;
        $currentEarnings = $netIncome * (1 - $rePercentage);
        
        $totalEquity = $totalEquityBase + $retainedEarnings + $currentEarnings;

        // 3. Arus Kas (Cash Flow) - simplified
        $cashAccount = Account::where('code', '1-101')->first();
        $cashInitial = 0;
        $cashInflow = 0;
        $cashOutflow = 0;
        $cashEnding = 0;

        if ($cashAccount) {
            $prevEndDate = $startDate->copy()->subDay();
            $cashInitialArr = $this->getAccountBalances('asset', null, $prevEndDate)->where('code', '1-101')->first();
            $cashInitial = $cashInitialArr ? $cashInitialArr->period_balance : 0;

            $cashCurrentArr = $this->getAccountBalances('asset', $startDate, $endDate)->where('code', '1-101')->first();
            if ($cashCurrentArr) {
                $cashInflow = $cashCurrentArr->total_debit; // Kas bertambah di debit
                $cashOutflow = $cashCurrentArr->total_credit; // Kas berkurang di kredit
            }
            $cashEnding = $cashInitial + $cashInflow - $cashOutflow;
        }

        // 4. Perubahan Ekuitas (Changes in Equity)
        // Beginning Equity = Equity up to prevEndDate + Retained Earnings up to prevEndDate
        $prevAssets = $this->getAccountBalances('asset', null, $prevEndDate); // not needed
        $prevEquities = $this->getAccountBalances('equity', null, $prevEndDate);
        $prevRevenues = $this->getAccountBalances('revenue', null, $prevEndDate);
        $prevExpenses = $this->getAccountBalances('expense', null, $prevEndDate);
        
        $beginningEquity = $prevEquities->sum('period_balance') + ($prevRevenues->sum('period_balance') - $prevExpenses->sum('period_balance'));
        
        // Prive/Dividends (Equity account 3-102) during period
        $priveAccount = $equities->where('code', '3-102')->first();
        $prive = $priveAccount ? abs($priveAccount->period_balance) : 0; // usually debit balance (- balance in our calc)
        
        $endingEquity = $beginningEquity + $netIncome - $prive; // Net income already calculated

        return view('pages.finance.reports.index', compact(
            'month', 'startDate', 'endDate',
            'revenues', 'expenses', 'totalRevenue', 'totalExpense', 'netIncome',
            'assets', 'liabilities', 'equities', 'totalAssets', 'totalLiabilities', 'totalEquityBase', 'retainedEarnings', 'currentEarnings', 'totalEquity',
            'cashInitial', 'cashInflow', 'cashOutflow', 'cashEnding',
            'beginningEquity', 'prive', 'endingEquity', 'rePercentage'
        ))->with('activePage', 'reports');
    }

    private function getAccountBalances($type, $startDate = null, $endDate = null)
    {
        $accounts = Account::where('type', $type)->with(['journalItems' => function($q) use ($startDate, $endDate) {
            $q->whereHas('journal', function($q) use ($startDate, $endDate) {
                $this->scopeByBranch($q);
                if ($startDate) {
                    $q->where('date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->where('date', '<=', $endDate);
                }
            });
        }])->get();

        foreach ($accounts as $acc) {
            $debit = $acc->journalItems->sum('debit');
            $credit = $acc->journalItems->sum('credit');
            $acc->total_debit = $debit;
            $acc->total_credit = $credit;

            // Normal balances:
            // Asset, Expense = Debit
            // Liability, Equity, Revenue = Credit
            if (in_array($type, ['asset', 'expense'])) {
                $acc->period_balance = $debit - $credit;
            } else {
                $acc->period_balance = $credit - $debit;
            }
        }

        return $accounts;
    }
}

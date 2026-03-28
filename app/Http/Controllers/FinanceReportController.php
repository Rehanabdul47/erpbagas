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

        $data = $this->getReportData($startDate, $endDate);
        $data['month'] = $month;

        return view('pages.finance.reports.index', $data)->with('activePage', 'reports');
    }

    public function print(Request $request)
    {
        $month = $request->input('month', date('Y-m'));
        $standard = $request->input('standard', 'psak');
        // Reports array: can contain 'lr' (Laba Rugi), 'neraca', 'ak' (Arus Kas), 'pe' (Perubahan Ekuitas)
        // If not provided or contains 'all', we print all
        $selectedReports = $request->input('reports', ['all']);
        
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $data = $this->getReportData($startDate, $endDate);
        $data['month'] = $month;
        $data['standard'] = $standard;
        $data['selectedReports'] = is_array($selectedReports) ? $selectedReports : [];

        // Additional data for Syariah
        if ($standard === 'syariah') {
            $data['zakatIn'] = 0;
            $data['zakatOut'] = 0;
            $data['kebajikanIn'] = 0;
            $data['kebajikanOut'] = 0;
            
            // Look for Zakat/Social funds if existing in COA
            $zakatAccount = Account::where('name', 'like', '%Zakat%')->first();
            if ($zakatAccount) {
                // Determine balance
            }
        }

        return view('pages.finance.reports.print', $data);
    }

    private function getReportData($startDate, $endDate)
    {
        // 1. Laba Rugi (Income Statement)
        $revenues = $this->getAccountBalances('revenue', $startDate, $endDate);
        $expenses = $this->getAccountBalances('expense', $startDate, $endDate);

        $totalRevenue = $revenues->sum('period_balance');
        $totalExpense = $expenses->sum('period_balance');
        $netIncome = $totalRevenue - $totalExpense;

        // 2. Neraca (Balance Sheet)
        $assets = $this->getAccountBalances('asset', null, $endDate);
        $liabilities = $this->getAccountBalances('liability', null, $endDate);
        $equities = $this->getAccountBalances('equity', null, $endDate);

        $totalAssets = $assets->sum('period_balance');
        $totalLiabilities = $liabilities->sum('period_balance');
        $totalEquityBase = $equities->sum('period_balance');

        $rePercentage = \App\Models\Setting::where('key', 'finance_retained_earnings_percentage')->value('value') ?? 40;
        $rePercentage = (float) $rePercentage / 100;
        
        $retainedEarnings = $netIncome * $rePercentage;
        $currentEarnings = $netIncome * (1 - $rePercentage);
        
        $totalEquity = $totalEquityBase + $retainedEarnings + $currentEarnings;

        // 3. Arus Kas (Cash Flow)
        $cashAccount = Account::where('code', '1-101')->first();
        $cashInitial = 0; $cashInflow = 0; $cashOutflow = 0; $cashEnding = 0;

        if ($cashAccount) {
            $prevEndDate = $startDate->copy()->subDay();
            $cashInitialArr = $this->getAccountBalances('asset', null, $prevEndDate)->where('code', '1-101')->first();
            $cashInitial = $cashInitialArr ? $cashInitialArr->period_balance : 0;

            $cashCurrentArr = $this->getAccountBalances('asset', $startDate, $endDate)->where('code', '1-101')->first();
            if ($cashCurrentArr) {
                $cashInflow = $cashCurrentArr->total_debit;
                $cashOutflow = $cashCurrentArr->total_credit;
            }
            $cashEnding = $cashInitial + $cashInflow - $cashOutflow;
        }

        // 4. Perubahan Ekuitas
        $prevEndDate = $startDate->copy()->subDay();
        $prevEquities = $this->getAccountBalances('equity', null, $prevEndDate);
        $prevRevenues = $this->getAccountBalances('revenue', null, $prevEndDate);
        $prevExpenses = $this->getAccountBalances('expense', null, $prevEndDate);
        
        $beginningEquity = $prevEquities->sum('period_balance') + ($prevRevenues->sum('period_balance') - $prevExpenses->sum('period_balance'));
        
        $priveAccount = $equities->where('code', '3-102')->first();
        $prive = $priveAccount ? abs($priveAccount->period_balance) : 0;
        
        $endingEquity = $beginningEquity + $netIncome - $prive;

        return compact(
            'startDate', 'endDate',
            'revenues', 'expenses', 'totalRevenue', 'totalExpense', 'netIncome',
            'assets', 'liabilities', 'equities', 'totalAssets', 'totalLiabilities', 'totalEquityBase', 
            'retainedEarnings', 'currentEarnings', 'totalEquity',
            'cashInitial', 'cashInflow', 'cashOutflow', 'cashEnding',
            'beginningEquity', 'prive', 'endingEquity', 'rePercentage'
        );
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

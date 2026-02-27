<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashTransaction;
use App\Models\Account;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // 1. Saldo Kas & Arus Kas (From CashTransaction like CashController)
        $monthlyIn = CashTransaction::whereMonth('date', $currentMonth)
                                    ->whereYear('date', $currentYear)
                                    ->where('type', 'in')
                                    ->sum('amount');
                                    
        $monthlyOut = CashTransaction::whereMonth('date', $currentMonth)
                                     ->whereYear('date', $currentYear)
                                     ->where('type', 'out')
                                     ->sum('amount');
                                     
        // Current Balance: All In - All Out
        $totalInAll = CashTransaction::where('type', 'in')->sum('amount');
        $totalOutAll = CashTransaction::where('type', 'out')->sum('amount');
        $currentBalance = $totalInAll - $totalOutAll;

        // 2. Net Income (From Journal Items / Accounts)
        $revenues = $this->getAccountBalances('revenue', $startDate, $endDate);
        $expenses = $this->getAccountBalances('expense', $startDate, $endDate);
        $netIncome = $revenues->sum('period_balance') - $expenses->sum('period_balance');

        // 3. Total Assets
        $assets = $this->getAccountBalances('asset', null, $endDate);
        $totalAssets = $assets->sum('period_balance');

        // 4. Recent Transactions
        $recentTransactions = CashTransaction::with('user')
                                              ->latest()
                                              ->take(5)
                                              ->get();

        $activePage = 'finance-dashboard';

        return view('pages.finance-dashboard.index', compact(
            'monthlyIn',
            'monthlyOut',
            'currentBalance',
            'netIncome',
            'totalAssets',
            'recentTransactions',
            'activePage'
        ));
    }

    private function getAccountBalances($type, $startDate = null, $endDate = null)
    {
        $accounts = Account::where('type', $type)->with(['journalItems' => function($q) use ($startDate, $endDate) {
            $q->whereHas('journal', function($q) use ($startDate, $endDate) {
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashTransaction;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\FiscalPeriod;
use App\Models\Budget;
use App\Models\ApprovalRequest;
use App\Traits\BranchScope;
use Carbon\Carbon;

class FinanceDashboardController extends Controller
{
    use BranchScope;

    public function index()
    {
        $now = Carbon::now();
        $startDate = $now->copy()->startOfMonth();
        $endDate = $now->copy()->endOfMonth();

        // ── 1. Saldo Kas & Arus Kas ──
        $monthlyInQuery = CashTransaction::whereMonth('date', $now->month)->whereYear('date', $now->year)->where('type', 'in');
        $this->scopeByUserBranch($monthlyInQuery);
        $monthlyIn = $monthlyInQuery->sum('amount');

        $monthlyOutQuery = CashTransaction::whereMonth('date', $now->month)->whereYear('date', $now->year)->where('type', 'out');
        $this->scopeByUserBranch($monthlyOutQuery);
        $monthlyOut = $monthlyOutQuery->sum('amount');

        $totalInQuery = CashTransaction::where('type', 'in');
        $this->scopeByUserBranch($totalInQuery);
        $totalOutQuery = CashTransaction::where('type', 'out');
        $this->scopeByUserBranch($totalOutQuery);
        $currentBalance = $totalInQuery->sum('amount') - $totalOutQuery->sum('amount');

        // ── 2. Net Income ──
        $revenues = $this->getAccountBalances('revenue', $startDate, $endDate);
        $expenses = $this->getAccountBalances('expense', $startDate, $endDate);
        $netIncome = $revenues->sum('period_balance') - $expenses->sum('period_balance');

        // ── 3. Total Assets ──
        $assets = $this->getAccountBalances('asset', null, $endDate);
        $totalAssets = $assets->sum('period_balance');

        // ── 4. Piutang & Hutang ──
        $totalPiutang = $this->getControlAccountBalance('1-103', $endDate);
        $totalHutang = $this->getControlAccountBalance('2-101', $endDate, true);

        // ── 5. AR Aging (Piutang per bulan) ──
        $arAging = $this->getAgingData('1-103', 'debit');

        // ── 6. AP Aging (Hutang per bulan) ──
        $apAging = $this->getAgingData('2-101', 'credit');

        // ── 7. Kas Trend (6 bulan terakhir) ──
        $cashTrend = $this->getCashTrend(6);

        // ── 8. Recent journal entries ──
        $recentJournals = JournalEntry::with(['items.account'])
            ->notVoid()
            ->latest('date')
            ->take(5)
            ->get();

        // ── 9. Budget summary ──
        $currentPeriod = FiscalPeriod::where('status', 'open')->first();
        $budgetSummary = null;
        if ($currentPeriod) {
            $budget = Budget::with('items.account')
                ->where('fiscal_period_id', $currentPeriod->id)
                ->where('status', '!=', 'draft')
                ->first();
            if ($budget) {
                $totalPlanned = $budget->items->sum('planned_amount');
                $totalActual = 0;
                foreach ($budget->items as $item) {
                    $actual = JournalItem::where('account_id', $item->account_id)
                        ->whereHas('journal', fn($q) => $q->where('status', '!=', 'void')
                            ->whereBetween('date', [$currentPeriod->start_date, $currentPeriod->end_date]))
                        ->sum($item->account->isDebitNormal() ? 'debit' : 'credit');
                    $totalActual += $actual;
                }
                $budgetSummary = [
                    'name' => $budget->name,
                    'planned' => $totalPlanned,
                    'actual' => $totalActual,
                    'variance' => $totalPlanned - $totalActual,
                    'percentage' => $totalPlanned > 0 ? round(($totalActual / $totalPlanned) * 100, 1) : 0,
                ];
            }
        }

        // ── 10. Pending approvals ──
        $pendingApprovals = ApprovalRequest::where('status', 'pending')->count();

        // ── 11. Recent 5 transactions ──
        $recentQuery = CashTransaction::with('user')->latest();
        $this->scopeByUserBranch($recentQuery);
        $recentTransactions = $recentQuery->take(5)->get();

        return view('pages.finance-dashboard.index', compact(
            'monthlyIn', 'monthlyOut', 'currentBalance', 'netIncome',
            'totalAssets', 'totalPiutang', 'totalHutang',
            'arAging', 'apAging', 'cashTrend',
            'recentJournals', 'recentTransactions',
            'budgetSummary', 'pendingApprovals'
        ))->with('activePage', 'finance-dashboard');
    }

    private function getAccountBalances($type, $startDate = null, $endDate = null)
    {
        $accounts = Account::where('type', $type)->where('is_header', false)->with(['journalItems' => function($q) use ($startDate, $endDate) {
            $q->whereHas('journal', function($q) use ($startDate, $endDate) {
                $this->scopeByBranch($q);
                $q->where('status', '!=', 'void');
                if ($startDate) $q->where('date', '>=', $startDate);
                if ($endDate) $q->where('date', '<=', $endDate);
            });
        }])->get();

        foreach ($accounts as $acc) {
            $debit = $acc->journalItems->sum('debit');
            $credit = $acc->journalItems->sum('credit');
            $acc->period_balance = in_array($type, ['asset', 'expense'])
                ? $debit - $credit
                : $credit - $debit;
        }

        return $accounts;
    }

    private function getControlAccountBalance($code, $endDate, $isCreditNormal = false)
    {
        $account = Account::where('code', $code)->first();
        if (!$account) return 0;

        $query = JournalItem::where('account_id', $account->id)
            ->whereHas('journal', function($q) use ($endDate) {
                $this->scopeByBranch($q);
                $q->where('status', '!=', 'void');
                if ($endDate) $q->where('date', '<=', $endDate);
            });

        $debit = (clone $query)->sum('debit');
        $credit = (clone $query)->sum('credit');

        return $isCreditNormal ? ($credit - $debit) : ($debit - $credit);
    }

    private function getAgingData($code, $normalSide)
    {
        $account = Account::where('code', $code)->first();
        if (!$account) return [];

        $aging = [];
        for ($i = 0; $i < 4; $i++) {
            $start = Carbon::now()->subMonths($i)->startOfMonth();
            $end = Carbon::now()->subMonths($i)->endOfMonth();
            $label = $i === 0 ? 'Bulan Ini' : ($i === 1 ? '1 Bulan' : "{$i} Bulan");

            $query = JournalItem::where('account_id', $account->id)
                ->whereHas('journal', fn($q) => $q->where('status', '!=', 'void')
                    ->whereBetween('date', [$start, $end]));

            $amount = $normalSide === 'debit'
                ? $query->sum('debit') - $query->sum('credit')
                : $query->sum('credit') - $query->sum('debit');

            $aging[] = ['label' => $label, 'amount' => max(0, $amount)];
        }
        return $aging;
    }

    private function getCashTrend($months)
    {
        $trend = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $label = $date->format('M Y');

            $inQuery = CashTransaction::whereMonth('date', $date->month)->whereYear('date', $date->year)->where('type', 'in');
            $this->scopeByUserBranch($inQuery);
            $in = $inQuery->sum('amount');

            $outQuery = CashTransaction::whereMonth('date', $date->month)->whereYear('date', $date->year)->where('type', 'out');
            $this->scopeByUserBranch($outQuery);
            $out = $outQuery->sum('amount');

            $trend[] = ['label' => $label, 'in' => $in, 'out' => $out, 'net' => $in - $out];
        }
        return $trend;
    }
}

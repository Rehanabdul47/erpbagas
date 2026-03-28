<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use App\Traits\BranchScope;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashController extends Controller
{
    use BranchScope;

    public function index(Request $request)
    {
        $month = $request->input('month', date('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = CashTransaction::with('user');
        $this->scopeByUserBranch($query);

        if ($request->filled('month')) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->latest('date')->latest('id')->get();

        // Calculate stats for the selected month - scoped by branch
        $currentMonthQuery = CashTransaction::whereBetween('date', [$startDate, $endDate]);
        $this->scopeByUserBranch($currentMonthQuery);
        $currentMonthTransactions = $currentMonthQuery->get();
        $monthlyIn = $currentMonthTransactions->where('type', 'in')->sum('amount');
        $monthlyOut = $currentMonthTransactions->where('type', 'out')->sum('amount');

        // Total balance is all time In - Out - scoped by branch
        $totalInQuery = CashTransaction::where('type', 'in');
        $this->scopeByUserBranch($totalInQuery);
        $totalIn = $totalInQuery->sum('amount');

        $totalOutQuery = CashTransaction::where('type', 'out');
        $this->scopeByUserBranch($totalOutQuery);
        $totalOut = $totalOutQuery->sum('amount');
        $totalBalance = $totalIn - $totalOut;

        // Calculate Piutang Usaha (1-103) - Asset (Normal Balance: Debit)
        $piutangAccount = \App\Models\Account::where('code', '1-103')->first();
        $totalPiutang = 0;
        if ($piutangAccount) {
            $piutangQuery = \App\Models\JournalItem::where('account_id', $piutangAccount->id)
                ->whereHas('journal', function($q) {
                    $this->scopeByBranch($q);
                });
            $totalPiutang = $piutangQuery->sum('debit') - $piutangQuery->sum('credit');
        }

        // Calculate Hutang Usaha (2-101) - Liability (Normal Balance: Credit)
        $hutangAccount = \App\Models\Account::where('code', '2-101')->first();
        $totalHutang = 0;
        if ($hutangAccount) {
            $hutangQuery = \App\Models\JournalItem::where('account_id', $hutangAccount->id)
                ->whereHas('journal', function($q) {
                    $this->scopeByBranch($q);
                });
            $totalHutang = $hutangQuery->sum('credit') - $hutangQuery->sum('debit');
        }

        $stats = [
            'totalBalance' => $totalBalance,
            'monthlyIn' => $monthlyIn,
            'monthlyOut' => $monthlyOut,
            'currentMonth' => $startDate->format('F Y'),
            'totalPiutang' => $totalPiutang,
            'totalHutang' => $totalHutang,
        ];
        $rePercentage = \App\Models\Setting::where('key', 'finance_retained_earnings_percentage')->value('value') ?? 40;
        $rePercentage = (float) $rePercentage / 100;
        
        $accounts = \App\Models\Account::orderBy('code')->get();

        return view('pages.finance.index', compact('transactions', 'month', 'stats', 'accounts', 'rePercentage'))
            ->with('activePage', 'finance');
    }

    public function print(Request $request)
    {
        $month = $request->input('month', date('Y-m'));
        $startDate = \Carbon\Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = CashTransaction::with('user');
        $this->scopeByUserBranch($query);

        if ($request->filled('month')) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        // Calculate stats for the selected month - scoped by branch
        $currentMonthQuery = CashTransaction::whereBetween('date', [$startDate, $endDate]);
        $this->scopeByUserBranch($currentMonthQuery);
        $currentMonthTransactions = $currentMonthQuery->get();
        $monthlyIn = $currentMonthTransactions->where('type', 'in')->sum('amount');
        $monthlyOut = $currentMonthTransactions->where('type', 'out')->sum('amount');

        // Total balance is all time In - Out - scoped by branch
        $totalInQuery = CashTransaction::where('type', 'in');
        $this->scopeByUserBranch($totalInQuery);
        $totalIn = $totalInQuery->sum('amount');

        $totalOutQuery = CashTransaction::where('type', 'out');
        $this->scopeByUserBranch($totalOutQuery);
        $totalOut = $totalOutQuery->sum('amount');
        $totalBalance = $totalIn - $totalOut;

        $stats = [
            'currentMonth' => $startDate->translatedFormat('F Y'),
            'monthlyIn' => $monthlyIn,
            'monthlyOut' => $monthlyOut,
            'totalBalance' => $totalBalance
        ];

        return view('pages.finance.print', compact('transactions', 'month', 'stats', 'startDate', 'endDate'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reference' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = auth()->id();
        $transaction = CashTransaction::create($validated);

        $this->syncJournalEntry($transaction, $validated);

        $typeLabel = $validated['type'] === 'in' ? 'Kas Masuk' : 'Kas Keluar';
        return redirect()->route('finance.index')->with('success', "Transaksi {$typeLabel} berhasil dicatat");
    }

    public function update(Request $request, CashTransaction $finance)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reference' => 'nullable|string|max:255',
        ]);

        // Keep the original type
        $validated['type'] = $finance->type;
        $finance->update($validated);

        $this->syncJournalEntry($finance, $validated);

        return redirect()->route('finance.index')->with('success', "Transaksi berhasil diupdate");
    }

    public function destroy(CashTransaction $finance)
    {
        // Delete associated Journal Entry first
        $journalEntry = \App\Models\JournalEntry::where('reference', 'CASH-' . $finance->id)->first();
        if ($journalEntry) {
            $journalEntry->items()->delete();
            $journalEntry->delete();
        }

        $finance->delete();

        return redirect()->route('finance.index')->with('success', "Transaksi berhasil dihapus");
    }

    private function syncJournalEntry(CashTransaction $transaction, array $validated)
    {
        // Try to extract account code from category string like "Beban Gaji (5-102)"
        $otherAccount = null;
        if (preg_match('/\((.*?)\)/', $validated['category'], $matches)) {
            $otherAccount = \App\Models\Account::where('code', $matches[1])->first();
        }

        if (!$otherAccount) {
            $otherAccountCode = $validated['type'] === 'in' ? '4-102' : '5-108'; // Default accounts
            $otherAccount = \App\Models\Account::where('code', $otherAccountCode)->first();
        }

        // Determine if Kas (1-101) or Bank (1-102) depending on Description method prefix
        $kasCode = '1-101'; // Default Kas Tunai
        if (preg_match('/\[(.*?)\]/', $validated['description'], $methodMatches)) {
            $method = strtolower($methodMatches[1]);
            if (in_array($method, ['transfer', 'qris', 'giro'])) {
                $kasCode = '1-102'; // Bank
            }
        }
        $kasAccount = \App\Models\Account::where('code', $kasCode)->first();

        if ($kasAccount && $otherAccount) {
            $journalEntry = \App\Models\JournalEntry::where('reference', 'CASH-' . $transaction->id)->first();

            if (!$journalEntry) {
                $journalEntry = \App\Models\JournalEntry::create([
                    'branch_id' => auth()->user()->branch_id ?? 1,
                    'date' => $validated['date'],
                    'reference' => 'CASH-' . $transaction->id,
                    'description' => "Kas: " . $validated['description'],
                ]);
            } else {
                $journalEntry->update([
                    'date' => $validated['date'],
                    'description' => "Kas: " . $validated['description'],
                ]);
                // Delete old items to recreate them
                $journalEntry->items()->delete();
            }

            if ($validated['type'] === 'in') {
                $journalEntry->items()->createMany([
                    ['account_id' => $kasAccount->id, 'debit' => $validated['amount'], 'credit' => 0],
                    ['account_id' => $otherAccount->id, 'debit' => 0, 'credit' => $validated['amount']],
                ]);
            } else {
                $journalEntry->items()->createMany([
                    ['account_id' => $otherAccount->id, 'debit' => $validated['amount'], 'credit' => 0],
                    ['account_id' => $kasAccount->id, 'debit' => 0, 'credit' => $validated['amount']],
                ]);
            }
        }
    }
}

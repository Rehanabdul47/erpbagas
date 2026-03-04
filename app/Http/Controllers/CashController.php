<?php

namespace App\Http\Controllers;

use App\Models\CashTransaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', date('Y-m'));
        $startDate = Carbon::parse($month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = CashTransaction::with('user');

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

        // Calculate stats for the selected month (or all time if no month filter, though default is current month)
        $currentMonthTransactions = CashTransaction::whereBetween('date', [$startDate, $endDate])->get();
        $monthlyIn = $currentMonthTransactions->where('type', 'in')->sum('amount');
        $monthlyOut = $currentMonthTransactions->where('type', 'out')->sum('amount');

        // Total balance is all time In - Out
        $totalIn = CashTransaction::where('type', 'in')->sum('amount');
        $totalOut = CashTransaction::where('type', 'out')->sum('amount');
        $totalBalance = $totalIn - $totalOut;

        $stats = [
            'totalBalance' => $totalBalance,
            'monthlyIn' => $monthlyIn,
            'monthlyOut' => $monthlyOut,
            'currentMonth' => $startDate->format('F Y'),
        ];
        $rePercentage = \App\Models\Setting::where('key', 'finance_retained_earnings_percentage')->value('value') ?? 40;

        return view('pages.finance.index', compact('transactions', 'stats', 'month', 'rePercentage'))
            ->with('activePage', 'finance');
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

        // Record Journal Entry
        $kasAccount = \App\Models\Account::where('code', '1-101')->first();
        
        // Try to extract account code from category string like "Beban Gaji (5-102)"
        $otherAccount = null;
        if (preg_match('/\((.*?)\)/', $validated['category'], $matches)) {
            $otherAccount = \App\Models\Account::where('code', $matches[1])->first();
        }

        // Fallback to default if not found or couldn't parse
        if (!$otherAccount) {
            $otherAccountCode = $validated['type'] === 'in' ? '4-102' : '5-108';
            $otherAccount = \App\Models\Account::where('code', $otherAccountCode)->first();
        }

        if ($kasAccount && $otherAccount) {
            $journalEntry = \App\Models\JournalEntry::create([
                'date' => $validated['date'],
                'reference' => $validated['reference'] ?? 'CASH-' . $transaction->id,
                'description' => "Transaksi Kas: " . $validated['description'],
            ]);

            if ($validated['type'] === 'in') {
                // In: Debit Kas, Credit Other
                $journalEntry->items()->createMany([
                    ['account_id' => $kasAccount->id, 'debit' => $validated['amount'], 'credit' => 0],
                    ['account_id' => $otherAccount->id, 'debit' => 0, 'credit' => $validated['amount']],
                ]);
            } else {
                // Out: Debit Other, Credit Kas
                $journalEntry->items()->createMany([
                    ['account_id' => $otherAccount->id, 'debit' => $validated['amount'], 'credit' => 0],
                    ['account_id' => $kasAccount->id, 'debit' => 0, 'credit' => $validated['amount']],
                ]);
            }
            
            // Link them
            $transaction->update(['reference' => $journalEntry->id]); // Using reference to store ID for simple tracking
        }

        $typeLabel = $validated['type'] === 'in' ? 'Kas Masuk' : 'Kas Keluar';
        return redirect()->route('finance.index')->with('success', "Transaksi {$typeLabel} berhasil dicatat");
    }

    public function update(Request $request, CashTransaction $finance)
    {
        // Parameter name defaults to the route segment name (finance => $finance)
        $validated = $request->validate([
            'date' => 'required|date',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'reference' => 'nullable|string|max:255',
        ]);

        $finance->update($validated);

        return redirect()->route('finance.index')->with('success', "Transaksi berhasil diupdate");
    }

    public function destroy(CashTransaction $finance)
    {
        $finance->delete();

        return redirect()->route('finance.index')->with('success', "Transaksi berhasil dihapus");
    }
}

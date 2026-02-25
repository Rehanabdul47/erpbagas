<?php

namespace App\Observers;

use App\Models\CashTransaction;
use App\Models\JournalEntry;
use App\Models\Account;

class CashTransactionObserver
{
    /**
     * Handle the CashTransaction "created" event.
     */
    public function created(CashTransaction $transaction): void
    {
        $this->syncJournalEntry($transaction);
    }

    /**
     * Handle the CashTransaction "updated" event.
     */
    public function updated(CashTransaction $transaction): void
    {
        $this->syncJournalEntry($transaction);
    }

    /**
     * Handle the CashTransaction "deleted" event.
     */
    public function deleted(CashTransaction $transaction): void
    {
        $entry = JournalEntry::where('reference', 'cashtrx-' . $transaction->id)->first();
        if ($entry) {
            $entry->delete();
        }
    }

    private function syncJournalEntry(CashTransaction $trx)
    {
        // Remove existing if any (for updates)
        $entry = JournalEntry::where('reference', 'cashtrx-' . $trx->id)->first();
        if ($entry) {
            $entry->delete();
        }

        $cashAccount = Account::where('code', '1001')->first();
        $targetAccount = $this->determineTargetAccount($trx);

        if (!$cashAccount || !$targetAccount) {
            return; // Skip if accounts not seeded properly
        }

        $journal = JournalEntry::create([
            'date' => $trx->date,
            'reference' => 'cashtrx-' . $trx->id,
            'description' => $trx->description . ($trx->reference ? ' (Ref: ' . $trx->reference . ')' : ''),
        ]);

        if ($trx->type === 'in') {
            // Debit Kas, Credit Target
            $journal->items()->create([
                'account_id' => $cashAccount->id,
                'debit' => $trx->amount,
                'credit' => 0,
            ]);
            $journal->items()->create([
                'account_id' => $targetAccount->id,
                'debit' => 0,
                'credit' => $trx->amount,
            ]);
        } else {
            // Debit Target, Credit Kas
            $journal->items()->create([
                'account_id' => $targetAccount->id,
                'debit' => $trx->amount,
                'credit' => 0,
            ]);
            $journal->items()->create([
                'account_id' => $cashAccount->id,
                'debit' => 0,
                'credit' => $trx->amount,
            ]);
        }
    }

    private function determineTargetAccount(CashTransaction $trx)
    {
        $category = strtolower($trx->category);
        
        if ($trx->type === 'in') {
            if (str_contains($category, 'penjualan')) return Account::where('code', '4001')->first();
            if (str_contains($category, 'piutang')) return Account::where('code', '1101')->first();
            if (str_contains($category, 'modal')) return Account::where('code', '3001')->first();
            return Account::where('code', '4999')->first();
        } else {
            if (str_contains($category, 'gaji')) return Account::where('code', '6001')->first();
            if (str_contains($category, 'operasional')) return Account::where('code', '6002')->first();
            if (str_contains($category, 'server')) return Account::where('code', '6002')->first();
            if (str_contains($category, 'iklan') || str_contains($category, 'promosi') || str_contains($category, 'pemasaran')) return Account::where('code', '6003')->first();
            if (str_contains($category, 'sewa')) return Account::where('code', '6004')->first();
            if (str_contains($category, 'aset') || str_contains($category, 'peralatan')) return Account::where('code', '1301')->first();
            if (str_contains($category, 'kasbon')) return Account::where('code', '1101')->first();
            return Account::where('code', '6999')->first();
        }
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\CashTransaction;
use App\Models\Sale;
use App\Models\Order as EcommerceOrder;
use App\Models\PurchaseOrder;
use App\Models\Payroll;
use App\Models\Account;
use App\Models\FiscalPeriod;
use Carbon\Carbon;

class SyncFinanceData extends Command
{
    protected $signature = 'finance:sync';
    protected $description = 'Synchronize all operational transactions to the new General Ledger format';

    public function handle()
    {
        $this->info('Memulai sinkronisasi data keuangan...');

        // 1. Delete all automatically generated journal entries to prevent duplication
        // We delete by source_type (new format) or known reference prefixes (old format)
        $this->info('Menghapus jurnal yang di-generate sistem secara otomatis...');
        JournalEntry::whereNotNull('source_type')
            ->orWhere('reference', 'like', 'CASH-%')
            ->orWhere('reference', 'like', 'POS-%')
            ->orWhere('reference', 'like', 'ECOMM-%')
            ->orWhere('reference', 'like', 'PO-%')
            ->orWhere('reference', 'like', 'PAYROLL-%')
            ->delete();

        // Ensure we have active fiscal period
        $this->ensureFiscalPeriod();

        // 2. Synchronize Cash Transactions
        $this->info('Sinkronisasi Transaksi Kas (Buku Kas)...');
        $cashTrx = CashTransaction::all();
        foreach ($cashTrx as $trx) {
            $this->syncCashTransaction($trx);
        }

        // 3. Synchronize POS Sales
        $this->info('Sinkronisasi Penjualan POS...');
        $sales = Sale::all();
        foreach ($sales as $sale) {
            $this->syncPosSale($sale);
        }

        // 4. Synchronize E-Commerce Orders
        if (class_exists(EcommerceOrder::class)) {
            $this->info('Sinkronisasi E-Commerce Orders...');
            $ecomms = EcommerceOrder::whereIn('status', ['completed', 'shipped'])->get();
            foreach ($ecomms as $order) {
                $this->syncEcommerceOrder($order);
            }
        }

        // 5. Synchronize Purchase Orders
        $this->info('Sinkronisasi Pembelian (PO)...');
        $pos = PurchaseOrder::whereIn('status', ['received', 'completed', 'paid'])->get();
        foreach ($pos as $po) {
            $this->syncPurchaseOrder($po);
        }

        // 6. Synchronize Payroll
        if (class_exists(Payroll::class)) {
            $this->info('Sinkronisasi Payroll...');
            $payrolls = Payroll::where('status', 'paid')->get();
            foreach ($payrolls as $payroll) {
                $this->syncPayroll($payroll);
            }
        }

        $this->info('Sinkronisasi Selesai! Semua transaksi sekarang terhubung ke Ledger dan Periode Fiskal.');
    }

    private function ensureFiscalPeriod()
    {
        $currentMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        
        FiscalPeriod::firstOrCreate(
            ['name' => 'Periode ' . date('F Y')],
            [
                'start_date' => $currentMonth,
                'end_date' => $endOfMonth,
                'status' => 'open'
            ]
        );
    }

    private function getPeriodId($date)
    {
        $d = Carbon::parse($date);
        $period = FiscalPeriod::where('start_date', '<=', $d)
            ->where('end_date', '>=', $d)
            ->first();

        if (!$period) {
            $period = FiscalPeriod::create([
                'name' => 'Periode ' . $d->format('F Y'),
                'start_date' => $d->copy()->startOfMonth(),
                'end_date' => $d->copy()->endOfMonth(),
                'status' => 'open'
            ]);
        }
        return $period->id;
    }

    private function syncCashTransaction($trx)
    {
        $kasAccount = Account::where('code', '1-101')->first();
        if (!$kasAccount) return;

        $otherAccount = null;
        if (preg_match('/\((.*?)\)/', $trx->category, $matches)) {
            $otherAccount = Account::where('code', $matches[1])->first();
        }
        if (!$otherAccount) {
            $otherAccountCode = $trx->type === 'in' ? '4-102' : '5-108';
            $otherAccount = Account::where('code', $otherAccountCode)->first();
        }

        if ($otherAccount) {
            $entry = JournalEntry::create([
                'branch_id' => $trx->user->branch_id ?? 1,
                'fiscal_period_id' => $this->getPeriodId($trx->date),
                'date' => $trx->date,
                'reference' => 'CASH-' . $trx->id,
                'description' => "Transaksi Kas: " . $trx->description,
                'status' => 'posted',
                'source_type' => CashTransaction::class,
                'source_id' => $trx->id,
            ]);

            if ($trx->type === 'in') {
                $entry->items()->createMany([
                    ['account_id' => $kasAccount->id, 'debit' => $trx->amount, 'credit' => 0],
                    ['account_id' => $otherAccount->id, 'debit' => 0, 'credit' => $trx->amount],
                ]);
            } else {
                $entry->items()->createMany([
                    ['account_id' => $otherAccount->id, 'debit' => $trx->amount, 'credit' => 0],
                    ['account_id' => $kasAccount->id, 'debit' => 0, 'credit' => $trx->amount],
                ]);
            }
        }
    }

    private function syncPosSale($sale)
    {
        $kasAccount = Account::where('code', '1-101')->first();
        $pendapatanAccount = Account::where('code', '4-101')->first();
        $hppAccount = Account::where('code', '5-101')->first();
        $persediaanAccount = Account::where('code', '1-104')->first();

        if ($kasAccount && $pendapatanAccount && $hppAccount && $persediaanAccount) {
            $totalAmount = $sale->total_amount;
            $totalCogs = 0;
            // Best effort to calc cogs if not stored on sale
            foreach ($sale->items ?? [] as $item) {
                if ($item->product) {
                    $totalCogs += ($item->product->cogs * $item->quantity);
                }
            }
            if ($totalCogs == 0) $totalCogs = $totalAmount * 0.6; // fallback 60% margin

            $entry = JournalEntry::create([
                'branch_id' => $sale->branch_id ?? 1,
                'fiscal_period_id' => $this->getPeriodId($sale->sale_date),
                'date' => $sale->sale_date,
                'reference' => 'POS-' . $sale->invoice_no,
                'description' => "Penjualan POS #{$sale->invoice_no}",
                'status' => 'posted',
                'source_type' => Sale::class,
                'source_id' => $sale->id,
            ]);

            $entry->items()->createMany([
                ['account_id' => $kasAccount->id, 'debit' => $totalAmount, 'credit' => 0],
                ['account_id' => $pendapatanAccount->id, 'debit' => 0, 'credit' => $totalAmount],
                ['account_id' => $hppAccount->id, 'debit' => $totalCogs, 'credit' => 0],
                ['account_id' => $persediaanAccount->id, 'debit' => 0, 'credit' => $totalCogs],
            ]);
        }
    }

    private function syncEcommerceOrder($order)
    {
         $kasAccount = Account::where('code', '1-101')->first();
         $pendapatanAccount = Account::where('code', '4-101')->first();
         
         if ($kasAccount && $pendapatanAccount) {
             $entry = JournalEntry::create([
                 'branch_id' => $order->branch_id ?? 1,
                 'fiscal_period_id' => $this->getPeriodId($order->created_at),
                 'date' => $order->created_at,
                 'reference' => 'ECOMM-' . $order->order_id,
                 'description' => "Penjualan E-Commerce #{$order->order_id}",
                 'status' => 'posted',
                 'source_type' => EcommerceOrder::class,
                 'source_id' => $order->id,
             ]);

             $amt = $order->total_price ?? 0;
             if ($amt > 0) {
                 $entry->items()->createMany([
                     ['account_id' => $kasAccount->id, 'debit' => $amt, 'credit' => 0],
                     ['account_id' => $pendapatanAccount->id, 'debit' => 0, 'credit' => $amt],
                 ]);
             }
         }
    }

    private function syncPurchaseOrder($po)
    {
        $persediaanAccount = Account::where('code', '1-104')->first();
        $hutangAccount = Account::where('code', '2-101')->first();
        $kasAccount = Account::where('code', '1-101')->first();

        if ($persediaanAccount) {
            $amt = $po->total_amount ?? 0;
            if ($amt == 0) return;

            $entry = JournalEntry::create([
                'branch_id' => $po->branch_id ?? 1,
                'fiscal_period_id' => $this->getPeriodId($po->created_at),
                'date' => $po->created_at,
                'reference' => 'PO-' . $po->po_number,
                'description' => "Pembelian PO #{$po->po_number}",
                'status' => 'posted',
                'source_type' => PurchaseOrder::class,
                'source_id' => $po->id,
            ]);

            // Jika status paid, pakai Kas, jika completed/received pakai Hutang
            $creditAccId = ($po->status === 'paid' && $kasAccount) ? $kasAccount->id : ($hutangAccount->id ?? $kasAccount->id ?? null);
            
            if ($creditAccId) {
                $entry->items()->createMany([
                    ['account_id' => $persediaanAccount->id, 'debit' => $amt, 'credit' => 0],
                    ['account_id' => $creditAccId, 'debit' => 0, 'credit' => $amt],
                ]);
            }
        }
    }

    private function syncPayroll($payroll)
    {
        $gajiAccount = Account::where('code', '5-102')->first();
        $kasAccount = Account::where('code', '1-101')->first();

        if ($gajiAccount && $kasAccount) {
            $amt = $payroll->net_salary ?? 0;
            if ($amt == 0) return;

            $entry = JournalEntry::create([
                'branch_id' => 1,
                'fiscal_period_id' => $this->getPeriodId($payroll->payment_date ?? $payroll->created_at),
                'date' => $payroll->payment_date ?? $payroll->created_at,
                'reference' => 'PAYROLL-' . $payroll->id,
                'description' => "Penggajian periode " . $payroll->period_month . '/' . $payroll->period_year,
                'status' => 'posted',
                'source_type' => Payroll::class,
                'source_id' => $payroll->id,
            ]);

            $entry->items()->createMany([
                ['account_id' => $gajiAccount->id, 'debit' => $amt, 'credit' => 0],
                ['account_id' => $kasAccount->id, 'debit' => 0, 'credit' => $amt],
            ]);
        }
    }
}

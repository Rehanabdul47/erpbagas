<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\FiscalPeriod;
use App\Models\AuditTrail;
use App\Traits\BranchScope;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GeneralLedgerController extends Controller
{
    use BranchScope;

    public function index(Request $request)
    {
        $query = JournalEntry::with(['items.account', 'branch', 'fiscalPeriod']);
        $this->scopeByBranch($query);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('month')) {
            $start = Carbon::parse($request->month . '-01')->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $query->whereBetween('date', [$start, $end]);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('account_id')) {
            $accountId = $request->account_id;
            $query->whereHas('items', fn($q) => $q->where('account_id', $accountId));
        }

        $entries = $query->latest('date')->latest('id')->paginate(25);
        $accounts = Account::postable()->orderBy('code')->get();
        $month = $request->input('month', date('Y-m'));

        return view('pages.finance.ledger.index', compact('entries', 'accounts', 'month'))
            ->with('activePage', 'ledger');
    }

    public function create()
    {
        $accounts = Account::postable()->active()->orderBy('code')->get();
        $periods = FiscalPeriod::where('status', 'open')->orderBy('start_date', 'desc')->get();

        return view('pages.finance.ledger.create', compact('accounts', 'periods'))
            ->with('activePage', 'ledger');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'description' => 'required|string',
            'reference' => 'nullable|string|max:255',
            'fiscal_period_id' => 'nullable|exists:fiscal_periods,id',
            'cost_center' => 'nullable|string|max:50',
            'project_code' => 'nullable|string|max:50',
            'status' => 'required|in:draft,posted',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:accounts,id',
            'items.*.debit' => 'required|numeric|min:0',
            'items.*.credit' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
        ]);

        // Validate balanced
        $totalDebit = collect($request->items)->sum('debit');
        $totalCredit = collect($request->items)->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->with('error', 'Total Debit dan Kredit harus seimbang! Selisih: Rp ' . number_format(abs($totalDebit - $totalCredit), 2, ',', '.'));
        }

        // Check period is open
        if ($request->fiscal_period_id) {
            $period = FiscalPeriod::find($request->fiscal_period_id);
            if ($period && !$period->isOpen()) {
                return back()->withInput()->with('error', 'Periode fiskal sudah ditutup. Tidak dapat memposting jurnal.');
            }
        }

        // Auto-detect fiscal period if not specified
        $fiscalPeriodId = $request->fiscal_period_id;
        if (!$fiscalPeriodId) {
            $period = FiscalPeriod::findForDate($request->date);
            $fiscalPeriodId = $period?->id;
        }

        $entry = JournalEntry::create([
            'branch_id' => auth()->user()->branch_id,
            'fiscal_period_id' => $fiscalPeriodId,
            'date' => $request->date,
            'reference' => $request->reference ?? 'JV-' . date('YmdHis'),
            'description' => $request->description,
            'status' => $request->status,
            'cost_center' => $request->cost_center,
            'project_code' => $request->project_code,
        ]);

        foreach ($request->items as $item) {
            if ($item['debit'] > 0 || $item['credit'] > 0) {
                $entry->items()->create([
                    'account_id' => $item['account_id'],
                    'debit' => $item['debit'] ?? 0,
                    'credit' => $item['credit'] ?? 0,
                ]);
            }
        }

        AuditTrail::record($entry, 'created', null, $entry->load('items')->toArray(), "Jurnal #{$entry->reference} dibuat");

        $statusLabel = $request->status === 'posted' ? 'diposting' : 'disimpan sebagai draft';
        return redirect()->route('finance.ledger.index')->with('success', "Jurnal berhasil {$statusLabel}.");
    }

    public function show(JournalEntry $entry)
    {
        $entry->load(['items.account', 'branch', 'fiscalPeriod', 'approver', 'auditTrails.user']);

        return view('pages.finance.ledger.show', compact('entry'))
            ->with('activePage', 'ledger');
    }

    public function post(JournalEntry $entry)
    {
        if (!$entry->isDraft()) {
            return back()->with('error', 'Hanya jurnal draft yang bisa diposting.');
        }

        // Check period
        if ($entry->fiscalPeriod && !$entry->fiscalPeriod->isOpen()) {
            return back()->with('error', 'Periode fiskal sudah ditutup.');
        }

        $entry->update(['status' => 'posted']);
        AuditTrail::record($entry, 'posted', ['status' => 'draft'], ['status' => 'posted'], "Jurnal #{$entry->reference} diposting");

        return back()->with('success', "Jurnal #{$entry->reference} berhasil diposting.");
    }

    public function void(JournalEntry $entry)
    {
        if ($entry->isVoid()) {
            return back()->with('error', 'Jurnal sudah divoid.');
        }

        $oldStatus = $entry->status;
        $entry->update(['status' => 'void']);
        AuditTrail::record($entry, 'voided', ['status' => $oldStatus], ['status' => 'void'], "Jurnal #{$entry->reference} divoid");

        return back()->with('success', "Jurnal #{$entry->reference} berhasil divoid.");
    }
}

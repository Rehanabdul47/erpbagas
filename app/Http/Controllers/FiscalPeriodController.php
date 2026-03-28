<?php

namespace App\Http\Controllers;

use App\Models\FiscalPeriod;
use App\Models\AuditTrail;
use Illuminate\Http\Request;

class FiscalPeriodController extends Controller
{
    public function index()
    {
        $periods = FiscalPeriod::with('closedByUser')
            ->orderBy('start_date', 'desc')
            ->get();

        return view('pages.finance.periods.index', compact('periods'))
            ->with('activePage', 'periods');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        // Check overlap
        $overlap = FiscalPeriod::where(function ($q) use ($validated) {
            $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
              ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
              ->orWhere(function ($q2) use ($validated) {
                  $q2->where('start_date', '<=', $validated['start_date'])
                     ->where('end_date', '>=', $validated['end_date']);
              });
        })->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'Periode tumpang tindih dengan periode yang sudah ada.');
        }

        $period = FiscalPeriod::create($validated);
        AuditTrail::record($period, 'created', null, $period->toArray(), "Periode fiskal baru: {$period->name}");

        return redirect()->route('finance.periods.index')->with('success', "Periode {$period->name} berhasil dibuat.");
    }

    public function close(FiscalPeriod $period)
    {
        if (!$period->isOpen()) {
            return back()->with('error', 'Periode sudah ditutup atau terkunci.');
        }

        $period->update([
            'status' => 'closed',
            'closed_by' => auth()->id(),
            'closed_at' => now(),
        ]);

        AuditTrail::record($period, 'updated', ['status' => 'open'], ['status' => 'closed'], "Periode {$period->name} ditutup");

        return back()->with('success', "Periode {$period->name} berhasil ditutup.");
    }

    public function lock(FiscalPeriod $period)
    {
        if ($period->isLocked()) {
            return back()->with('error', 'Periode sudah terkunci.');
        }

        $old = $period->status;
        $period->update(['status' => 'locked']);
        AuditTrail::record($period, 'updated', ['status' => $old], ['status' => 'locked'], "Periode {$period->name} dikunci");

        return back()->with('success', "Periode {$period->name} berhasil dikunci.");
    }

    public function reopen(FiscalPeriod $period)
    {
        if ($period->isOpen()) {
            return back()->with('error', 'Periode sudah terbuka.');
        }

        $old = $period->status;
        $period->update([
            'status' => 'open',
            'closed_by' => null,
            'closed_at' => null,
        ]);

        AuditTrail::record($period, 'updated', ['status' => $old], ['status' => 'open'], "Periode {$period->name} dibuka kembali");

        return back()->with('success', "Periode {$period->name} berhasil dibuka kembali.");
    }
    public function update(Request $request, FiscalPeriod $period)
    {
        if (!auth()->user()->isExecutive()) {
            return back()->with('error', 'Hanya executive yang dapat mengubah periode fiskal.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
        ]);

        // Check overlap excluding current period
        $overlap = FiscalPeriod::where('id', '!=', $period->id)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhere(function ($q2) use ($validated) {
                      $q2->where('start_date', '<=', $validated['start_date'])
                         ->where('end_date', '>=', $validated['end_date']);
                  });
            })->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'Periode tumpang tindih dengan periode yang sudah ada.');
        }

        $old = $period->toArray();
        $period->update($validated);

        AuditTrail::record($period, 'updated', $old, $period->toArray(), "Periode {$period->name} diperbarui");

        return back()->with('success', "Periode {$period->name} berhasil diperbarui.");
    }

    public function destroy(FiscalPeriod $period)
    {
        if (!auth()->user()->isExecutive()) {
            return back()->with('error', 'Hanya executive yang dapat menghapus periode fiskal.');
        }

        if ($period->journalEntries()->exists() || $period->budgets()->exists()) {
            return back()->with('error', 'Periode tidak dapat dihapus karena sudah memiliki transaksi atau anggaran terkait.');
        }

        $name = $period->name;
        $period->delete();

        AuditTrail::record($period, 'deleted', $period->toArray(), null, "Periode {$name} dihapus");

        return back()->with('success', "Periode {$name} berhasil dihapus.");
    }
}

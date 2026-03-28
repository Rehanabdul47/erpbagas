@extends('layouts.app')

@section('title', 'Budget vs Actual')
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<a href="{{ route('finance.budgets.index') }}" class="text-gray-400 hover:text-white">Anggaran</a>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Budget vs Actual</span>
@endsection

@section('content')
<div>
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0">
            <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white m-0">Budget vs Actual Report</h3>
            <p class="text-xs text-gray-400 mt-0.5">Perbandingan anggaran dengan realisasi per akun</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm mb-6">
        <form class="flex flex-wrap items-end gap-4">
            <div class="space-y-1.5 flex-1 min-w-[250px]">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Pilih Anggaran</label>
                <select name="budget_id" onchange="this.form.submit()" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">— Pilih Anggaran —</option>
                    @foreach($budgets as $b)
                    <option value="{{ $b->id }}" {{ $budgetId == $b->id ? 'selected' : '' }}>
                        {{ $b->name }} ({{ $b->fiscalPeriod->name ?? '-' }})
                    </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    @if($budget && count($reportData) > 0)
    {{-- Summary Cards --}}
    @php
        $totalPlanned = collect($reportData)->sum('planned');
        $totalActual = collect($reportData)->sum('actual');
        $totalVariance = $totalPlanned - $totalActual;
        $overBudgetCount = collect($reportData)->where('status', 'over')->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-gray-800 border-l-4 border-blue-500 border-y border-r border-gray-700/50 rounded-xl p-4">
            <div class="text-xs text-gray-400 mb-1">Total Anggaran</div>
            <div class="text-xl font-bold text-blue-400">Rp {{ number_format($totalPlanned, 0, ',', '.') }}</div>
        </div>
        <div class="bg-gray-800 border-l-4 border-emerald-500 border-y border-r border-gray-700/50 rounded-xl p-4">
            <div class="text-xs text-gray-400 mb-1">Total Realisasi</div>
            <div class="text-xl font-bold text-emerald-400">Rp {{ number_format($totalActual, 0, ',', '.') }}</div>
        </div>
        <div class="bg-gray-800 border-l-4 {{ $totalVariance >= 0 ? 'border-emerald-500' : 'border-red-500' }} border-y border-r border-gray-700/50 rounded-xl p-4">
            <div class="text-xs text-gray-400 mb-1">Variance</div>
            <div class="text-xl font-bold {{ $totalVariance >= 0 ? 'text-emerald-400' : 'text-red-400' }}">Rp {{ number_format(abs($totalVariance), 0, ',', '.') }}</div>
        </div>
        <div class="bg-gray-800 border-l-4 {{ $overBudgetCount > 0 ? 'border-red-500' : 'border-emerald-500' }} border-y border-r border-gray-700/50 rounded-xl p-4">
            <div class="text-xs text-gray-400 mb-1">Over Budget</div>
            <div class="text-xl font-bold {{ $overBudgetCount > 0 ? 'text-red-400' : 'text-emerald-400' }}">{{ $overBudgetCount }} akun</div>
        </div>
    </div>

    {{-- Detail Table --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">Akun</th>
                        <th class="px-5 py-4 font-medium text-right">Anggaran</th>
                        <th class="px-5 py-4 font-medium text-right">Realisasi</th>
                        <th class="px-5 py-4 font-medium text-right">Variance</th>
                        <th class="px-5 py-4 font-medium text-right">%</th>
                        <th class="px-5 py-4 font-medium text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @foreach($reportData as $row)
                    <tr class="hover:bg-gray-700/20 transition-colors">
                        <td class="px-5 py-4">
                            <span class="font-mono text-xs text-indigo-400 mr-2">{{ $row['account']->code }}</span>
                            <span class="text-white">{{ $row['account']->name }}</span>
                        </td>
                        <td class="px-5 py-4 text-right font-medium text-blue-400">Rp {{ number_format($row['planned'], 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right font-medium text-white">Rp {{ number_format($row['actual'], 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right font-bold {{ $row['variance'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $row['variance'] >= 0 ? '' : '-' }}Rp {{ number_format(abs($row['variance']), 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-right text-sm {{ $row['variance'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $row['variance_percent'] }}%
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($row['status'] === 'under')
                            <span class="text-[10px] px-2 py-1 rounded-full font-bold bg-emerald-500/10 text-emerald-400">UNDER</span>
                            @else
                            <span class="text-[10px] px-2 py-1 rounded-full font-bold bg-red-500/10 text-red-400">OVER</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @elseif($budgetId)
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-12 text-center text-gray-500">
        <i data-lucide="file-x" class="w-12 h-12 mx-auto mb-3 text-gray-600"></i>
        <p>Anggaran tidak ditemukan atau belum memiliki item.</p>
    </div>
    @else
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-12 text-center text-gray-500">
        <i data-lucide="bar-chart-3" class="w-12 h-12 mx-auto mb-3 text-gray-600"></i>
        <p>Pilih anggaran di atas untuk melihat laporan Budget vs Actual.</p>
    </div>
    @endif
</div>
@endsection

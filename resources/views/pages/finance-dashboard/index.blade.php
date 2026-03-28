@extends('layouts.app')

@section('title', 'Overview Keuangan')
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Overview Keuangan</span>
@endsection

@section('content')
<div>
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
            <i data-lucide="line-chart" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white m-0">Overview Keuangan</h3>
            <p class="text-xs text-gray-400 mt-0.5">Ringkasan posisi keuangan, arus kas, piutang/hutang, dan anggaran</p>
        </div>
    </div>

    {{-- Row 1: Main KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-6">
        <div class="bg-gray-800 border-l-4 border-indigo-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center"><i data-lucide="wallet" class="w-5 h-5"></i></div>
                <a href="{{ route('finance.ledger.index') }}" class="text-[10px] text-indigo-400 hover:text-indigo-300">Jurnal Umum →</a>
            </div>
            <div class="text-2xl font-bold text-white mb-1">Rp {{ number_format($currentBalance, 0, ',', '.') }}</div>
            <div class="text-gray-400 text-xs">Saldo Kas Saat Ini</div>
        </div>

        <div class="bg-gray-800 border-l-4 {{ $netIncome >= 0 ? 'border-emerald-500' : 'border-red-500' }} border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg {{ $netIncome >= 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }} flex items-center justify-center"><i data-lucide="{{ $netIncome >= 0 ? 'trending-up' : 'trending-down' }}" class="w-5 h-5"></i></div>
                <a href="{{ route('finance.reports') }}" class="text-[10px] text-indigo-400 hover:text-indigo-300">Laporan →</a>
            </div>
            <div class="text-2xl font-bold {{ $netIncome >= 0 ? 'text-emerald-400' : 'text-red-400' }} mb-1">Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</div>
            <div class="text-gray-400 text-xs">{{ $netIncome >= 0 ? 'Laba' : 'Rugi' }} Bersih Bulan Ini</div>
        </div>

        <div class="bg-gray-800 border-l-4 border-amber-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center"><i data-lucide="banknote" class="w-5 h-5"></i></div>
            </div>
            <div class="text-2xl font-bold text-amber-400 mb-1">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</div>
            <div class="text-gray-400 text-xs">Total Piutang Usaha (AR)</div>
        </div>

        <div class="bg-gray-800 border-l-4 border-purple-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center"><i data-lucide="credit-card" class="w-5 h-5"></i></div>
            </div>
            <div class="text-2xl font-bold text-purple-400 mb-1">Rp {{ number_format($totalHutang, 0, ',', '.') }}</div>
            <div class="text-gray-400 text-xs">Total Hutang Usaha (AP)</div>
        </div>
    </div>

    {{-- Row 2: Arus Kas & Total Asset --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0"><i data-lucide="arrow-down-left" class="w-6 h-6"></i></div>
            <div>
                <div class="text-xs text-gray-400 mb-1">Kas Masuk Bulan Ini</div>
                <div class="text-xl font-bold text-emerald-400">Rp {{ number_format($monthlyIn, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-red-500/10 text-red-400 flex items-center justify-center shrink-0"><i data-lucide="arrow-up-right" class="w-6 h-6"></i></div>
            <div>
                <div class="text-xs text-gray-400 mb-1">Kas Keluar Bulan Ini</div>
                <div class="text-xl font-bold text-red-400">Rp {{ number_format($monthlyOut, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0"><i data-lucide="building" class="w-6 h-6"></i></div>
            <div>
                <div class="text-xs text-gray-400 mb-1">Total Nilai Aset</div>
                <div class="text-xl font-bold text-blue-400">Rp {{ number_format($totalAssets, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
        {{-- Cash Flow Trend Chart --}}
        <div class="xl:col-span-2 bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm">
            <div class="p-5 border-b border-gray-700/50">
                <h4 class="font-bold text-white flex items-center gap-2"><i data-lucide="bar-chart-2" class="w-4 h-4 text-indigo-400"></i> Tren Arus Kas (6 Bulan)</h4>
            </div>
            <div class="p-5">
                <div class="space-y-3">
                    @foreach($cashTrend as $item)
                    @php
                        $maxVal = max(collect($cashTrend)->max('in'), collect($cashTrend)->max('out'), 1);
                        $inW = $maxVal > 0 ? ($item['in'] / $maxVal) * 100 : 0;
                        $outW = $maxVal > 0 ? ($item['out'] / $maxVal) * 100 : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-400 font-medium w-20">{{ $item['label'] }}</span>
                            <span class="text-emerald-400 text-[10px]">+{{ number_format($item['in']/1000000, 1) }}jt</span>
                            <span class="text-red-400 text-[10px]">-{{ number_format($item['out']/1000000, 1) }}jt</span>
                        </div>
                        <div class="flex gap-1 h-4">
                            <div class="bg-emerald-500/30 rounded-l-full transition-all" style="width: {{ $inW }}%">
                                <div class="h-full bg-emerald-500 rounded-l-full" style="width: 100%"></div>
                            </div>
                            <div class="bg-red-500/30 rounded-r-full transition-all" style="width: {{ $outW }}%">
                                <div class="h-full bg-red-500 rounded-r-full" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- AR/AP Aging & Budget Summary --}}
        <div class="space-y-6">
            {{-- AR Aging --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm">
                <div class="p-4 border-b border-gray-700/50">
                    <h4 class="font-bold text-white text-sm flex items-center gap-2"><i data-lucide="clock" class="w-4 h-4 text-amber-400"></i> Aging Piutang</h4>
                </div>
                <div class="p-4 space-y-2">
                    @forelse($arAging as $age)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400 text-xs">{{ $age['label'] }}</span>
                        <span class="text-amber-400 font-bold text-xs">Rp {{ number_format($age['amount'], 0, ',', '.') }}</span>
                    </div>
                    @empty
                    <p class="text-gray-500 text-xs">Tidak ada data</p>
                    @endforelse
                </div>
            </div>

            {{-- Budget Summary --}}
            @if($budgetSummary)
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm">
                <div class="p-4 border-b border-gray-700/50">
                    <h4 class="font-bold text-white text-sm flex items-center gap-2"><i data-lucide="piggy-bank" class="w-4 h-4 text-emerald-400"></i> Anggaran Aktif</h4>
                </div>
                <div class="p-4">
                    <div class="text-xs text-gray-400 mb-2">{{ $budgetSummary['name'] }}</div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-400">Realisasi: {{ $budgetSummary['percentage'] }}%</span>
                        <span class="{{ $budgetSummary['variance'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            Sisa: Rp {{ number_format(abs($budgetSummary['variance']), 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3 overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $budgetSummary['percentage'] > 100 ? 'bg-red-500' : ($budgetSummary['percentage'] > 80 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                             style="width: {{ min($budgetSummary['percentage'], 100) }}%"></div>
                    </div>
                </div>
            </div>
            @endif

            @if($pendingApprovals > 0)
            <div class="bg-amber-500/5 border border-amber-500/20 rounded-xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0"><i data-lucide="bell" class="w-5 h-5"></i></div>
                <div>
                    <div class="text-sm font-bold text-amber-400">{{ $pendingApprovals }} Pending Approval</div>
                    <div class="text-[10px] text-gray-400">Transaksi menunggu persetujuan</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Row 4: Recent Transactions & Journals --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Recent Cash Transactions --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2"><i data-lucide="list" class="w-4 h-4 text-indigo-400"></i> Transaksi Kas Terbaru</h4>
                <a href="{{ route('finance.ledger.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Jurnal Umum →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-gray-700/20">
                            <td class="px-5 py-3 text-gray-300 text-xs">{{ $trx->date->format('d M') }}</td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded {{ $trx->type === 'in' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }} flex items-center justify-center">
                                        <i data-lucide="{{ $trx->type === 'in' ? 'arrow-down-left' : 'arrow-up-right' }}" class="w-3 h-3"></i>
                                    </div>
                                    <span class="text-white text-xs font-medium truncate max-w-[150px]">{{ $trx->category }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-xs {{ $trx->type === 'in' ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $trx->type === 'in' ? '+' : '-' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500 text-sm">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Journal Entries --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2"><i data-lucide="book-open" class="w-4 h-4 text-blue-400"></i> Jurnal Terbaru</h4>
                <a href="{{ route('finance.ledger.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">General Ledger →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($recentJournals as $je)
                        <tr class="hover:bg-gray-700/20">
                            <td class="px-5 py-3 text-gray-300 text-xs">{{ $je->date->format('d M') }}</td>
                            <td class="px-5 py-3">
                                <span class="font-mono text-[10px] px-1.5 py-0.5 bg-gray-900 text-indigo-400 rounded mr-1">{{ $je->reference }}</span>
                                <span class="text-white text-xs truncate max-w-[150px]">{{ Str::limit($je->description, 30) }}</span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @php $sc2 = ['draft'=>'text-gray-400','posted'=>'text-blue-400','approved'=>'text-emerald-400','void'=>'text-red-400']; @endphp
                                <span class="text-[10px] font-bold {{ $sc2[$je->status] ?? '' }}">{{ strtoupper($je->status) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500 text-sm">Belum ada jurnal</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

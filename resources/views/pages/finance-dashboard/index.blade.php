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
            <p class="text-xs text-gray-400 mt-0.5">Pantau saldo kas, arus kas masuk/keluar, dan posisi profitabilitas secara langsung.</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="bg-gray-800 border-l-4 border-indigo-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="wallet" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">Rp {{ number_format($currentBalance, 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Saldo Kas Saat Ini</div>
        </div>
        <div class="bg-gray-800 border-l-4 border-emerald-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="arrow-down-left" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-emerald-400 mb-1">Rp {{ number_format($monthlyIn, 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Kas Masuk Bulan Ini</div>
        </div>
        <div class="bg-gray-800 border-l-4 border-red-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center mb-4"><i data-lucide="arrow-up-right" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-red-500 mb-1">Rp {{ number_format($monthlyOut, 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Kas Keluar Bulan Ini</div>
        </div>
        <div class="bg-gray-800 border-l-4 {{ $netIncome >= 0 ? 'border-emerald-500' : 'border-red-500' }} border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg {{ $netIncome >= 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }} flex items-center justify-center mb-4"><i data-lucide="{{ $netIncome >= 0 ? 'trending-up' : 'trending-down' }}" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold {{ $netIncome >= 0 ? 'text-emerald-400' : 'text-red-400' }} mb-1">Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">{{ $netIncome >= 0 ? 'Laba' : 'Rugi' }} Bersih (YTD)</div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 pb-6">
        {{-- Transaksi Terbaru --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full xl:col-span-2">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4 text-indigo-400"></i> Riwayat Transaksi Kas Terbaru
                </h4>
                <a href="{{ route('finance.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Buka Buku Kas →</a>
            </div>
            <div class="p-0 flex-1 relative min-h-[300px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">Tanggal</th>
                                <th class="px-5 py-3 font-medium">Kategori</th>
                                <th class="px-5 py-3 font-medium">Keterangan</th>
                                <th class="px-5 py-3 font-medium text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($recentTransactions as $trx)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-4 text-gray-300">{{ $trx->date->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($trx->type === 'in')
                                        <div class="w-6 h-6 rounded bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                                            <i data-lucide="arrow-down-left" class="w-3 h-3"></i>
                                        </div>
                                        @else
                                        <div class="w-6 h-6 rounded bg-red-500/10 text-red-500 flex items-center justify-center shrink-0">
                                            <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                                        </div>
                                        @endif
                                        <span class="font-bold text-white text-xs">{{ $trx->category }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-gray-300 truncate max-w-[200px]" title="{{ $trx->description }}">{{ $trx->description }}</div>
                                </td>
                                <td class="px-5 py-4 text-right font-bold {{ $trx->type === 'in' ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $trx->type === 'in' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                    <i data-lucide="file-x" class="w-8 h-8 mx-auto mb-2 text-gray-600"></i>
                                    Belum ada transaksi kas.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Highlight Posisi Keuangan --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="scale" class="w-4 h-4 text-blue-400"></i> Ringkasan Neraca
                </h4>
                <a href="{{ route('finance.reports') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Laporan Lengkap →</a>
            </div>
            <div class="p-5 flex-1 flex flex-col justify-center gap-6">
                <!-- Total Assets -->
                <div class="bg-gray-900/50 border border-gray-700/50 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-500/10 flex items-center justify-center text-blue-400 shrink-0">
                        <i data-lucide="building" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Total Nilai Aset</div>
                        <div class="text-xl font-black text-white">Rp {{ number_format($totalAssets, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Laba Berjalan -->
                <div class="bg-gray-900/50 border border-gray-700/50 rounded-xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full {{ $netIncome >= 0 ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }} flex items-center justify-center shrink-0">
                        <i data-lucide="pie-chart" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">{{ $netIncome >= 0 ? 'Laba' : 'Rugi' }} Netto (Aset Lancar)</div>
                        <div class="text-xl font-black {{ $netIncome >= 0 ? 'text-emerald-400' : 'text-red-400' }}">Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

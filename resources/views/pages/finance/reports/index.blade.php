@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('breadcrumb', 'Laporan Keuangan')

@section('content')
<div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm mb-6 print:hidden">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i data-lucide="file-bar-chart" class="w-6 h-6 text-indigo-400"></i>
            Laporan Periode: <span class="text-indigo-400">{{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}</span>
        </h2>
        <form id="filter-form" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <a href="{{ route('finance.index', ['month' => $month]) }}" class="flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Buku Kas
            </a>
            <input type="month" name="month" value="{{ $month }}" onchange="document.getElementById('filter-form').submit()"
                   class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
            <button type="button" onclick="window.print()" class="flex items-center gap-2 px-4 py-2 bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500 hover:text-white border border-indigo-500/20 rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak Dokumen
            </button>
        </form>
    </div>
</div>

    <!-- Consolidated Report Tables -->
    <div class="space-y-8">
        
        <!-- 1. LABA RUGI -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-700/50 bg-gray-900/50 flex justify-between items-center">
                <h3 class="font-bold text-white flex items-center gap-2 m-0 uppercase tracking-widest text-sm">
                    <i data-lucide="trending-up" class="w-4 h-4 text-indigo-400"></i> Laporan Laba Rugi
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <tbody class="divide-y divide-gray-700/50">
                        <tr class="bg-gray-900/30"><td colspan="2" class="px-5 py-3 font-bold text-gray-300 uppercase tracking-wider text-xs">Pendapatan</td></tr>
                        @foreach($revenues as $rev)
                            @if($rev->period_balance != 0)
                            <tr class="hover:bg-indigo-500/5 transition-colors">
                                <td class="px-5 py-3 pl-8 flex justify-between text-gray-200"><span>{{ $rev->name }}</span> <span class="text-gray-500 font-mono text-xs">{{ $rev->code }}</span></td>
                                <td class="px-5 py-3 text-right text-gray-200">Rp {{ number_format($rev->period_balance, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        @if($revenues->sum('period_balance') == 0)
                            <tr><td class="px-5 py-4 pl-8 text-gray-500 italic">Tidak ada transaksi pendapatan</td><td class="px-5 py-4 text-right text-gray-500">Rp 0</td></tr>
                        @endif
                        <tr class="bg-gray-900/50">
                            <td class="px-5 py-4 font-bold text-gray-300 text-right">Total Pendapatan</td>
                            <td class="px-5 py-4 text-right font-bold text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="bg-gray-900/30"><td colspan="2" class="px-5 py-3 font-bold text-gray-300 uppercase tracking-wider text-xs">Beban (Biaya)</td></tr>
                        @foreach($expenses as $exp)
                            @if($exp->period_balance != 0)
                            <tr class="hover:bg-indigo-500/5 transition-colors">
                                <td class="px-5 py-3 pl-8 flex justify-between text-gray-200"><span>{{ $exp->name }}</span> <span class="text-gray-500 font-mono text-xs">{{ $exp->code }}</span></td>
                                <td class="px-5 py-3 text-right text-gray-200">Rp {{ number_format($exp->period_balance, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        @if($expenses->sum('period_balance') == 0)
                            <tr><td class="px-5 py-4 pl-8 text-gray-500 italic">Tidak ada transaksi beban</td><td class="px-5 py-4 text-right text-gray-500">Rp 0</td></tr>
                        @endif
                        <tr class="bg-gray-900/50">
                            <td class="px-5 py-4 font-bold text-gray-300 text-right">Total Beban</td>
                            <td class="px-5 py-4 text-right font-bold text-white">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="bg-indigo-500/10 border-t-2 border-indigo-500/30">
                            <td class="px-5 py-5 font-black uppercase text-sm tracking-wider {{ $netIncome >= 0 ? 'text-emerald-400' : 'text-red-400' }} text-right">Laba (Rugi) Bersih</td>
                            <td class="px-5 py-5 text-right font-black text-lg {{ $netIncome >= 0 ? 'text-emerald-400' : 'text-red-400' }}">Rp {{ number_format($netIncome, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. NERACA -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-700/50 bg-gray-900/50 flex justify-between items-center">
                <h3 class="font-bold text-white flex items-center gap-2 m-0 uppercase tracking-widest text-sm">
                    <i data-lucide="scale" class="w-4 h-4 text-indigo-400"></i> Neraca (Laporan Posisi Keuangan)
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-700/50 text-sm">
                <!-- Aset -->
                <div class="flex flex-col">
                    <div class="bg-emerald-500/10 px-5 py-3 font-bold text-emerald-400 uppercase tracking-wider text-xs border-b border-emerald-500/20 text-center">Aset</div>
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <tbody class="divide-y divide-gray-700/50">
                                @foreach($assets as $asset)
                                    @if($asset->period_balance != 0)
                                    <tr class="hover:bg-emerald-500/5 transition-colors">
                                        <td class="px-5 py-3 flex justify-between text-gray-200"><span>{{ $asset->name }}</span> <span class="text-gray-500 font-mono text-xs">{{ $asset->code }}</span></td>
                                        <td class="px-5 py-3 text-right text-gray-200">Rp {{ number_format($asset->period_balance, 0, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                @if($assets->sum('period_balance') == 0)
                                    <tr><td class="px-5 py-4 text-gray-500 italic">Tidak ada saldo aset</td><td class="px-5 py-4 text-right text-gray-500">Rp 0</td></tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 mt-auto bg-gray-900/50 border-t border-gray-700/50 flex justify-between items-center">
                        <div class="font-bold text-gray-300 uppercase text-xs tracking-wider">Total Aset</div>
                        <div class="text-right font-black text-white text-base">Rp {{ number_format($totalAssets, 0, ',', '.') }}</div>
                    </div>
                </div>
                
                <!-- Kewajiban & Ekuitas -->
                <div class="flex flex-col">
                    <div class="bg-red-500/10 px-5 py-3 font-bold text-red-400 uppercase tracking-wider text-xs border-b border-red-500/20 text-center">Kewajiban</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <tbody class="divide-y divide-gray-700/50">
                                @foreach($liabilities as $liab)
                                    @if($liab->period_balance != 0)
                                    <tr class="hover:bg-red-500/5 transition-colors">
                                        <td class="px-5 py-3 flex justify-between text-gray-200"><span>{{ $liab->name }}</span> <span class="text-gray-500 font-mono text-xs">{{ $liab->code }}</span></td>
                                        <td class="px-5 py-3 text-right text-gray-200">Rp {{ number_format($liab->period_balance, 0, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-5 py-2 text-right font-bold text-gray-300 text-xs bg-gray-900/20 border-t border-gray-700/30">Total: Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</div>
                    </div>
                    
                    <div class="bg-indigo-500/10 px-5 py-3 font-bold text-indigo-400 uppercase tracking-wider text-xs border-y border-indigo-500/20 text-center">Ekuitas</div>
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-left whitespace-nowrap">
                            <tbody class="divide-y divide-gray-700/50">
                                @foreach($equities as $eq)
                                    @if($eq->period_balance != 0)
                                    <tr class="hover:bg-indigo-500/5 transition-colors">
                                        <td class="px-5 py-3 flex justify-between text-gray-200"><span>{{ $eq->name }}</span> <span class="text-gray-500 font-mono text-xs">{{ $eq->code }}</span></td>
                                        <td class="px-5 py-3 text-right text-gray-200">Rp {{ number_format($eq->period_balance, 0, ',', '.') }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                <tr class="hover:bg-indigo-500/5 transition-colors">
                                    <td class="px-5 py-3 flex justify-between text-gray-200"><span>Laba Ditahan</span> <span class="text-gray-500 font-mono text-xs">300x</span></td>
                                    <td class="px-5 py-3 text-right text-gray-200">Rp {{ number_format($retainedEarnings, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="px-5 py-2 text-right font-bold text-gray-300 text-xs bg-gray-900/20 border-t border-gray-700/30">Total: Rp {{ number_format($totalEquity, 0, ',', '.') }}</div>
                    </div>
                    <div class="p-4 bg-gray-900/50 border-t border-gray-700/50 flex justify-between items-center">
                        <div class="font-bold text-gray-300 uppercase text-xs tracking-wider">Total Liabilitas & Ekuitas</div>
                        <div class="text-right font-black text-white text-base">Rp {{ number_format($totalLiabilities + $totalEquity, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. ARUS KAS -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-700/50 bg-gray-900/50 flex justify-between items-center">
                <h3 class="font-bold text-white flex items-center gap-2 m-0 uppercase tracking-widest text-sm">
                    <i data-lucide="bar-chart-2" class="w-4 h-4 text-indigo-400"></i> Laporan Arus Kas
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <tbody class="divide-y divide-gray-700/50">
                        <tr class="bg-gray-900/30">
                            <td class="px-5 py-4 text-gray-200 font-bold">Saldo Kas Awal <span class="text-gray-500 font-normal text-xs ml-2">(Per 1 {{ $startDate->translatedFormat('F Y') }})</span></td>
                            <td class="px-5 py-4 text-right font-bold text-indigo-400">Rp {{ number_format($cashInitial, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="bg-emerald-500/10"><td colspan="2" class="px-5 py-3 font-bold text-emerald-400 uppercase tracking-wider text-xs">Arus Kas Masuk</td></tr>
                        <tr class="hover:bg-emerald-500/5 transition-colors">
                            <td class="px-5 py-4 pl-8 text-gray-300">Penerimaan Kas (Pendapatan, Piutang, Modal dll)</td>
                            <td class="px-5 py-4 text-right text-emerald-400 font-bold">+ Rp {{ number_format($cashInflow, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="bg-red-500/10"><td colspan="2" class="px-5 py-3 font-bold text-red-400 uppercase tracking-wider text-xs">Arus Kas Keluar</td></tr>
                        <tr class="hover:bg-red-500/5 transition-colors">
                            <td class="px-5 py-4 pl-8 text-gray-300">Pengeluaran Kas (Beban, Prive, Aset dll)</td>
                            <td class="px-5 py-4 text-right text-red-400 font-bold">- Rp {{ number_format($cashOutflow, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="bg-gray-900/50 border-t-2 border-gray-700">
                            <td class="px-5 py-4 font-bold text-gray-200 text-right uppercase tracking-wider text-xs">Kenaikan (Penurunan) Kas Bersih</td>
                            <td class="px-5 py-4 text-right font-bold {{ ($cashInflow - $cashOutflow) >= 0 ? 'text-emerald-400' : 'text-red-400' }}">Rp {{ number_format($cashInflow - $cashOutflow, 0, ',', '.') }}</td>
                        </tr>
                        
                        <tr class="bg-indigo-500/10 border-t border-indigo-500/30">
                            <td class="px-5 py-5 font-black uppercase tracking-wider text-sm text-right text-indigo-300">Saldo Kas Akhir <span class="text-indigo-400/50 lowercase ml-2 font-medium">(Per {{ $endDate->translatedFormat('d F Y') }})</span></td>
                            <td class="px-5 py-5 text-right font-black text-lg text-indigo-400">Rp {{ number_format($cashEnding, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. PERUBAHAN EKUITAS -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-700/50 bg-gray-900/50 flex justify-between items-center">
                <h3 class="font-bold text-white flex items-center gap-2 m-0 uppercase tracking-widest text-sm">
                    <i data-lucide="pie-chart" class="w-4 h-4 text-indigo-400"></i> Laporan Perubahan Ekuitas
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <tbody class="divide-y divide-gray-700/50">
                        <tr class="bg-gray-900/30">
                            <td class="px-5 py-4 text-gray-200 font-bold">Modal Awal <span class="text-gray-500 font-normal text-xs ml-2">(Per 1 {{ $startDate->translatedFormat('F Y') }})</span></td>
                            <td class="px-5 py-4 text-right font-bold text-indigo-400">Rp {{ number_format($beginningEquity, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="hover:bg-indigo-500/5 transition-colors">
                            <td class="px-5 py-4 pl-8 text-gray-300">Laba (Rugi) Bersih Periode Berjalan</td>
                            <td class="px-5 py-4 text-right font-bold {{ $netIncome >= 0 ? 'text-emerald-400' : 'text-red-400' }}">{{ $netIncome >= 0 ? '+' : '-' }} Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</td>
                        </tr>
                        <tr class="hover:bg-indigo-500/5 transition-colors">
                            <td class="px-5 py-4 pl-8 text-gray-300">Prive (Penarikan Modal Pemilik)</td>
                            <td class="px-5 py-4 text-right font-bold text-red-400">- Rp {{ number_format($prive, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="bg-gray-900/50 border-t border-gray-700">
                            <td class="px-5 py-4 font-bold text-gray-200 text-right uppercase tracking-wider text-xs">Penambahan (Pengurangan) Ekuitas</td>
                            <td class="px-5 py-4 text-right font-bold {{ ($netIncome - $prive) >= 0 ? 'text-emerald-400' : 'text-red-400' }}">Rp {{ number_format($netIncome - $prive, 0, ',', '.') }}</td>
                        </tr>

                        <tr class="bg-indigo-500/10 border-t-2 border-indigo-500/30">
                            <td class="px-5 py-5 font-black uppercase tracking-wider text-sm text-right text-indigo-300">Modal Akhir <span class="text-indigo-400/50 lowercase ml-2 font-medium">(Per {{ $endDate->translatedFormat('d F Y') }})</span></td>
                            <td class="px-5 py-5 text-right font-black text-lg text-indigo-400">Rp {{ number_format($endingEquity, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <!-- 5. CATATAN -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col mt-6">
            <div class="p-4 border-b border-gray-700/50 bg-gray-900/50 flex justify-between items-center">
                <h3 class="font-bold text-white flex items-center gap-2 m-0 uppercase tracking-widest text-sm">
                    <i data-lucide="file-text" class="w-4 h-4 text-indigo-400"></i> Catatan atas Laporan Keuangan
                </h3>
            </div>
            <div class="p-6 text-sm leading-relaxed text-gray-400 space-y-6">
                <section>
                    <h4 class="text-white font-bold mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> 1. Gambaran Umum
                    </h4>
                    <p class="pl-4">
                        Laporan keuangan ini menyajikan posisi keuangan (Neraca), kinerja keuangan (Laba Rugi), arus kas, 
                        serta perubahan ekuitas perusahaan untuk periode pembukuan yang berakhir pada <strong class="text-indigo-300 font-medium font-mono text-xs">{{ $endDate->translatedFormat('d F Y') }}</strong>. 
                        Laporan disusun secara real-time berdasarkan transaksi kas dan operasional modul ERP.
                    </p>
                </section>

                <section>
                    <h4 class="text-white font-bold mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span> 2. Analisis Saldo Material
                    </h4>
                    <div class="pl-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-900/40 p-3 rounded-lg border border-gray-700/30">
                            <span class="text-xs text-gray-500 block mb-1 uppercase tracking-wider">Likuiditas (Saldo Akhir)</span>
                            <span class="text-indigo-400 font-bold">Rp {{ number_format($cashEnding, 0, ',', '.') }}</span>
                        </div>
                        <div class="bg-gray-900/40 p-3 rounded-lg border border-gray-700/30">
                            <span class="text-xs text-gray-500 block mb-1 uppercase tracking-wider">Kinerja ({{ $netIncome >= 0 ? 'Laba' : 'Rugi' }} Bersih)</span>
                            <span class="{{ $netIncome >= 0 ? 'text-emerald-400' : 'text-red-400' }} font-bold">Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>

    </div>

@push('styles')
<style>
@media print {
    body { background: white !important; color: black !important; }
    .print\:hidden { display: none !important; }
    #page-content { padding: 0 !important; margin: 0 !important; background: white !important; }
    .bg-gray-800, .bg-gray-900\/50, .bg-gray-900\/30, .bg-indigo-500\/10 { background: white !important; border: 1px solid #eee !important; }
    .text-white, .text-indigo-400, .text-gray-200, .text-gray-300, .text-indigo-300 { color: black !important; }
    .text-gray-400, .text-gray-500 { color: #555 !important; }
    .border-indigo-500\/30, .border-emerald-500\/20, .border-red-500\/20 { border-color: #333 !important; }
    table { width: 100% !important; border-collapse: collapse !important; }
    tr { page-break-inside: avoid !important; }
}
</style>
@endpush
@endsection

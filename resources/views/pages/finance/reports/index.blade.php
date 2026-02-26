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

<div x-data="{ activeTab: 'laba-rugi' }" class="w-full">
    <div class="flex overflow-x-auto border-b border-gray-700/50 mb-6 hide-scrollbar print:hidden">
        <button @click="activeTab = 'laba-rugi'" :class="activeTab === 'laba-rugi' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
            <i data-lucide="trending-up" class="w-4 h-4"></i> Laba Rugi
        </button>
        <button @click="activeTab = 'neraca'" :class="activeTab === 'neraca' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
            <i data-lucide="scale" class="w-4 h-4"></i> Neraca
        </button>
        <button @click="activeTab = 'arus-kas'" :class="activeTab === 'arus-kas' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
            <i data-lucide="bar-chart-2" class="w-4 h-4"></i> Arus Kas
        </button>
        <button @click="activeTab = 'ekuitas'" :class="activeTab === 'ekuitas' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
            <i data-lucide="pie-chart" class="w-4 h-4"></i> Ekuitas
        </button>
        <button @click="activeTab = 'catatan'" :class="activeTab === 'catatan' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800/50' : 'text-gray-400 hover:text-white hover:bg-gray-800/30'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
            <i data-lucide="file-text" class="w-4 h-4"></i> Catatan
        </button>
    </div>

    <!-- 1. LABA RUGI -->
    <div x-show="activeTab === 'laba-rugi'" x-cloak :class="{'print:block': activeTab === 'laba-rugi', 'print:hidden': activeTab !== 'laba-rugi'}" class="bg-white print:bg-white border sm:border-gray-200 rounded-xl p-8 sm:p-12 shadow-sm text-gray-800 max-w-4xl mx-auto print:max-w-none print:shadow-none print:border-none print:p-0">
        <div class="text-center mb-10 border-b border-gray-200 pb-6">
            <h3 class="text-2xl font-bold mb-1 uppercase tracking-wider text-black">Laporan Laba Rugi</h3>
            <p class="text-gray-500 text-sm">Untuk periode bulan {{ $startDate->translatedFormat('F Y') }}</p>
        </div>
        
        <table class="w-full text-sm">
            <tbody>
                <tr class="bg-gray-50"><td colspan="2" class="px-4 py-3 font-bold border-y border-gray-200">Pendapatan</td></tr>
                @foreach($revenues as $rev)
                    @if($rev->period_balance != 0)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 pl-8 flex justify-between"><span>{{ $rev->name }}</span> <span class="text-gray-400 font-mono text-xs">{{ $rev->code }}</span></td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($rev->period_balance, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                @if($revenues->sum('period_balance') == 0)
                    <tr class="border-b border-gray-100"><td class="px-4 py-4 pl-8 text-gray-400 italic">Tidak ada transaksi pendapatan</td><td class="px-4 py-4 text-right">Rp 0</td></tr>
                @endif
                <tr class="border-b border-gray-200 bg-gray-50 mt-2">
                    <td class="px-4 py-3 font-bold text-gray-700">Total Pendapatan</td>
                    <td class="px-4 py-3 text-right font-bold text-black border-t-2 border-black">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" class="py-4"></td></tr>

                <tr class="bg-gray-50"><td colspan="2" class="px-4 py-3 font-bold border-y border-gray-200">Beban (Biaya)</td></tr>
                @foreach($expenses as $exp)
                    @if($exp->period_balance != 0)
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 pl-8 flex justify-between"><span>{{ $exp->name }}</span> <span class="text-gray-400 font-mono text-xs">{{ $exp->code }}</span></td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($exp->period_balance, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                @endforeach
                @if($expenses->sum('period_balance') == 0)
                    <tr class="border-b border-gray-100"><td class="px-4 py-4 pl-8 text-gray-400 italic">Tidak ada transaksi beban</td><td class="px-4 py-4 text-right">Rp 0</td></tr>
                @endif
                <tr class="border-b border-gray-200 bg-gray-50 mt-2">
                    <td class="px-4 py-3 font-bold text-gray-700">Total Beban</td>
                    <td class="px-4 py-3 text-right font-bold text-black border-t-2 border-black">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" class="py-4"></td></tr>

                <tr>
                    <td class="px-4 py-4 font-black uppercase text-base {{ $netIncome >= 0 ? 'text-emerald-700' : 'text-red-700' }} tracking-wider">Laba (Rugi) Bersih</td>
                    <td class="px-4 py-4 text-right font-black text-lg border-y-[3px] border-double border-black {{ $netIncome >= 0 ? 'text-emerald-700' : 'text-red-700' }}">Rp {{ number_format($netIncome, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 2. NERACA -->
    <div x-show="activeTab === 'neraca'" x-cloak :class="{'print:block': activeTab === 'neraca', 'print:hidden': activeTab !== 'neraca'}" style="display: none;" class="bg-white print:bg-white border sm:border-gray-200 rounded-xl p-8 sm:p-12 shadow-sm text-gray-800 max-w-5xl mx-auto print:max-w-none print:shadow-none print:border-none print:p-0">
        <div class="text-center mb-10 border-b border-gray-200 pb-6">
            <h3 class="text-2xl font-bold mb-1 uppercase tracking-wider text-black">Neraca (Laporan Posisi Keuangan)</h3>
            <p class="text-gray-500 text-sm">Per {{ $endDate->translatedFormat('d F Y') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12 w-full">
            <!-- Aset -->
            <div class="flex flex-col h-full">
                <table class="w-full text-sm flex-1">
                    <tbody>
                        <tr class="bg-emerald-50"><td colspan="2" class="px-4 py-3 font-bold text-emerald-900 border-y border-emerald-200 uppercase tracking-wider text-center">Aset</td></tr>
                        @foreach($assets as $asset)
                            @if($asset->period_balance != 0)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 flex justify-between"><span>{{ $asset->name }}</span> <span class="text-gray-400 font-mono text-xs">{{ $asset->code }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">Rp {{ number_format($asset->period_balance, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        @if($assets->sum('period_balance') == 0)
                            <tr class="border-b border-gray-100"><td class="px-4 py-4 text-gray-400 italic">Tidak ada saldo aset</td><td class="px-4 py-4 text-right">Rp 0</td></tr>
                        @endif
                    </tbody>
                </table>
                <table class="w-full text-sm mt-8 border-t-[3px] border-double border-emerald-700">
                    <tr>
                        <td class="px-4 py-4 font-black text-emerald-800 uppercase text-base">Total Aset</td>
                        <td class="px-4 py-4 text-right font-black text-emerald-800 text-lg">Rp {{ number_format($totalAssets, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Kewajiban & Ekuitas -->
            <div class="flex flex-col h-full">
                <table class="w-full text-sm">
                    <tbody>
                        <tr class="bg-red-50"><td colspan="2" class="px-4 py-3 font-bold text-red-900 border-y border-red-200 uppercase tracking-wider text-center">Kewajiban (Liabilitas)</td></tr>
                        @foreach($liabilities as $liab)
                            @if($liab->period_balance != 0)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 flex justify-between"><span>{{ $liab->name }}</span> <span class="text-gray-400 font-mono text-xs">{{ $liab->code }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">Rp {{ number_format($liab->period_balance, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        <tr class="border-b border-gray-200 bg-gray-50 mt-1">
                            <td class="px-4 py-2 font-medium text-gray-600 italic text-xs">Total Kewajiban</td>
                            <td class="px-4 py-2 text-right font-bold border-t border-gray-300">Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</td>
                        </tr>

                        <tr><td colspan="2" class="py-2"></td></tr>

                        <tr class="bg-indigo-50"><td colspan="2" class="px-4 py-3 font-bold text-indigo-900 border-y border-indigo-200 uppercase tracking-wider text-center">Ekuitas</td></tr>
                        @foreach($equities as $eq)
                            @if($eq->period_balance != 0)
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="px-4 py-3 flex justify-between"><span>{{ $eq->name }}</span> <span class="text-gray-400 font-mono text-xs">{{ $eq->code }}</span></td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">Rp {{ number_format($eq->period_balance, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                        @endforeach
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 flex justify-between"><span>Laba Ditahan</span> <span class="text-gray-400 font-mono text-xs">300x</span></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">Rp {{ number_format($retainedEarnings, 0, ',', '.') }}</td>
                        </tr>
                        <tr class="border-b border-gray-200 bg-gray-50 mt-1">
                            <td class="px-4 py-2 font-medium text-gray-600 italic text-xs">Total Ekuitas</td>
                            <td class="px-4 py-2 text-right font-bold border-t border-gray-300">Rp {{ number_format($totalEquity, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
                <div class="flex-1 mt-auto"></div>
                <table class="w-full text-sm mt-8 border-t-[3px] border-double border-indigo-700">
                    <tr>
                        <td class="px-4 py-4 font-black text-indigo-800 uppercase text-base shrink-0">Total Kewajiban & Ekuitas</td>
                        <td class="px-4 py-4 text-right font-black text-indigo-800 text-lg">Rp {{ number_format($totalLiabilities + $totalEquity, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. ARUS KAS -->
    <div x-show="activeTab === 'arus-kas'" x-cloak :class="{'print:block': activeTab === 'arus-kas', 'print:hidden': activeTab !== 'arus-kas'}" style="display: none;" class="bg-white print:bg-white border sm:border-gray-200 rounded-xl p-8 sm:p-12 shadow-sm text-gray-800 max-w-4xl mx-auto print:max-w-none print:shadow-none print:border-none print:p-0">
        <div class="text-center mb-10 border-b border-gray-200 pb-6">
            <h3 class="text-2xl font-bold mb-1 uppercase tracking-wider text-black">Laporan Arus Kas</h3>
            <p class="text-gray-500 text-sm">Untuk periode bulan {{ $startDate->translatedFormat('F Y') }}</p>
        </div>

        <table class="w-full text-sm text-lg">
            <tbody>
                <tr class="border-b-2 border-gray-800 mb-4 bg-gray-50">
                    <td class="px-4 py-4"><span class="font-bold">Saldo Kas Awal</span> <span class="text-gray-500 text-sm">(Per 1 {{ $startDate->translatedFormat('F Y') }})</span></td>
                    <td class="px-4 py-4 text-right font-bold text-indigo-700">Rp {{ number_format($cashInitial, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" class="py-4"></td></tr>
                
                <tr class="bg-gray-50"><td colspan="2" class="px-4 py-3 font-bold border-y border-gray-200">Arus Kas Masuk</td></tr>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-4 pl-8">Penerimaan Kas (Pendapatan, Piutang, Modal dll)</td>
                    <td class="px-4 py-4 text-right text-emerald-600 font-medium">+ Rp {{ number_format($cashInflow, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" class="py-2"></td></tr>

                <tr class="bg-gray-50"><td colspan="2" class="px-4 py-3 font-bold border-y border-gray-200">Arus Kas Keluar</td></tr>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-4 pl-8">Pengeluaran Kas (Beban, Prive, Aset dll)</td>
                    <td class="px-4 py-4 text-right text-red-600 font-medium">- Rp {{ number_format($cashOutflow, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" class="py-4"></td></tr>

                <tr class="border-t border-gray-300">
                    <td class="px-4 py-4 font-medium text-gray-600">Kenaikan (Penurunan) Kas Bersih</td>
                    <td class="px-4 py-4 text-right font-bold border-b border-gray-300 {{ ($cashInflow - $cashOutflow) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($cashInflow - $cashOutflow, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" class="py-4"></td></tr>
                
                <tr>
                    <td class="px-4 py-5 font-black uppercase tracking-wider text-xl border-y-[3px] border-double border-black">Saldo Kas Akhir <span class="font-medium text-sm text-gray-500 lowercase">(Per {{ $endDate->translatedFormat('d F Y') }})</span></td>
                    <td class="px-4 py-5 text-right font-black text-2xl border-y-[3px] border-double border-black text-indigo-700">Rp {{ number_format($cashEnding, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 4. PERUBAHAN EKUITAS -->
    <div x-show="activeTab === 'ekuitas'" x-cloak :class="{'print:block': activeTab === 'ekuitas', 'print:hidden': activeTab !== 'ekuitas'}" style="display: none;" class="bg-white print:bg-white border sm:border-gray-200 rounded-xl p-8 sm:p-12 shadow-sm text-gray-800 max-w-4xl mx-auto print:max-w-none print:shadow-none print:border-none print:p-0">
        <div class="text-center mb-10 border-b border-gray-200 pb-6">
            <h3 class="text-2xl font-bold mb-1 uppercase tracking-wider text-black">Laporan Perubahan Ekuitas</h3>
            <p class="text-gray-500 text-sm">Untuk periode bulan {{ $startDate->translatedFormat('F Y') }}</p>
        </div>

        <table class="w-full text-sm text-lg">
            <tbody>
                <tr class="border-b-2 border-gray-800 bg-gray-50">
                    <td class="px-4 py-4"><span class="font-bold">Modal Awal</span> <span class="text-gray-500 text-sm">(Per 1 {{ $startDate->translatedFormat('F Y') }})</span></td>
                    <td class="px-4 py-4 text-right font-bold text-indigo-700">Rp {{ number_format($beginningEquity, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" class="py-4"></td></tr>

                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-4 pl-8">Laba (Rugi) Bersih Periode Berjalan</td>
                    <td class="px-4 py-4 text-right font-medium {{ $netIncome >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $netIncome >= 0 ? '+' : '-' }} Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="px-4 py-4 pl-8">Prive (Penarikan Modal Pemilik)</td>
                    <td class="px-4 py-4 text-right font-medium text-red-600">- Rp {{ number_format($prive, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" class="py-4"></td></tr>

                <tr class="border-t border-gray-300">
                    <td class="px-4 py-4 font-medium text-gray-600">Penambahan (Pengurangan) Ekuitas</td>
                    <td class="px-4 py-4 text-right font-bold border-b border-gray-300 {{ ($netIncome - $prive) >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($netIncome - $prive, 0, ',', '.') }}</td>
                </tr>

                <tr><td colspan="2" class="py-4"></td></tr>

                <tr>
                    <td class="px-4 py-5 font-black uppercase tracking-wider text-xl border-y-[3px] border-double border-black">Modal Akhir <span class="font-medium text-sm text-gray-500 lowercase">(Per {{ $endDate->translatedFormat('d F Y') }})</span></td>
                    <td class="px-4 py-5 text-right font-black text-2xl border-y-[3px] border-double border-black text-indigo-700">Rp {{ number_format($endingEquity, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- 5. CATATAN -->
    <div x-show="activeTab === 'catatan'" x-cloak :class="{'print:block': activeTab === 'catatan', 'print:hidden': activeTab !== 'catatan'}" style="display: none;" class="bg-white print:bg-white border sm:border-gray-200 rounded-xl p-8 sm:p-12 shadow-sm text-gray-800 max-w-4xl mx-auto print:max-w-none print:shadow-none print:border-none print:p-0">
        <div class="text-center mb-10 border-b border-gray-200 pb-6">
            <h3 class="text-2xl font-bold mb-1 uppercase tracking-wider text-black">Catatan atas Laporan Keuangan</h3>
            <p class="text-gray-500 text-sm">Periode berakhir pada {{ $endDate->translatedFormat('d F Y') }}</p>
        </div>

        <div class="space-y-12 text-base leading-relaxed text-gray-700">
            <section>
                <h4 class="text-lg font-bold text-black border-b border-gray-200 pb-2 mb-4">1. Gambaran Umum</h4>
                <p>
                    Laporan keuangan ini menyajikan posisi keuangan (Neraca), kinerja keuangan (Laba Rugi), arus kas, 
                    serta perubahan ekuitas perusahaan untuk periode pembukuan yang berakhir pada <strong class="text-black">{{ $endDate->translatedFormat('d F Y') }}</strong>. 
                    Laporan disusun menggunakan basis jurnal otomatis (Auto-Posted Journal) yang tersinkronisasi secara real-time dengan seluruh transaksi kas dan operasional modul ERP.
                </p>
            </section>

            <section>
                <h4 class="text-lg font-bold text-black border-b border-gray-200 pb-2 mb-4">2. Kebijakan Akuntansi Signifikan</h4>
                <ul class="list-disc list-outside pl-6 space-y-3">
                    <li><strong class="text-black">Kas dan Setara Kas:</strong> Saldo kas dilaporkan pada nilai nominalnya berdasarkan arus masuk dan arus keluar aktual yang dicatat dalam sistem buku kas perusahaan periode ini.</li>
                    <li><strong class="text-black">Pendapatan:</strong> Pendapatan diakui pada saat terjadi penambahan nilai tambah ekonomi (basis kas riil) ke dalam perusahaan berdasarkan transaksi kas masuk.</li>
                    <li><strong class="text-black">Beban:</strong> Beban diakui secara proporsional ketika terdapat pengeluaran kas atau penggunaan manfaat sumber daya di bulan yang bersangkutan secara operasional.</li>
                </ul>
            </section>

            <section>
                <h4 class="text-lg font-bold text-black border-b border-gray-200 pb-2 mb-4">3. Analisis dan Rincian Saldo Material</h4>
                <div class="bg-indigo-50/50 border border-indigo-100 rounded-xl p-6 shadow-sm">
                    <div class="space-y-4">
                        <p><strong class="text-indigo-900 block font-black text-sm uppercase tracking-wider mb-1">Likuiditas (Kas Aset Lancar):</strong> Saldo kas di akhir periode tercatat kuat sebesar <strong class="text-black bg-white px-1.5 py-0.5 rounded shadow-sm border border-gray-200 font-mono">Rp {{ number_format($cashEnding, 0, ',', '.') }}</strong>, yang merupakan representasi likuiditas tunai perusahaan untuk periode operasi masa depan.</p>
                        
                        <p><strong class="text-indigo-900 block font-black text-sm uppercase tracking-wider mb-1">Posisi Aset Teragregasi:</strong> Total seluruh aset berwujud perusahaan disajikan senilai <strong class="text-black bg-white px-1.5 py-0.5 rounded shadow-sm border border-gray-200 font-mono">Rp {{ number_format($totalAssets, 0, ',', '.') }}</strong> pada neraca akhir.</p>
                        
                        <p><strong class="text-indigo-900 block font-black text-sm uppercase tracking-wider mb-1">Kewajiban Pokok:</strong> Total kewajiban (liabilitas) yang harus diselesaikan oleh perusahaan dalam waktu dekat maupun jangka panjang tercatat sebesar <strong class="text-black bg-white px-1.5 py-0.5 rounded shadow-sm border border-gray-200 font-mono">Rp {{ number_format($totalLiabilities, 0, ',', '.') }}</strong>.</p>
                        
                        <p><strong class="text-indigo-900 block font-black text-sm uppercase tracking-wider mb-1">Kinerja Finansial (Laba/Rugi Bersih):</strong> Pada laporan laba rugi komprehensif, operasional perusahaan selama satu bulan berjalan mencetak <strong class="{{ $netIncome >= 0 ? 'text-emerald-700' : 'text-red-700' }} uppercase font-bold text-lg">{{ $netIncome >= 0 ? 'Laba' : 'Rugi' }}</strong> sebesar <strong class="{{ $netIncome >= 0 ? 'text-emerald-700' : 'text-red-700' }} bg-white px-1.5 py-0.5 rounded shadow-sm border border-gray-200 font-mono text-lg">Rp {{ number_format(abs($netIncome), 0, ',', '.') }}</strong>.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

@push('styles')
<style>
@media print {
    /* Hide everything except the active tab content */
    body > *:not(main) { display: none !important; }
    main > *:not(#page-content) { display: none !important; }
    #page-content { padding: 0 !important; margin: 0 !important; background: white !important; }
    
    /* Layout adjustments for print */
    body { background: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    @page { margin: 2cm; }
}
</style>
@endpush
@endsection

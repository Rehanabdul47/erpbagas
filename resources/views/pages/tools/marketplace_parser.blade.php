@extends('layouts.app')

@section('title', 'Marketplace Parser')
@section('breadcrumb', 'Tools / Marketplace Parser')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    {{-- Header Section --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Marketplace Parser</h1>
        <p class="text-gray-400">Analisis data ekspor pesanan TikTok Shop dan Shopee untuk melihat Omzet, HPP, Profit, dan Statistik lainnya.</p>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Upload Form --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-2xl p-6 shadow-xl">
        <form action="{{ route('tools.marketplace-parser.parse') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                {{-- TikTok Upload --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="music" class="w-4 h-4 text-pink-500"></i> File TikTok Shop
                    </label>
                    <div class="relative group">
                        <input type="file" name="tiktok_file" id="tiktok_file" accept=".xlsx,.xls,.csv"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="bg-gray-900 border-2 border-dashed border-gray-700 group-hover:border-pink-500/50 rounded-xl p-8 text-center transition-all">
                            <div class="w-12 h-12 bg-pink-500/10 text-pink-500 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                            </div>
                            <p id="tiktok-file-name" class="text-sm text-gray-400 group-hover:text-gray-300">Pilih file ekspor TikTok (.xlsx, .csv)</p>
                        </div>
                    </div>
                </div>

                {{-- Shopee Upload --}}
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-orange-500"></i> File Shopee
                    </label>
                    <div class="relative group">
                        <input type="file" name="shopee_file" id="shopee_file" accept=".xlsx,.xls,.csv"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="bg-gray-900 border-2 border-dashed border-gray-700 group-hover:border-orange-500/50 rounded-xl p-8 text-center transition-all">
                            <div class="w-12 h-12 bg-orange-500/10 text-orange-500 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                            </div>
                            <p id="shopee-file-name" class="text-sm text-gray-400 group-hover:text-gray-300">Pilih file ekspor Shopee (.xlsx, .csv)</p>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" 
                class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-600/20 transition-all flex items-center justify-center gap-2 group">
                <span>Mulai Analisis</span>
                <i data-lucide="bar-chart-2" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
            </button>
        </form>
    </div>

    {{-- Analysis Results --}}
    @if(isset($tiktokData) || isset($shopeeData))
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            {{-- TikTok Results --}}
            @if(isset($tiktokData))
                <div class="bg-gray-800 border border-gray-700/50 rounded-2xl overflow-hidden flex flex-col shadow-xl">
                    <div class="bg-pink-500/10 border-b border-pink-500/20 px-6 py-4 flex items-center gap-3">
                        <i data-lucide="music" class="w-6 h-6 text-pink-500"></i>
                        <h2 class="text-xl font-bold text-pink-400">Analisis TikTok Shop</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Key Metrics --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-900 border border-gray-700 rounded-xl p-4">
                                <div class="text-[10px] font-bold text-gray-500 uppercase mb-1">Total Pesanan Unik</div>
                                <div class="text-2xl font-bold text-white">{{ number_format($tiktokData['total_orders']) }}</div>
                            </div>
                            <div class="bg-gray-900 border border-gray-700 rounded-xl p-4">
                                <div class="text-[10px] font-bold text-gray-500 uppercase mb-1">Total SKU Terjual</div>
                                <div class="text-2xl font-bold text-white">{{ number_format($tiktokData['total_skus']) }}</div>
                            </div>
                            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4">
                                <div class="text-[10px] font-bold text-emerald-500/70 uppercase mb-1">Total Omzet Bersih</div>
                                <div class="text-2xl font-bold text-emerald-400">Rp {{ number_format($tiktokData['omzet'], 0, ',', '.') }}</div>
                            </div>
                            <div class="bg-indigo-500/10 border border-indigo-500/20 rounded-xl p-4">
                                <div class="text-[10px] font-bold text-indigo-500/70 uppercase mb-1">Estimasi Profit (Omzet - HPP)</div>
                                <div class="text-2xl font-bold text-indigo-400">Rp {{ number_format($tiktokData['profit'], 0, ',', '.') }}</div>
                                <div class="text-xs text-indigo-500/70 mt-1">HPP: Rp {{ number_format($tiktokData['hpp_total'], 0, ',', '.') }}</div>
                            </div>
                        </div>

                        {{-- Cancellations & Duplications --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 flex justify-between items-center">
                                <div>
                                    <div class="text-[10px] font-bold text-red-500/70 uppercase mb-1">Pesanan Dibatalkan</div>
                                    <div class="text-xl font-bold text-red-400">{{ number_format($tiktokData['batal_count']) }} Pesanan</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-bold text-red-500/70 uppercase mb-1">Potensi Omzet Hilang</div>
                                    <div class="text-xl font-bold text-red-400">Rp {{ number_format($tiktokData['batal_omzet'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 flex justify-between items-center">
                                <div>
                                    <div class="text-[10px] font-bold text-amber-500/70 uppercase mb-1">Duplikasi Order ID</div>
                                    <div class="text-xl font-bold text-amber-400">{{ number_format($tiktokData['duplicate_count']) }} ID</div>
                                </div>
                                <i data-lucide="copy" class="w-8 h-8 text-amber-500/30"></i>
                            </div>
                        </div>

                        {{-- Duplicate List (Conditional) --}}
                        @if($tiktokData['duplicate_count'] > 0)
                        <div x-data="{ show: false }">
                            <button @click="show = !show" class="text-[10px] text-amber-500 hover:text-amber-400 uppercase font-bold tracking-widest flex items-center gap-1 mb-2">
                                <i :class="show ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-3 h-3 transition-transform"></i>
                                Lihat Daftar ID Terduplikasi
                            </button>
                            <div x-show="show" x-transition class="bg-gray-900 border border-gray-700 rounded-xl p-3 max-h-32 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($tiktokData['duplicate_orders'] as $id => $count)
                                        <span class="px-2 py-1 bg-gray-800 border border-gray-700 rounded text-[10px] text-gray-400 font-mono">
                                            {{ $id }} <span class="text-amber-500">({{ $count }}x)</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Status Breakdown --}}
                        <div>
                            <h3 class="text-sm font-bold text-white mb-3 uppercase tracking-wider flex items-center gap-2"><i data-lucide="layers" class="w-4 h-4 text-indigo-400"></i> Ringkasan Status Pesanan</h3>
                            <div class="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden">
                                <table class="w-full text-left text-xs whitespace-nowrap">
                                    <thead class="bg-gray-800 text-gray-400 uppercase">
                                        <tr>
                                            <th class="px-4 py-2">Status</th>
                                            <th class="px-4 py-2 text-right">Jumlah Pesanan</th>
                                            <th class="px-4 py-2 text-right">Qty Item</th>
                                            <th class="px-4 py-2 text-right">Total Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700/50">
                                        @foreach($tiktokData['status_breakdown'] as $status => $stats)
                                        <tr class="hover:bg-gray-800/50">
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                                    {{ in_array($status, ['Selesai', 'Completed']) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 
                                                       (in_array($status, ['Batal', 'Dibatalkan', 'Cancelled']) ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20') }}">
                                                    {{ $status ?: 'Unknown' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right text-gray-300">{{ number_format($stats['count']) }}</td>
                                            <td class="px-4 py-2 text-right text-gray-300">{{ number_format($stats['qty']) }}</td>
                                            <td class="px-4 py-2 text-right font-bold text-white">Rp {{ number_format($stats['omzet'], 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Ranking Produk --}}
                        <div>
                            <h3 class="text-sm font-bold text-white mb-3 uppercase tracking-wider flex items-center gap-2"><i data-lucide="award" class="w-4 h-4 text-yellow-500"></i> Top Produk (Seller SKU)</h3>
                            <div class="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden">
                                <table class="w-full text-left text-xs whitespace-nowrap">
                                    <thead class="bg-gray-800 text-gray-400 uppercase">
                                        <tr>
                                            <th class="px-4 py-2">Seller SKU</th>
                                            <th class="px-4 py-2">Nama Produk</th>
                                            <th class="px-4 py-2 text-right">Terjual</th>
                                            <th class="px-4 py-2 text-right">Omzet</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700/50">
                                        @foreach(array_slice($tiktokData['rankings'], 0, 10) as $sku => $data)
                                        <tr class="hover:bg-gray-800/50">
                                            <td class="px-4 py-2 font-mono text-pink-400">{{ $sku }}</td>
                                            <td class="px-4 py-2 text-gray-300 truncate max-w-[150px]" title="{{ $data['name'] }}">{{ $data['name'] }}</td>
                                            <td class="px-4 py-2 text-right font-bold text-white">{{ $data['qty'] }}</td>
                                            <td class="px-4 py-2 text-right text-emerald-400">Rp {{ number_format($data['omzet'], 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Other Stats --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 mb-2">Metode Pembayaran</h4>
                                <ul class="space-y-1">
                                    @foreach(array_slice($tiktokData['payment_methods'], 0, 5) as $method => $count)
                                    <li class="flex justify-between text-xs bg-gray-900 px-3 py-1.5 rounded-lg border border-gray-700"><span class="text-gray-300">{{ $method }}</span> <span class="font-bold text-pink-400">{{ $count }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-gray-400 mb-2">Dibatalkan Oleh</h4>
                                <ul class="space-y-1">
                                    @foreach(array_slice($tiktokData['cancel_by'], 0, 5) as $reason => $count)
                                    <li class="flex justify-between text-xs bg-gray-900 px-3 py-1.5 rounded-lg border border-gray-700"><span class="text-gray-300">{{ $reason ?: 'Sistem' }}</span> <span class="font-bold text-red-400">{{ $count }}</span></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Shopee Results --}}
            @if(isset($shopeeData))
                <div class="bg-gray-800 border border-gray-700/50 rounded-2xl overflow-hidden flex flex-col shadow-xl">
                    <div class="bg-orange-500/10 border-b border-orange-500/20 px-6 py-4 flex items-center gap-3">
                        <i data-lucide="shopping-bag" class="w-6 h-6 text-orange-500"></i>
                        <h2 class="text-xl font-bold text-orange-400">Analisis Shopee</h2>
                    </div>
                    <div class="p-6 space-y-6">
                        {{-- Key Metrics --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gray-900 border border-gray-700 rounded-xl p-4">
                                <div class="text-[10px] font-bold text-gray-500 uppercase mb-1">Total Pesanan Unik</div>
                                <div class="text-2xl font-bold text-white">{{ number_format($shopeeData['total_orders']) }}</div>
                            </div>
                            <div class="bg-gray-900 border border-gray-700 rounded-xl p-4">
                                <div class="text-[10px] font-bold text-gray-500 uppercase mb-1">Total Resi Unik</div>
                                <div class="text-2xl font-bold text-white">{{ number_format($shopeeData['total_resis']) }}</div>
                            </div>
                            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4">
                                <div class="text-[10px] font-bold text-emerald-500/70 uppercase mb-1">Total Omzet Bersih</div>
                                <div class="text-2xl font-bold text-emerald-400">Rp {{ number_format($shopeeData['omzet'], 0, ',', '.') }}</div>
                            </div>
                        </div>

                        {{-- Cancellations & Duplications --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 flex justify-between items-center">
                                <div>
                                    <div class="text-[10px] font-bold text-red-500/70 uppercase mb-1">Pesanan Dibatalkan</div>
                                    <div class="text-xl font-bold text-red-400">{{ number_format($shopeeData['batal_count']) }} Pesanan</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-[10px] font-bold text-red-500/70 uppercase mb-1">Potensi Omzet Hilang</div>
                                    <div class="text-xl font-bold text-red-400">Rp {{ number_format($shopeeData['batal_omzet'], 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 flex justify-between items-center">
                                <div>
                                    <div class="text-[10px] font-bold text-amber-500/70 uppercase mb-1">Duplikasi Order ID</div>
                                    <div class="text-xl font-bold text-amber-400">{{ number_format($shopeeData['duplicate_count']) }} ID</div>
                                </div>
                                <i data-lucide="copy" class="w-8 h-8 text-amber-500/30"></i>
                            </div>
                        </div>

                        {{-- Duplicate List (Conditional) --}}
                        @if($shopeeData['duplicate_count'] > 0)
                        <div x-data="{ show: false }">
                            <button @click="show = !show" class="text-[10px] text-amber-500 hover:text-amber-400 uppercase font-bold tracking-widest flex items-center gap-1 mb-2">
                                <i :class="show ? 'rotate-180' : ''" data-lucide="chevron-down" class="w-3 h-3 transition-transform"></i>
                                Lihat Daftar ID Terduplikasi
                            </button>
                            <div x-show="show" x-transition class="bg-gray-900 border border-gray-700 rounded-xl p-3 max-h-32 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-700">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($shopeeData['duplicate_orders'] as $id => $count)
                                        <span class="px-2 py-1 bg-gray-800 border border-gray-700 rounded text-[10px] text-gray-400 font-mono">
                                            {{ $id }} <span class="text-amber-500">({{ $count }}x)</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Status Breakdown --}}
                        <div>
                            <h3 class="text-sm font-bold text-white mb-3 uppercase tracking-wider flex items-center gap-2"><i data-lucide="layers" class="w-4 h-4 text-indigo-400"></i> Ringkasan Status Pesanan</h3>
                            <div class="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden">
                                <table class="w-full text-left text-xs whitespace-nowrap">
                                    <thead class="bg-gray-800 text-gray-400 uppercase">
                                        <tr>
                                            <th class="px-4 py-2">Status</th>
                                            <th class="px-4 py-2 text-right">Jumlah Pesanan</th>
                                            <th class="px-4 py-2 text-right">Qty Item</th>
                                            <th class="px-4 py-2 text-right">Total Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700/50">
                                        @foreach($shopeeData['status_breakdown'] as $status => $stats)
                                        <tr class="hover:bg-gray-800/50">
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                                    {{ in_array($status, ['Selesai', 'Completed']) ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 
                                                       (in_array($status, ['Batal', 'Dibatalkan', 'Cancelled']) ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20') }}">
                                                    {{ $status ?: 'Unknown' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-2 text-right text-gray-300">{{ number_format($stats['count']) }}</td>
                                            <td class="px-4 py-2 text-right text-gray-300">{{ number_format($stats['qty']) }}</td>
                                            <td class="px-4 py-2 text-right font-bold text-white">Rp {{ number_format($stats['omzet'], 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Ranking Produk --}}
                        <div>
                            <h3 class="text-sm font-bold text-white mb-3 uppercase tracking-wider flex items-center gap-2"><i data-lucide="award" class="w-4 h-4 text-yellow-500"></i> Top Produk (No. Referensi SKU)</h3>
                            <div class="bg-gray-900 border border-gray-700 rounded-xl overflow-hidden">
                                <table class="w-full text-left text-xs whitespace-nowrap">
                                    <thead class="bg-gray-800 text-gray-400 uppercase">
                                        <tr>
                                            <th class="px-4 py-2">Referensi SKU</th>
                                            <th class="px-4 py-2">Nama Produk</th>
                                            <th class="px-4 py-2 text-right">Terjual</th>
                                            <th class="px-4 py-2 text-right">Omzet</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-700/50">
                                        @foreach(array_slice($shopeeData['rankings'], 0, 10) as $sku => $data)
                                        <tr class="hover:bg-gray-800/50">
                                            <td class="px-4 py-2 font-mono text-orange-400">{{ $sku }}</td>
                                            <td class="px-4 py-2 text-gray-300 truncate max-w-[150px]" title="{{ $data['name'] }}">{{ $data['name'] }}</td>
                                            <td class="px-4 py-2 text-right font-bold text-white">{{ $data['qty'] }}</td>
                                            <td class="px-4 py-2 text-right text-emerald-400">Rp {{ number_format($data['omzet'], 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Other Stats --}}
                        <div>
                            <h4 class="text-xs font-bold text-gray-400 mb-2">Alasan Pembatalan Utama</h4>
                            <ul class="space-y-1">
                                @foreach(array_slice($shopeeData['cancel_reasons'], 0, 5) as $reason => $count)
                                <li class="flex justify-between text-xs bg-gray-900 px-3 py-1.5 rounded-lg border border-gray-700">
                                    <span class="text-gray-300 truncate max-w-[80%]" title="{{ $reason }}">{{ $reason ?: 'Tidak diketahui' }}</span> 
                                    <span class="font-bold text-red-400">{{ $count }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    @endif
</div>

<script>
    document.getElementById('tiktok_file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Pilih file ekspor TikTok (.xlsx, .csv)';
        document.getElementById('tiktok-file-name').textContent = fileName;
        document.getElementById('tiktok-file-name').classList.add('text-pink-400', 'font-medium');
    });

    document.getElementById('shopee_file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Pilih file ekspor Shopee (.xlsx, .csv)';
        document.getElementById('shopee-file-name').textContent = fileName;
        document.getElementById('shopee-file-name').classList.add('text-orange-400', 'font-medium');
    });
</script>
@endsection

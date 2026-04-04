@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-indigo-600/10 border border-indigo-500/20 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-white mb-1">Selamat Datang, {{ Auth::user()->name ?? 'Admin' }} 👋</h2>
            <p class="text-sm text-indigo-200">Berikut ringkasan bisnis hari ini — {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('digital-marketing.orders.index') }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i> Order Baru
            </a>
            <a href="{{ route('inventory.index') ?? '#' }}" class="flex items-center gap-2 px-4 py-2 bg-gray-800 border border-gray-700 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="package-plus" class="w-4 h-4"></i> Tambah Stok
            </a>
            <a href="{{ route('finance.index') ?? '#' }}" class="flex items-center gap-2 px-4 py-2 bg-gray-800 border border-gray-700 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors">
                <i data-lucide="receipt" class="w-4 h-4"></i> Buat Invoice
            </a>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- KPI Stats (Marketing) -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="banknote" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">Rp {{ number_format($metrics['revenue_today'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm mb-3">Revenue Hari Ini</div>
            <div class="flex items-center gap-1 text-xs font-medium {{ $metrics['revenue_growth'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                <i data-lucide="{{ $metrics['revenue_growth'] >= 0 ? 'trending-up' : 'trending-down' }}" class="w-4 h-4"></i> 
                {{ $metrics['revenue_growth'] > 0 ? '+' : '' }}{{ number_format($metrics['revenue_growth'], 1) }}% vs kemarin
            </div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center mb-4"><i data-lucide="shopping-bag" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($metrics['orders_today']) }}</div>
            <div class="text-gray-400 text-sm mb-3">Total Order Hari Ini</div>
            <div class="flex items-center gap-1 text-xs font-medium {{ $metrics['orders_growth'] >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                <i data-lucide="{{ $metrics['orders_growth'] >= 0 ? 'trending-up' : 'trending-down' }}" class="w-4 h-4"></i>
                {{ $metrics['orders_growth'] > 0 ? '+' : '' }}{{ number_format($metrics['orders_growth'], 1) }}% vs kemarin
            </div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4"><i data-lucide="clock" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($metrics['pending_orders']) }}</div>
            <div class="text-gray-400 text-sm mb-3">Order Pending (Butuh Tindakan)</div>
            @if($metrics['pending_orders'] > 0)
                <div class="flex items-center gap-1 text-xs font-medium text-amber-500"><i data-lucide="alert-circle" class="w-4 h-4"></i> Segera proses E-Commerce</div>
            @else
                <div class="flex items-center gap-1 text-xs font-medium text-emerald-400"><i data-lucide="check-circle" class="w-4 h-4"></i> Semua pesanan beres</div>
            @endif
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center mb-4"><i data-lucide="alert-triangle" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($metrics['low_stock_count']) }}</div>
            <div class="text-gray-400 text-sm mb-3">Alert Stok Rendah</div>
            @if($metrics['low_stock_count'] > 0)
                <div class="flex items-center gap-1 text-xs font-medium text-red-400"><i data-lucide="arrow-down" class="w-4 h-4"></i> {{ $metrics['low_stock_count'] }} item butuh restock</div>
            @else
                <div class="flex items-center gap-1 text-xs font-medium text-emerald-400"><i data-lucide="check-circle" class="w-4 h-4"></i> Stok aman</div>
            @endif
        </div>
    </div>

    <!-- ============================================= -->
    <!-- Charts Row + Notifications -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm lg:col-span-2 flex flex-col">
            <div class="p-5 border-b border-gray-700/50 flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 font-bold text-white">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-indigo-400"></i> <span id="chartTrendTitle">Trend Penjualan</span>
                        <span class="flex items-center gap-1 ml-2 px-2 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">
                            <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span></span>
                            <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">Live</span>
                        </span>
                    </div>
                    <select id="chartPlatformFilter" class="bg-gray-900 border border-gray-700 text-white text-xs rounded-lg focus:border-indigo-500 p-2">
                        <option value="all">Semua Channel</option>
                        <option value="pos">Kasir / POS</option>
                        <option value="ecommerce">Semua E-Commerce</option>
                        <option value="Tokopedia">Tokopedia</option>
                        <option value="Shopee">Shopee</option>
                        <option value="TikTok">TikTok Shop</option>
                        <option value="Lazada">Lazada</option>
                    </select>
                </div>
                {{-- Date Range Filter --}}
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center bg-gray-900 border border-gray-700/50 rounded-lg px-3 py-1.5 gap-2">
                        <i data-lucide="calendar-range" class="w-4 h-4 text-indigo-500 shrink-0"></i>
                        <input type="text" id="chartStartDate" value="{{ $chartData['start_date'] }}" readonly class="chart-datepicker bg-transparent text-white text-xs outline-none cursor-pointer focus:text-indigo-400 w-24" placeholder="Dari">
                        <span class="text-gray-500 text-xs font-bold">s/d</span>
                        <input type="text" id="chartEndDate" value="{{ $chartData['end_date'] }}" readonly class="chart-datepicker bg-transparent text-white text-xs outline-none cursor-pointer focus:text-indigo-400 w-24" placeholder="Sampai">
                    </div>
                    <button id="chartApplyFilter" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2 rounded-lg transition-all flex items-center gap-1.5 shadow-lg shadow-indigo-600/20 active:scale-95">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i> Terapkan
                    </button>
                    <button id="chartResetFilter" class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs font-medium px-3 py-2 rounded-lg transition-all">
                        7 Hari Terakhir
                    </button>
                </div>
            </div>
            <div class="p-5 flex-1 w-full relative min-h-[300px]">
                <canvas id="salesTrendChart"></canvas>
            </div>
        </div>
        
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-white"><i data-lucide="bell" class="w-5 h-5 text-indigo-400"></i> Notifikasi Terbaru</div>
            </div>
            <div class="p-2 overflow-y-auto max-h-[350px] space-y-1">
                @forelse($notifications as $notif)
                <div class="flex items-start gap-3 p-3 hover:bg-gray-700/50 rounded-lg cursor-pointer transition-colors relative">
                    <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 bg-{{ $notif['color'] }}-500"></div>
                    <div class="flex-1 min-w-0">
                        <strong class="block text-sm font-semibold text-white truncate">{{ $notif['title'] }}</strong>
                        <span class="block text-xs text-gray-400 mt-0.5 truncate">{{ $notif['message'] }}</span>
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-sm text-gray-500">Belum ada notifikasi</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- DIVISI PRODUKSI: Production Summary -->
    <!-- ============================================= -->
    <div class="space-y-4">
        <div class="flex items-center gap-2 px-1">
            <div class="w-8 h-8 bg-purple-500/10 text-purple-400 flex items-center justify-center rounded-lg"><i data-lucide="factory" class="w-4 h-4"></i></div>
            <h3 class="text-sm font-bold text-white uppercase tracking-wider">Divisi Produksi</h3>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-3">
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="package" class="w-4 h-4 text-indigo-400"></i><span class="text-[10px] font-bold text-gray-400 uppercase">Stok</span></div>
                <div class="text-2xl font-bold text-white">{{ number_format($productionSummary['total_stock']) }}</div>
                <div class="text-[10px] text-gray-500 mt-1">{{ $productionSummary['total_sku'] }} SKU</div>
            </div>
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="activity" class="w-4 h-4 text-emerald-400"></i><span class="text-[10px] font-bold text-gray-400 uppercase">Produksi Aktif</span></div>
                <div class="text-2xl font-bold text-white">{{ $productionSummary['active_productions'] }}</div>
                <div class="text-[10px] text-gray-500 mt-1">{{ $productionSummary['active_tailors'] }}/{{ $productionSummary['total_tailors'] }} penjahit aktif</div>
            </div>
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="shopping-bag" class="w-4 h-4 text-blue-400"></i><span class="text-[10px] font-bold text-gray-400 uppercase">PO Pending</span></div>
                <div class="text-2xl font-bold text-white">{{ $productionSummary['active_pos'] }}</div>
                <div class="text-[10px] text-gray-500 mt-1">{{ $productionSummary['active_transfers'] }} transfer aktif</div>
            </div>
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
                <div class="flex items-center gap-2 mb-2"><i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i><span class="text-[10px] font-bold text-gray-400 uppercase">Stok Kritis</span></div>
                <div class="text-2xl font-bold {{ $productionSummary['low_stock'] > 0 ? 'text-amber-500' : 'text-white' }}">{{ $productionSummary['low_stock'] }}</div>
                <div class="text-[10px] text-gray-500 mt-1">produk di bawah safety stock</div>
            </div>
        </div>

        {{-- Production Widgets --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Active Productions --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
                <div class="p-4 border-b border-gray-700/50 flex items-center justify-between">
                    <h4 class="font-bold text-white flex items-center gap-2 text-sm"><i data-lucide="factory" class="w-4 h-4 text-emerald-400"></i> Produksi Berjalan</h4>
                    <a href="{{ route('production.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Lihat →</a>
                </div>
                <div class="p-0 flex-1 relative min-h-[200px]">
                    <div class="absolute inset-0 overflow-y-auto w-full">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Produk</th>
                                    <th class="px-4 py-2.5 font-medium">Qty</th>
                                    <th class="px-4 py-2.5 font-medium">Penjahit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                @forelse($activeProductions as $prod)
                                <tr class="hover:bg-gray-700/20 transition-colors">
                                    <td class="px-4 py-2.5 text-gray-300 text-xs">{{ $prod->product->name }}</td>
                                    <td class="px-4 py-2.5"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400">{{ $prod->quantity }}</span></td>
                                    <td class="px-4 py-2.5 text-xs text-gray-400">{{ $prod->tailor ? $prod->tailor->name : 'Internal' }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500 text-xs">Tidak ada produksi aktif</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Critical Stock --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
                <div class="p-4 border-b border-gray-700/50 flex items-center justify-between">
                    <h4 class="font-bold text-white flex items-center gap-2 text-sm"><i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i> Stok Kritis</h4>
                    <a href="{{ route('inventory.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Inventori →</a>
                </div>
                <div class="p-0 flex-1 relative min-h-[200px]">
                    <div class="absolute inset-0 overflow-y-auto w-full">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Produk</th>
                                    <th class="px-4 py-2.5 font-medium">Stok</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                @forelse($criticalStockProducts as $p)
                                <tr class="hover:bg-gray-700/20 transition-colors">
                                    <td class="px-4 py-2.5 text-gray-300 text-xs">{{ $p->name }}</td>
                                    <td class="px-4 py-2.5"><span class="font-bold {{ $p->stock == 0 ? 'text-red-500' : 'text-amber-500' }}">{{ $p->stock }}</span> <span class="text-gray-500 text-xs">/ {{ $p->safety_stock }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500 text-xs">Stok aman ✓</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Stock Transfers --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
                <div class="p-4 border-b border-gray-700/50 flex items-center justify-between">
                    <h4 class="font-bold text-white flex items-center gap-2 text-sm"><i data-lucide="arrow-right-left" class="w-4 h-4 text-indigo-400"></i> Transfer Stok</h4>
                    <a href="{{ route('production.transfers.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Lihat →</a>
                </div>
                <div class="p-0 flex-1 relative min-h-[200px]">
                    <div class="absolute inset-0 overflow-y-auto w-full">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Alur</th>
                                    <th class="px-4 py-2.5 font-medium text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                @forelse($recentTransfers as $trf)
                                <tr class="hover:bg-gray-700/20 transition-colors">
                                    <td class="px-4 py-2.5">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="text-xs text-gray-300">{{ $trf->sourceBranch->name }}</span>
                                            <i data-lucide="arrow-down" class="w-3 h-3 text-gray-600"></i>
                                            <span class="text-xs text-indigo-300">{{ $trf->destinationBranch->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        @php
                                            $trColors = [
                                                'pending' => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                                'sending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                                'received' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold border {{ $trColors[$trf->status] ?? '' }}">{{ ucfirst($trf->status) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500 text-xs">Tidak ada transfer</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- DIVISI KEUANGAN, HRM, PRODUK TERLARIS -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Keuangan -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-4 border-b border-gray-700/50 flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-white text-sm">
                    <i data-lucide="wallet" class="w-4 h-4 text-emerald-400"></i> Keuangan (Bulan Ini)
                </div>
                <a href="{{ route('finance.dashboard') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Detail →</a>
            </div>
            <div class="p-5 flex-1 flex flex-col gap-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Kas Masuk</span>
                    <span class="font-semibold text-emerald-400">Rp {{ number_format($financeSummary['income_this_month'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Kas Keluar</span>
                    <span class="font-semibold text-red-400">Rp {{ number_format($financeSummary['expense_this_month'], 0, ',', '.') }}</span>
                </div>
                <div class="pt-4 mt-auto border-t border-gray-700/50 flex justify-between items-center">
                    <span class="text-sm text-gray-400">Saldo Kas</span>
                    <span class="font-bold text-white text-lg">Rp {{ number_format($financeSummary['total_balance'], 0, ',', '.') }}</span>
                </div>
            </div>
            {{-- Recent Cash Transactions --}}
            @if(isset($recentCashTransactions) && $recentCashTransactions->count() > 0)
            <div class="border-t border-gray-700/50">
                <div class="p-3 space-y-1">
                    @foreach($recentCashTransactions->take(3) as $trx)
                    <div class="flex items-center justify-between py-1.5 px-2 hover:bg-gray-700/30 rounded text-xs">
                        <div class="flex items-center gap-2">
                            @if($trx->type === 'in')
                            <div class="w-5 h-5 rounded bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0"><i data-lucide="arrow-down-left" class="w-3 h-3"></i></div>
                            @else
                            <div class="w-5 h-5 rounded bg-red-500/10 text-red-500 flex items-center justify-center shrink-0"><i data-lucide="arrow-up-right" class="w-3 h-3"></i></div>
                            @endif
                            <span class="text-gray-300 truncate max-w-[100px]">{{ $trx->category }}</span>
                        </div>
                        <span class="font-bold {{ $trx->type === 'in' ? 'text-emerald-400' : 'text-red-400' }}">{{ $trx->type === 'in' ? '+' : '-' }}{{ number_format($trx->amount/1000, 0) }}K</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Kehadiran Karyawan -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-4 border-b border-gray-700/50 flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-white text-sm">
                    <i data-lucide="users" class="w-4 h-4 text-blue-400"></i> Kehadiran Karyawan
                </div>
                <a href="{{ route('hrm.dashboard') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Detail →</a>
            </div>
            <div class="p-5 flex-1 flex flex-col justify-center">
                <div class="flex items-center justify-center gap-6 mb-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ $attendanceSummary['present'] }}<span class="text-sm text-gray-500 font-normal">/{{ $attendanceSummary['total'] }}</span></div>
                        <div class="text-xs text-gray-400 mt-1">Hadir Hari Ini</div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center mb-4">
                    <div class="bg-amber-500/10 rounded p-2">
                        <div class="text-amber-500 font-bold text-lg">{{ $attendanceSummary['sick'] }}</div>
                        <div class="text-[10px] text-gray-400 uppercase">Sakit</div>
                    </div>
                    <div class="bg-blue-500/10 rounded p-2">
                        <div class="text-blue-500 font-bold text-lg">{{ $attendanceSummary['leave'] }}</div>
                        <div class="text-[10px] text-gray-400 uppercase">Izin</div>
                    </div>
                    <div class="bg-red-500/10 rounded p-2">
                        <div class="text-red-500 font-bold text-lg">{{ $attendanceSummary['absent'] }}</div>
                        <div class="text-[10px] text-gray-400 uppercase">Alpa</div>
                    </div>
                </div>
                {{-- Recent attendance mini-list --}}
                @if(isset($recentAttendances) && $recentAttendances->count() > 0)
                <div class="border-t border-gray-700/50 pt-3 space-y-1">
                    @foreach($recentAttendances->take(3) as $att)
                    <div class="flex items-center justify-between py-1 px-2 text-xs">
                        <span class="text-white font-medium truncate max-w-[120px]">{{ $att->employee->name }}</span>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-400 font-mono">{{ $att->clock_in ? substr($att->clock_in, 0, 5) : '-' }}</span>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold {{ $att->status === 'present' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-500' }}">{{ ucfirst($att->status) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Top Produk -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-4 border-b border-gray-700/50 flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-white text-sm">
                    <i data-lucide="award" class="w-4 h-4 text-amber-500"></i> Top Produk Terlaris
                </div>
                <a href="{{ route('marketing.dashboard') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Detail →</a>
            </div>
            <div class="p-2 overflow-y-auto max-h-[300px] space-y-1">
                @forelse($topProducts as $index => $prod)
                <div class="flex items-center gap-3 p-2 hover:bg-gray-700/50 rounded-lg transition-colors">
                    <div class="w-6 h-6 rounded-full shrink-0 flex items-center justify-center font-bold text-xs {{ $index === 0 ? 'bg-amber-500 text-white' : ($index === 1 ? 'bg-gray-300 text-gray-800' : ($index === 2 ? 'bg-orange-400 text-white' : 'bg-gray-700 text-gray-400')) }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <strong class="block text-sm font-semibold text-white truncate">{{ $prod->name }}</strong>
                        <span class="block text-xs text-gray-400 mt-0.5 truncate">{{ $prod->category ?? $prod->category_name ?? '-' }}</span>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-sm font-bold text-emerald-400 text-right">Rp {{ number_format($prod->price, 0, ',', '.') }}</div>
                        @if(isset($prod->total_sold))
                            <div class="text-xs text-gray-500">{{ $prod->total_sold }} terjual</div>
                        @else
                            <div class="text-xs text-gray-500">Stok: {{ $prod->stock }}</div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-sm text-gray-500">Belum ada data penjualan produk</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ============================================= -->
    <!-- Logistics & Performance Combined -->
    <!-- ============================================= -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        @if(auth()->user()->isExecutive())
        <!-- Branch Revenue (Omzet per Cabang) -->
        <div class="lg:col-span-1 bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-white">
                    <i data-lucide="award" class="w-5 h-5 text-amber-500"></i> Ranking Omzet Cabang
                </div>
                <div class="text-[10px] uppercase font-bold text-gray-500 tracking-wider">Bulan Ini</div>
            </div>
            <div class="p-2 flex-1">
                <div class="w-full overflow-hidden rounded-lg border border-gray-700/50">
                    <table class="w-full text-left text-[10px] uppercase tracking-tight">
                        <thead class="bg-gray-900/50 text-gray-500 font-bold border-b border-gray-700/50">
                            <tr>
                                <th class="px-3 py-3 w-10 text-center">Rank</th>
                                <th class="px-3 py-3">Nama Cabang</th>
                                <th class="px-3 py-3 text-right">Growth</th>
                                <th class="px-3 py-3 text-right">Omzet</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($branchRevenue as $index => $branch)
                            <tr class="hover:bg-gray-700/30 transition-colors">
                                <td class="px-3 py-4 text-center">
                                    @if($index === 0)
                                        <i data-lucide="trophy" class="w-4 h-4 text-amber-500 mx-auto"></i>
                                    @elseif($index === 1)
                                        <i data-lucide="medal" class="w-4 h-4 text-gray-300 mx-auto"></i>
                                    @elseif($index === 2)
                                        <i data-lucide="medal" class="w-4 h-4 text-orange-400 mx-auto"></i>
                                    @else
                                        <span class="text-gray-500 font-bold">#{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4">
                                    <span class="font-bold text-white block truncate max-w-[100px]">{{ $branch->name }}</span>
                                    <span class="text-[9px] text-gray-500">{{ $branch->total_transactions }} Transaksi</span>
                                </td>
                                <td class="px-3 py-4 text-right">
                                    @if($branch->growth > 0)
                                        <span class="text-emerald-400 font-bold">+{{ number_format($branch->growth, 1) }}%</span>
                                    @elseif($branch->growth < 0)
                                        <span class="text-red-400 font-bold">{{ number_format($branch->growth, 1) }}%</span>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-right font-bold text-emerald-400">
                                    <span class="block">Rp {{ number_format($branch->total_revenue ?? 0, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-3 py-8 text-center text-gray-500 lowercase italic">Belum ada transaksi hari ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($branchRevenue->count() > 0)
            <div class="p-4 bg-gray-900/20 border-t border-gray-700/50">
                <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    <span>Total Omzet POS</span>
                    <span class="text-white">Rp {{ number_format($branchRevenue->sum('total_revenue'), 0, ',', '.') }}</span>
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Recent Orders (Marketing) -->
        <div class="{{ auth()->user()->isExecutive() ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-white"><i data-lucide="list-ordered" class="w-5 h-5 text-indigo-400"></i> Order Terbaru</div>
                <a href="{{ route('digital-marketing.orders.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">Lihat Semua &rarr;</a>
            </div>
            <div class="w-full overflow-x-auto flex-1">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-4 font-medium">Order ID</th>
                            <th class="px-5 py-4 font-medium">Channel</th>
                            <th class="px-5 py-4 font-medium">Customer</th>
                            <th class="px-5 py-4 font-medium">Total</th>
                            <th class="px-5 py-4 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-700/20 transition-colors">
                            <td class="px-5 py-4">
                                <span class="font-bold text-white block">{{ $order['id'] }}</span>
                                <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order['date'])->format('d M Y H:i') }}</span>
                            </td>
                            <td class="px-5 py-4 text-gray-400 text-xs font-medium">{{ $order['channel'] }}</td>
                            <td class="px-5 py-4 text-white font-medium">{{ $order['customer'] }}</td>
                            <td class="px-5 py-4 font-bold text-emerald-400">Rp {{ number_format($order['total'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                @if(in_array($order['status'], ['pending', 'new']))
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20 uppercase">Pending</span>
                                @elseif(in_array($order['status'], ['ready_to_ship', 'shipped', 'processing']))
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase">Proses</span>
                                @elseif(in_array($order['status'], ['completed', 'delivered']))
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Selesai</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-red-500/10 text-red-400 border border-red-500/20 uppercase">{{ $order['status'] }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">Belum ada data transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<style>
    .flatpickr-calendar {
        background: #1f2937 !important;
        border: 1px solid #374151 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
    }
    .flatpickr-day.selected {
        background: #4f46e5 !important;
        border-color: #4f46e5 !important;
    }
    .flatpickr-day:hover {
        background: #374151 !important;
    }
    .flatpickr-months .flatpickr-month, .flatpickr-current-month .flatpickr-monthDropdown-months {
        background: #1f2937 !important;
        color: white !important;
    }
    .flatpickr-weekday {
        color: #9ca3af !important;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Flatpickr
        flatpickr(".chart-datepicker", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            disableMobile: true,
            maxDate: "today"
        });

        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        const initialData = @json($chartData);

        // Color palette for multi-dataset
        const COLORS = [
            { border: '#6366f1', bg: 'rgba(99, 102, 241, 0.15)' },  // indigo (POS)
            { border: '#10b981', bg: 'rgba(16, 185, 129, 0.15)' },  // emerald (E-Commerce)
            { border: '#f59e0b', bg: 'rgba(245, 158, 11, 0.15)' },  // amber (Tokopedia)
            { border: '#ec4899', bg: 'rgba(236, 72, 153, 0.15)' },  // pink (Shopee)
            { border: '#8b5cf6', bg: 'rgba(139, 92, 246, 0.15)' },  // violet (TikTok)
            { border: '#06b6d4', bg: 'rgba(6, 182, 212, 0.15)' },   // cyan (Lazada)
            { border: '#ef4444', bg: 'rgba(239, 68, 68, 0.15)' },   // red
        ];

        function buildDatasets(apiData) {
            const datasets = apiData.datasets || [];
            const isMulti = apiData.multi || false;

            return datasets.map((ds, i) => {
                const color = COLORS[i % COLORS.length];
                
                // Only fill gradient for single dataset
                let bgColor = 'transparent';
                let fill = false;
                if (!isMulti && datasets.length === 1) {
                    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                    gradient.addColorStop(0, color.bg);
                    gradient.addColorStop(1, 'rgba(0,0,0,0)');
                    bgColor = gradient;
                    fill = true;
                } else {
                    // For multi-line, use subtle background
                    bgColor = color.bg;
                    fill = true;
                }

                return {
                    label: ds.label,
                    data: ds.data,
                    borderColor: color.border,
                    backgroundColor: bgColor,
                    borderWidth: isMulti ? 2 : 2.5,
                    tension: 0.4,
                    fill: fill,
                    pointBackgroundColor: color.border,
                    pointBorderColor: '#1f2937',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: color.border,
                    pointRadius: isMulti ? 3 : 4,
                    pointHoverRadius: 6,
                };
            });
        }

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: initialData.labels,
                datasets: buildDatasets(initialData)
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: initialData.multi || false,
                        position: 'top',
                        labels: {
                            color: '#9ca3af',
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 16,
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.95)',
                        titleColor: '#f3f4f6',
                        bodyColor: '#d1d5db',
                        borderColor: '#4f46e5',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true,
                        boxWidth: 8,
                        callbacks: {
                            title: function(context) {
                                // Access current chart data state
                                const isHourly = salesChart.data.isHourly || false;
                                if (isHourly) return 'Pukul ' + context[0].label;
                                return context[0].label;
                            },
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: 'rgba(55, 65, 81, 0.3)', drawBorder: false },
                        ticks: { 
                            color: '#9ca3af', 
                            font: { size: 11 },
                            maxTicksLimit: 14,
                            maxRotation: 45
                        }
                    },
                    y: {
                        grid: { color: 'rgba(55, 65, 81, 0.3)', drawBorder: false },
                        ticks: { 
                            color: '#9ca3af', 
                            font: { size: 11 },
                            callback: function(value) {
                                if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                return 'Rp ' + value;
                            }
                        },
                        beginAtZero: true
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
            }
        });

        // Store hourly state in chart instance for tooltip callback
        salesChart.data.isHourly = initialData.isHourly || false;

        // Rebuild chart with new data
        function updateChart(apiData) {
            salesChart.data.labels = apiData.labels;
            salesChart.data.datasets = buildDatasets(apiData);
            salesChart.data.isHourly = apiData.isHourly || false;
            salesChart.options.plugins.legend.display = apiData.multi || false;
            
            // Update Title
            const titleEl = document.getElementById('chartTrendTitle');
            if (titleEl) {
                titleEl.textContent = apiData.isHourly ? 'Penjualan Per Jam' : 'Trend Penjualan';
            }

            salesChart.update();
        }

        // Fetch chart data with current filters
        function fetchChartData() {
            const platform = document.getElementById('chartPlatformFilter').value;
            const startDate = document.getElementById('chartStartDate').value;
            const endDate = document.getElementById('chartEndDate').value;

            let url = `{{ route('dashboard.chart-data') }}?platform=${platform}`;
            if (startDate) url += `&start_date=${startDate}`;
            if (endDate) url += `&end_date=${endDate}`;

            fetch(url)
                .then(response => response.json())
                .then(data => updateChart(data))
                .catch(err => console.error('Error fetching chart data:', err));
        }

        // Platform filter
        document.getElementById('chartPlatformFilter').addEventListener('change', fetchChartData);

        // Apply date filter
        document.getElementById('chartApplyFilter').addEventListener('click', fetchChartData);

        // Reset to 7 days
        document.getElementById('chartResetFilter').addEventListener('click', function() {
            const today = new Date();
            const weekAgo = new Date(today);
            weekAgo.setDate(today.getDate() - 6);

            const fmt = d => d.toISOString().split('T')[0];
            
            const startEl = document.getElementById('chartStartDate');
            const endEl = document.getElementById('chartEndDate');
            startEl._flatpickr.setDate(fmt(weekAgo));
            endEl._flatpickr.setDate(fmt(today));

            fetchChartData();
        });

        // ========== REALTIME: Auto-refresh every 30 seconds ==========
        let refreshInterval = setInterval(fetchChartData, 30000);

        // Pause polling when tab is hidden, resume when visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(refreshInterval);
            } else {
                fetchChartData(); // immediate refresh on tab focus
                refreshInterval = setInterval(fetchChartData, 30000);
            }
        });
    });
</script>
@endsection

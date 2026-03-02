@extends('layouts.app')

@section('title', 'Overview Marketing')
@section('breadcrumb')
<span class="text-gray-200">Divisi Marketing</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Overview Marketing</span>
@endsection

@section('content')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
<style>
    .flatpickr-calendar {
        background: #1f2937 !important; /* gray-800 */
        border: 1px solid #374151 !important; /* gray-700 */
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
    }
    .flatpickr-day.selected {
        background: #4f46e5 !important; /* indigo-600 */
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
        color: #9ca3af !important; /* gray-400 */
    }
</style>
@endpush
<div>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
                <i data-lucide="line-chart" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white m-0">Overview Marketing</h3>
                <p class="text-xs text-gray-400 mt-0.5">Rangkuman performa penjualan, pelanggan, dan promosi.</p>
            </div>
        </div>
        
        <form method="GET" action="{{ route('marketing.dashboard') }}" class="flex flex-wrap items-center gap-3 bg-gray-800 border border-gray-700 p-2.5 rounded-xl shadow-lg">
            <div class="flex items-center gap-3 mr-2">
                <div class="flex flex-col">
                    <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest px-1">Filter Periode</span>
                    <div class="flex items-center bg-gray-950 border border-gray-700/50 rounded-lg px-3 py-1.5 mt-1">
                        <i data-lucide="calendar-range" class="w-4 h-4 text-indigo-500 mr-2"></i>
                        <input type="text" id="start_date" name="start_date" value="{{ $startDate }}" readonly class="datepicker bg-transparent text-white text-xs outline-none cursor-pointer focus:text-indigo-400 w-20">
                        <span class="mx-3 text-gray-500 font-bold">s/d</span>
                        <input type="text" id="end_date" name="end_date" value="{{ $endDate }}" readonly class="datepicker bg-transparent text-white text-xs outline-none cursor-pointer focus:text-indigo-400 w-20">
                    </div>
                </div>
            </div>

            <div class="flex flex-col">
                <span class="text-[9px] font-black text-indigo-400 uppercase tracking-widest px-1">Pilih Cabang</span>
                <div class="relative w-48 mt-1">
                    <select name="branch_id" class="w-full bg-gray-950 border border-gray-700/50 text-white rounded-lg pl-3 pr-10 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none cursor-pointer shadow-inner">
                        <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>Semua Cabang (POS)</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                </div>
            </div>

            <div class="flex items-end h-full self-end mb-0.5">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2 rounded-lg transition-all flex items-center gap-2 shadow-lg shadow-indigo-600/20 active:scale-95">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="banknote" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">Rp {{ number_format($totalRevenueMonth, 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Pendapatan Bulan Ini</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="shopping-cart" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($totalSalesMonth) }}</div>
            <div class="text-gray-400 text-sm">Transaksi Bulan Ini</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center mb-4"><i data-lucide="users" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($activeCustomers) }}</div>
            <div class="text-gray-400 text-sm">Total Pelanggan Terdaftar</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4"><i data-lucide="ticket" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($activeRedeemCodes) }}</div>
            <div class="text-gray-400 text-sm">Kupon/Redeem Aktif</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Trend Chart --}}
        <div class="lg:col-span-3 bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-4 h-4 text-indigo-400"></i> Trend Penjualan POS (30 Hari)
                </h4>
                @if($branchId === 'all')
                    <span class="text-[10px] font-bold text-gray-400 bg-gray-900 px-2 py-0.5 rounded border border-gray-700">Performa Antar Cabang</span>
                @else
                    <span class="text-[10px] font-bold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20">Revenue vs Transaksi</span>
                @endif
            </div>
            <div class="h-64 w-full">
                <canvas id="marketingTrendChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-6">
        {{-- Transaksi Terbaru (Unified POS & E-Commerce) --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full lg:col-span-3">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-emerald-400"></i> Transaksi Terbaru
                </h4>
                <div class="flex gap-3">
                    <a href="{{ route('pos.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Buka POS →</a>
                </div>
            </div>
            <div class="p-0 flex-1 relative min-h-[250px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">No. Invoice / Cabang</th>
                                <th class="px-5 py-3 font-medium">Pelanggan</th>
                                <th class="px-5 py-3 font-medium">Waktu</th>
                                <th class="px-5 py-3 font-medium text-right">Total Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($recentTransactions as $sale)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-3">
                                    <div class="flex flex-col">
                                        <a href="{{ $sale->route }}" target="_blank" class="font-mono font-bold text-indigo-400 text-xs hover:text-indigo-300 transition-colors">{{ $sale->reference }}</a>
                                        <span class="text-[10px] text-gray-500 font-medium uppercase mt-0.5 flex items-center gap-1">
                                            <i data-lucide="store" class="w-3 h-3 text-gray-600"></i>
                                            {{ $sale->platform }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 text-gray-300">{{ $sale->customer_name }}</td>
                                <td class="px-5 py-3 text-gray-400">{{ \Carbon\Carbon::parse($sale->date)->format('d M Y H:i') }}</td>
                                <td class="px-5 py-3 font-bold text-emerald-400 text-right">
                                    <div class="flex flex-col items-end">
                                        <span>Rp {{ number_format($sale->amount, 0, ',', '.') }}</span>
                                        @if($sale->is_redeem)
                                            <span class="mt-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-gray-900 border border-amber-400 flex items-center gap-1">
                                                <i data-lucide="ticket" class="w-3 h-3"></i> Kupon Used
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                                    <i data-lucide="file-x" class="w-8 h-8 mx-auto mb-2 text-gray-600"></i>
                                    Belum ada data transaksi.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Produk Terlaris --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="award" class="w-4 h-4 text-amber-400"></i> Produk Terlaris
                </h4>
            </div>
            <div class="p-0 flex-1 relative min-h-[250px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">Produk</th>
                                <th class="px-5 py-3 font-medium text-right">Terjual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($topProducts as $item)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-3 font-bold text-white">{{ collect(explode(' ', $item->product->name))->take(2)->implode(' ') }}...</td>
                                <td class="px-5 py-3 text-right">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-500">{{ number_format($item->total_sold) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="px-5 py-8 text-center text-gray-500">
                                    <i data-lucide="package-x" class="w-8 h-8 mx-auto mb-2 text-gray-600"></i>
                                    Belum ada data penjualan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pelanggan Baru --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-blue-400"></i> Pelanggan Baru
                </h4>
                <a href="{{ route('sales.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Sales & CRM →</a>
            </div>
            <div class="p-0 flex-1 relative min-h-[250px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">Pelanggan</th>
                                <th class="px-5 py-3 font-medium">Kontak</th>
                                <th class="px-5 py-3 font-medium">Tipe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($newCustomers as $cust)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-3 font-bold text-white">{{ $cust->name }}</td>
                                <td class="px-5 py-3 text-gray-300">{{ $cust->phone ?? $cust->email ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-500/10 text-gray-400">{{ ucfirst($cust->type) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-gray-500">
                                    <i data-lucide="user-x" class="w-8 h-8 mx-auto mb-2 text-gray-600"></i>
                                    Belum ada data pelanggan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Performa Kupon --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="ticket" class="w-4 h-4 text-amber-500"></i> Performa Redeem Code
                </h4>
                <a href="{{ route('redeem-codes.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Redeem Codes →</a>
            </div>
            <div class="p-0 flex-1 relative min-h-[250px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">Kode Kupon</th>
                                <th class="px-5 py-3 font-medium">Diskon</th>
                                <th class="px-5 py-3 font-medium text-right">Digunakan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($activeCoupons as $coupon)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-3 font-bold text-amber-400">{{ $coupon->code }}</td>
                                <td class="px-5 py-3 text-gray-300">
                                    {{ $coupon->type === 'percentage' ? $coupon->amount . '%' : 'Rp ' . number_format($coupon->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span class="font-bold text-white">{{ $coupon->sales_count }}x</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-gray-500">
                                    <i data-lucide="ticket" class="w-8 h-8 mx-auto mb-2 text-gray-600"></i>
                                    Belum ada data kupon aktif.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Datepickers
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d M Y",
            disableMobile: "true",
            onClose: function(selectedDates, dateStr, instance) {
                // If you want auto-submit on selection, uncomment below:
                // instance.element.closest('form').submit();
            }
        });

        const chartData = @json($chartData);
        const ctx = document.getElementById('marketingTrendChart').getContext('2d');
        
        const colors = [
            '#10b981', // emerald
            '#6366f1', // indigo
            '#f59e0b', // amber
            '#ec4899', // pink
            '#8b5cf6', // violet
            '#06b6d4', // cyan
        ];

        const datasets = chartData.datasets.map((ds, i) => {
            let color = colors[i % colors.length];
            
            // Override colors for consistency
            if (ds.label === 'Total Penjualan' || ds.label === 'Pendapatan') color = '#10b981';
            if (ds.label === 'Jumlah Transaksi') color = '#6366f1';

            // Special styling for primary line
            let fill = false;
            let backgroundColor = 'transparent';
            if (!chartData.isComparison && i === 0) {
                fill = true;
                const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, hexToRgba(color, 0.2));
                gradient.addColorStop(1, hexToRgba(color, 0));
                backgroundColor = gradient;
            }

            return {
                label: ds.label,
                data: ds.data,
                borderColor: color,
                backgroundColor: backgroundColor,
                borderWidth: 2,
                fill: fill,
                tension: 0.4,
                yAxisID: chartData.isComparison ? 'y' : (i === 0 ? 'y' : 'y1'),
                borderDash: !chartData.isComparison && i === 1 ? [5, 5] : []
            };
        });

        function hexToRgba(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: '#9ca3af',
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#e5e7eb',
                        borderColor: 'rgba(75, 85, 99, 0.4)',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                
                                const isRevenue = label.toLowerCase().includes('total') || 
                                                label.toLowerCase().includes('pendapatan') || 
                                                context.datasetIndex === 0 ||
                                                chartData.isComparison;

                                if (isRevenue && !label.toLowerCase().includes('transaksi')) {
                                    label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                } else {
                                    label += new Intl.NumberFormat('id-ID').format(context.raw);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#6b7280', maxTicksLimit: 10 }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: 'rgba(75, 85, 99, 0.1)', drawBorder: false },
                        ticks: {
                            color: '#6b7280',
                            callback: function(value) {
                                if(value >= 1000000) return 'Rp ' + (value/1000000).toFixed(1) + 'M';
                                if(value >= 1000) return 'Rp ' + (value/1000).toFixed(0) + 'K';
                                return 'Rp ' + value;
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: !chartData.isComparison,
                        position: 'right',
                        grid: { display: false },
                        ticks: { color: '#6366f1', stepSize: 1 }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection

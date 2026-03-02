@extends('layouts.app')

@section('title', 'Overview Digital Marketing')
@section('breadcrumb', 'Overview Digital Marketing')

@section('content')
<div>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center shrink-0">
                <i data-lucide="monitor-smartphone" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white m-0">Overview Digital Marketing</h3>
                <p class="text-xs text-gray-400 mt-0.5">Rangkuman performa penjualan online / e-commerce.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('digital-marketing.dashboard') }}" class="flex items-center gap-2">
            <div class="relative w-48 shrink-0">
                <select name="platform" onchange="this.form.submit()" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-3 pr-10 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none cursor-pointer shadow-inner">
                    <option value="all" {{ $selectedPlatform === 'all' ? 'selected' : '' }}>Semua Platform (Online)</option>
                    @foreach($platforms as $platform)
                        <option value="{{ $platform }}" {{ $selectedPlatform === $platform ? 'selected' : '' }}>{{ $platform }}</option>
                    @endforeach
                </select>
                <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500">
                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                </div>
            </div>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-4 mb-6">
        {{-- Gross Revenue --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                    <i data-lucide="dollar-sign" class="w-5 h-5"></i>
                </div>
                <div class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    Gross
                </div>
            </div>
            <h4 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Penjualan (Kotor)</h4>
            <div class="text-2xl font-black text-white truncate group-hover:scale-105 transition-transform origin-left" title="Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }}">
                Rp {{ number_format($totalGrossRevenue, 0, ',', '.') }}
            </div>
        </div>

        {{-- Fees --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-orange-500/10 rounded-full blur-xl group-hover:bg-orange-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-orange-500/10 text-orange-400 flex items-center justify-center border border-orange-500/20">
                    <i data-lucide="scissors" class="w-5 h-5"></i>
                </div>
            </div>
            <h4 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Fee Platform</h4>
            <div class="text-2xl font-black text-white truncate group-hover:scale-105 transition-transform origin-left" title="Rp {{ number_format($totalFees, 0, ',', '.') }}">
                Rp {{ number_format($totalFees, 0, ',', '.') }}
            </div>
        </div>

        {{-- Revenue after Fees --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
                    <i data-lucide="landmark" class="w-5 h-5"></i>
                </div>
            </div>
            <h4 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Pemasukan (Net Fee)</h4>
            <div class="text-2xl font-black text-white truncate group-hover:scale-105 transition-transform origin-left" title="Rp {{ number_format($totalRevenue, 0, ',', '.') }}">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
        </div>

        {{-- Orders --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center border border-blue-500/20">
                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                </div>
            </div>
            <h4 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Pesanan E-Commerce</h4>
            <div class="text-2xl font-black text-white group-hover:scale-105 transition-transform origin-left">
                {{ number_format($totalOrders, 0, ',', '.') }}
            </div>
        </div>

        {{-- Biaya Iklan --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-red-500/10 rounded-full blur-xl group-hover:bg-red-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center border border-red-500/20">
                    <i data-lucide="trending-down" class="w-5 h-5"></i>
                </div>
            </div>
            <h4 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Total Biaya Iklan</h4>
            <div class="text-2xl font-black text-white truncate group-hover:scale-105 transition-transform origin-left" title="Rp {{ number_format($totalExpense, 0, ',', '.') }}">
                Rp {{ number_format($totalExpense, 0, ',', '.') }}
            </div>
        </div>

        {{-- ROAS --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-500/10 rounded-full blur-xl group-hover:bg-indigo-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20">
                    <i data-lucide="percent" class="w-5 h-5"></i>
                </div>
                <div class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                    ROAS
                </div>
            </div>
            <h4 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Return on Ad Spend</h4>
            <div class="text-2xl font-black text-white truncate group-hover:scale-105 transition-transform origin-left">
                {{ number_format($roas, 2, ',', '.') }}x
            </div>
        </div>

        {{-- AOV --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-purple-500/10 rounded-full blur-xl group-hover:bg-purple-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center border border-purple-500/20">
                    <i data-lucide="calculator" class="w-5 h-5"></i>
                </div>
            </div>
            <h4 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Avg Order Value</h4>
            <div class="text-2xl font-black text-white truncate group-hover:scale-105 transition-transform origin-left">
                Rp {{ number_format($aov, 0, ',', '.') }}
            </div>
        </div>

        {{-- New Customers --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/10 rounded-full blur-xl group-hover:bg-amber-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center border border-amber-500/20">
                    <i data-lucide="users" class="w-5 h-5"></i>
                </div>
            </div>
            <h4 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Customer Online Baru</h4>
            <div class="text-2xl font-black text-white group-hover:scale-105 transition-transform origin-left">
                {{ number_format($newCustomers, 0, ',', '.') }}
            </div>
        </div>

        {{-- Net Profit --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm relative overflow-hidden group ring-2 ring-emerald-500/20">
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/10 rounded-full blur-xl group-hover:bg-emerald-500/20 transition-all duration-500"></div>
            <div class="flex justify-between items-start mb-4 relative z-10">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center border border-emerald-500/20">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
                <div class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    Sisa Profit
                </div>
            </div>
            <h4 class="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">Pemasukan Bersih (Net)</h4>
            <div class="text-2xl font-black text-emerald-400 truncate group-hover:scale-105 transition-transform origin-left" title="Rp {{ number_format($netRevenue, 0, ',', '.') }}">
                Rp {{ number_format($netRevenue, 0, ',', '.') }}
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column: Recent Orders --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Chart --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider">
                        {{ $selectedPlatform === 'all' ? 'Perbandingan Platform' : 'Performa ' . $selectedPlatform }} (30 Hari)
                    </h4>
                </div>
                <div class="h-72 w-full mt-4">
                    <canvas id="digitalPerformanceChart"></canvas>
                </div>
            </div>

            {{-- Recent E-Commerce Orders --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-gray-700/50 flex justify-between items-center bg-gray-900/30">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-orange-400"></i> Pesanan Online Terbaru
                    </h4>
                    <a href="{{ route('orders') }}" class="text-xs font-bold text-orange-400 hover:text-orange-300 px-3 py-1.5 bg-orange-500/10 rounded-md transition-colors border border-orange-500/20">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-[10px] uppercase font-bold tracking-wider border-b border-gray-700/50">
                            <tr>
                                <th class="px-5 py-3">Transaksi</th>
                                <th class="px-5 py-3">Platform</th>
                                <th class="px-5 py-3">Customer</th>
                                <th class="px-5 py-3">Total Amount</th>
                                <th class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($recentOrders as $order)
                            <tr class="hover:bg-gray-700/20 transition-colors group">
                                <td class="px-5 py-3">
                                    <div class="flex flex-col">
                                        <a href="{{ $order->route }}" class="font-mono font-bold text-orange-400 text-xs hover:text-orange-300 transition-colors">{{ $order->reference }}</a>
                                        <span class="text-[10px] text-gray-400 font-medium mt-0.5">{{ \Carbon\Carbon::parse($order->date)->format('d M Y, H:i') }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3 font-mono text-[10px] text-gray-400">{{ $order->platform }}</td>
                                <td class="px-5 py-3 text-gray-300">{{ $order->customer_name }}</td>
                                <td class="px-5 py-3 font-bold text-white">Rp {{ number_format($order->amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right">
                                    @php
                                        $statusConfig = [
                                            'completed' => ['color' => 'emerald', 'label' => 'Selesai'],
                                            'pending' => ['color' => 'amber', 'label' => 'Pending'],
                                            'processing' => ['color' => 'blue', 'label' => 'Proses'],
                                            'shipped' => ['color' => 'purple', 'label' => 'Dikirim'],
                                            'cancelled' => ['color' => 'red', 'label' => 'Batal'],
                                        ];
                                        $conf = $statusConfig[$order->status] ?? ['color' => 'gray', 'label' => ucfirst($order->status)];
                                    @endphp
                                    <span class="inline-flex px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-{{ $conf['color'] }}-500/10 text-{{ $conf['color'] }}-400 border border-{{ $conf['color'] }}-500/20">
                                        {{ $conf['label'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-12 text-gray-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <i data-lucide="shopping-cart" class="w-8 h-8 opacity-20"></i>
                                        <span>Belum ada pesanan e-commerce</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6 lg:col-span-1">
            {{-- Top Products Online --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
                <div class="p-4 border-b border-gray-700/50 flex justify-between items-center bg-gray-900/30">
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="package" class="w-4 h-4 text-emerald-400"></i> Terlaris (Online)
                    </h4>
                </div>
                <div class="p-4 divide-y divide-gray-700/50">
                    @forelse($topOnlineProducts as $i => $item)
                        <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3 group">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 font-bold text-sm
                                    {{ $i === 0 ? 'bg-amber-500/20 text-amber-500 border border-amber-500/30' : 
                                       ($i === 1 ? 'bg-gray-400/20 text-gray-300 border border-gray-400/30' : 
                                       ($i === 2 ? 'bg-orange-700/20 text-orange-600 border border-orange-700/30' : 'bg-gray-800 text-gray-500 border border-gray-700')) }}">
                                    #{{ $i + 1 }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-white truncate">{{ $item->product->name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $item->product->sku }}</p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <div class="text-sm font-black text-white">{{ number_format($item->total_sold, 0, ',', '.') }}</div>
                                <div class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider">Terjual</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-gray-500 text-sm">
                            Belum ada data penjualan online bulan ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chartData = @json($chartData);
        const ctx = document.getElementById('digitalPerformanceChart').getContext('2d');
        
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
            
            // Override colors for special datasets
            if (ds.label === 'Total' || ds.label === 'Total Revenue') color = '#10b981'; // Emerald
            if (ds.label.includes('Expenses')) color = '#ef4444'; // Red
            if (ds.label === 'Orders Count') color = '#6366f1'; // Indigo

            const isRevenue = ds.label.toLowerCase().includes('revenue') || ds.label === 'Total' || chartData.isComparison;
            
            // Special styling for primary line
            let fill = false;
            let backgroundColor = 'transparent';
            if (ds.label === 'Total' || (!chartData.isComparison && i === 0)) {
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
                yAxisID: chartData.isComparison ? 'y' : (i === 0 || ds.label.includes('Expenses') ? 'y' : 'y1'),
                borderDash: !chartData.isComparison && ds.label.toLowerCase().includes('orders') ? [5, 5] : []
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
                                
                                const isRevenue = label.toLowerCase().includes('revenue') || 
                                                label === 'Total: ' || 
                                                label.includes('Expenses') ||
                                                context.dataset.label === 'Total' || 
                                                @json($selectedPlatform === 'all') || 
                                                context.datasetIndex === 0;

                                if (isRevenue && !context.dataset.label.toLowerCase().includes('orders')) {
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
                        ticks: { color: '#6b7280', maxTicksLimit: 7 }
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

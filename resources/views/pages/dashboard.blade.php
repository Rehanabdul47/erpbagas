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
            <a href="{{ route('orders') ?? '#' }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors">
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

    <!-- KPI Stats -->
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

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm lg:col-span-2 flex flex-col">
            <div class="p-5 border-b border-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2 font-bold text-white"><i data-lucide="bar-chart-3" class="w-5 h-5 text-indigo-400"></i> Trend Penjualan (7 Hari Terakhir)</div>
                <select id="chartPlatformFilter" class="bg-gray-900 border border-gray-700 text-white text-sm rounded-lg focus:border-indigo-500 block p-2">
                    <option value="all">Semua Channel</option>
                    <option value="pos">Kasir / POS</option>
                    <option value="ecommerce">Semua E-Commerce</option>
                    <option value="Tokopedia">Tokopedia</option>
                    <option value="Shopee">Shopee</option>
                    <option value="TikTok">TikTok Shop</option>
                    <option value="Lazada">Lazada</option>
                </select>
            </div>
            <div class="p-5 flex-1 w-full relative min-h-[300px] flex items-center justify-center m-5">
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

    <!-- Phase 2: Finance, Attendance, Top Products -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Keuangan -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-4 border-b border-gray-700/50 flex items-center gap-2 font-bold text-white">
                <i data-lucide="wallet" class="w-5 h-5 text-emerald-400"></i> Keuangan (Bulan Ini)
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
                    <span class="text-sm text-gray-400">Saldo/Balance</span>
                    <span class="font-bold text-white text-lg">Rp {{ number_format($financeSummary['total_balance'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Kehadiran Karyawan -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-4 border-b border-gray-700/50 flex items-center gap-2 font-bold text-white">
                <i data-lucide="users" class="w-5 h-5 text-blue-400"></i> Kehadiran Karyawan
            </div>
            <div class="p-5 flex-1 flex flex-col justify-center">
                <div class="flex items-center justify-center gap-6 mb-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-white">{{ $attendanceSummary['present'] }}<span class="text-sm text-gray-500 font-normal">/{{ $attendanceSummary['total'] }}</span></div>
                        <div class="text-xs text-gray-400 mt-1">Hadir Hari Ini</div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-2 text-center">
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
            </div>
        </div>

        <!-- Top Produk -->
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-4 border-b border-gray-700/50 flex items-center gap-2 font-bold text-white">
                <i data-lucide="award" class="w-5 h-5 text-amber-500"></i> Top Produk Terlaris
            </div>
            <div class="p-2 overflow-y-auto max-h-[250px] space-y-1">
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

    <!-- Recent Orders -->
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm">
        <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
            <div class="flex items-center gap-2 font-bold text-white"><i data-lucide="list-ordered" class="w-5 h-5 text-indigo-400"></i> Order Terbaru</div>
            <a href="{{ route('orders') ?? '#' }}" class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">Lihat Semua &rarr;</a>
        </div>
        <div class="w-full overflow-x-auto">
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
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20 uppercase">Pending</span>
                            @elseif(in_array($order['status'], ['ready_to_ship', 'shipped', 'processing']))
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase">Proses Kirim</span>
                            @elseif(in_array($order['status'], ['completed', 'delivered']))
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">Selesai</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20 uppercase">{{ $order['status'] }}</span>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('salesTrendChart').getContext('2d');
        const initialData = @json($chartData);

        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: initialData.labels,
                datasets: [{
                    label: 'Total Revenue (Rp)',
                    data: initialData.data,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#8b5cf6',
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleColor: '#f3f4f6',
                        bodyColor: '#d1d5db',
                        borderColor: '#374151',
                        borderWidth: 1,
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
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
                        grid: { color: 'rgba(55, 65, 81, 0.5)', drawBorder: false },
                        ticks: { color: '#9ca3af', font: { size: 11 } }
                    },
                    y: {
                        grid: { color: 'rgba(55, 65, 81, 0.5)', drawBorder: false },
                        ticks: { 
                            color: '#9ca3af', 
                            font: { size: 11 },
                            callback: function(value, index, values) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                return value;
                            }
                        }
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
            }
        });

        // Filter functionality
        document.getElementById('chartPlatformFilter').addEventListener('change', function(e) {
            const platform = e.target.value;
            fetch(`/dashboard/chart-data?platform=${platform}`)
                .then(response => response.json())
                .then(data => {
                    salesChart.data.datasets[0].data = data.data;
                    salesChart.update();
                });
        });
    });
</script>
@endsection

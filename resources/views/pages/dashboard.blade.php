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
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center mb-4"><i data-lucide="shopping-bag" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">24</div>
            <div class="text-gray-400 text-sm mb-3">Order Hari Ini</div>
            <div class="flex items-center gap-1 text-xs font-medium text-emerald-400"><i data-lucide="trending-up" class="w-4 h-4"></i> +12% vs kemarin</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4"><i data-lucide="clock" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">5</div>
            <div class="text-gray-400 text-sm mb-3">Order Pending</div>
            <div class="flex items-center gap-1 text-xs font-medium text-amber-500"><i data-lucide="trending-down" class="w-4 h-4"></i> Butuh tindakan</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="banknote" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">Rp 12.5M</div>
            <div class="text-gray-400 text-sm mb-3">Revenue Hari Ini</div>
            <div class="flex items-center gap-1 text-xs font-medium text-emerald-400"><i data-lucide="trending-up" class="w-4 h-4"></i> +8% vs kemarin</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center mb-4"><i data-lucide="alert-triangle" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">3</div>
            <div class="text-gray-400 text-sm mb-3">Alert Stok</div>
            <div class="flex items-center gap-1 text-xs font-medium text-red-400"><i data-lucide="arrow-down" class="w-4 h-4"></i> 1 habis, 2 rendah</div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm lg:col-span-2 flex flex-col">
            <div class="p-5 border-b border-gray-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-2 font-bold text-white"><i data-lucide="bar-chart-3" class="w-5 h-5 text-indigo-400"></i> Trend Penjualan</div>
            </div>
            <div class="p-5 flex-1 w-full relative min-h-[250px] flex items-center justify-center border border-dashed border-gray-700 rounded-lg m-5">
                <span class="text-gray-500 text-sm">Integrasikan Library Chart JS/ApexCharts disini</span>
            </div>
        </div>
        
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-white"><i data-lucide="bell" class="w-5 h-5 text-indigo-400"></i> Notifikasi Terbaru</div>
            </div>
            <div class="p-2 overflow-y-auto max-h-[350px] space-y-1">
                <!-- Dummy notification -->
                <div class="flex items-start gap-3 p-3 hover:bg-gray-700/50 rounded-lg cursor-pointer transition-colors relative">
                    <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 bg-amber-500"></div>
                    <div class="flex-1 min-w-0">
                        <strong class="block text-sm font-semibold text-white truncate">Stok Gula Aren Menipis</strong>
                        <span class="block text-xs text-gray-400 mt-0.5 truncate">Sisa stok kurang dari batas minimum</span>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 hover:bg-gray-700/50 rounded-lg cursor-pointer transition-colors relative">
                    <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 bg-indigo-500"></div>
                    <div class="flex-1 min-w-0">
                        <strong class="block text-sm font-semibold text-white truncate">Pesanan Baru #ORD-011</strong>
                        <span class="block text-xs text-gray-400 mt-0.5 truncate">Pesanan dari Tokopedia senilai Rp 1.5M</span>
                    </div>
                </div>
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
                    <tr class="hover:bg-gray-700/20 transition-colors">
                        <td class="px-5 py-4 font-bold text-white">ORD-2024-001</td>
                        <td class="px-5 py-4 text-gray-300">Tokopedia</td>
                        <td class="px-5 py-4 text-gray-300">Budi Santoso</td>
                        <td class="px-5 py-4 font-bold text-white">Rp 1.500.000</td>
                        <td class="px-5 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-500 border border-amber-500/20">Pending</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Overview Marketing')
@section('breadcrumb')
<span class="text-gray-200">Divisi Marketing</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Overview Marketing</span>
@endsection

@section('content')
<div>
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
            <i data-lucide="line-chart" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white m-0">Overview Marketing</h3>
            <p class="text-xs text-gray-400 mt-0.5">Rangkuman performa penjualan, pelanggan, dan promosi.</p>
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-6">
        {{-- Transaksi POS Terbaru --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full lg:col-span-2">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-emerald-400"></i> Transaksi POS Terbaru
                </h4>
                <a href="{{ route('pos.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Buka POS →</a>
            </div>
            <div class="p-0 flex-1 relative min-h-[250px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">Invoice</th>
                                <th class="px-5 py-3 font-medium">Pelanggan</th>
                                <th class="px-5 py-3 font-medium">Tanggal</th>
                                <th class="px-5 py-3 font-medium">Total Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($recentTransactions as $sale)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-3 font-mono font-bold text-white text-xs">
                                    <a href="{{ route('pos.invoice', $sale) }}" target="_blank" class="hover:text-indigo-400 transition-colors">{{ $sale->invoice_no }}</a>
                                </td>
                                <td class="px-5 py-3 text-gray-300">{{ $sale->customer->name ?? 'Walk-in Customer' }}</td>
                                <td class="px-5 py-3 text-gray-400">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y H:i') }}</td>
                                <td class="px-5 py-3 font-bold text-emerald-400">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
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
@endsection

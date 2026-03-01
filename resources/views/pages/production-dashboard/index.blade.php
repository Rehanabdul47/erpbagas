@extends('layouts.app')

@section('title', 'Overview Produksi')
@section('breadcrumb')
<span class="text-gray-200">Divisi Produksi</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Overview Produksi</span>
@endsection

@section('content')
<div>
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white m-0">Overview Produksi</h3>
            <p class="text-xs text-gray-400 mt-0.5">Rangkuman operasional divisi produksi, inventori, dan suplai.</p>
        </div>
        <div class="ml-auto">
            <a href="{{ route('production.create') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-medium transition-colors shadow-sm shadow-indigo-600/20">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Mulai Produksi
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="package" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($totalStock) }}</div>
            <div class="text-gray-400 text-sm">Total Stok ({{ $totalSku }} SKU)</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="factory" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $activeProductionsCount }}</div>
            <div class="text-gray-400 text-sm">Produksi Berjalan</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4"><i data-lucide="alert-triangle" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $lowStock }}</div>
            <div class="text-gray-400 text-sm">Stok Kritis</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center mb-4"><i data-lucide="shopping-bag" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $activePosCount }}</div>
            <div class="text-gray-400 text-sm">PO Pending/Proses</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center mb-4"><i data-lucide="tags" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $totalCategories }}</div>
            <div class="text-gray-400 text-sm">Total Kategori</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-4"><i data-lucide="truck" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $totalSuppliers }}</div>
            <div class="text-gray-400 text-sm">Total Supplier</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-pink-500/10 text-pink-400 flex items-center justify-center mb-4"><i data-lucide="scissors" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $totalTailors }}</div>
            <div class="text-gray-400 text-sm">Total Penjahit</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-orange-500/10 text-orange-400 flex items-center justify-center mb-4"><i data-lucide="activity" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $activeTailorsCount }}</div>
            <div class="text-gray-400 text-sm">Penjahit Aktif</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-6">
        {{-- Produksi Aktif --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="factory" class="w-4 h-4 text-emerald-400"></i> Produksi Berjalan
                </h4>
                <a href="{{ route('production.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Lihat Semua →</a>
            </div>
            <div class="p-0 flex-1 relative min-h-[250px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">Batch</th>
                                <th class="px-5 py-3 font-medium">Produk</th>
                                <th class="px-5 py-3 font-medium">Qty</th>
                                <th class="px-5 py-3 font-medium">Penjahit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($activeProductions as $prod)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-3 font-mono font-bold text-indigo-400 text-xs">#{{ $prod->id }}</td>
                                <td class="px-5 py-3 text-gray-300">{{ $prod->product->name }}</td>
                                <td class="px-5 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400">{{ $prod->quantity }}</span></td>
                                <td class="px-5 py-3 text-xs text-gray-400">
                                    @if($prod->tailor)
                                        <div class="flex items-center gap-1.5 text-indigo-400 font-medium">
                                            <i data-lucide="scissors" class="w-3 h-3"></i>
                                            {{ $prod->tailor->name }}
                                        </div>
                                    @else
                                        <span class="italic text-gray-600">Internal</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-gray-500">
                                    <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-gray-600"></i>
                                    Semua produksi telah selesai.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Stok Kritis --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-500"></i> Stok Hampir Habis (Kritis)
                </h4>
                <a href="{{ route('inventory.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Inventori →</a>
            </div>
            <div class="p-0 flex-1 relative min-h-[250px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">SKU</th>
                                <th class="px-5 py-3 font-medium">Produk</th>
                                <th class="px-5 py-3 font-medium">Stok</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($criticalStockProducts as $p)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-3 font-bold text-white">{{ $p->sku }}</td>
                                <td class="px-5 py-3 text-gray-300">{{ $p->name }} <span class="text-xs text-gray-500">({{ $p->type === 'final_product' ? 'Final' : 'Raw' }})</span></td>
                                <td class="px-5 py-3">
                                    <span class="font-bold {{ $p->stock == 0 ? 'text-red-500' : 'text-amber-500' }}">{{ $p->stock }}</span> / <span class="text-gray-500">{{ $p->safety_stock }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-gray-500">
                                    <i data-lucide="check-circle" class="w-8 h-8 mx-auto mb-2 text-gray-600"></i>
                                    Stok aman. Tidak ada produk kritis.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Purchase Orders Terbaru --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full lg:col-span-2">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-blue-400"></i> Purchase Order Terbaru
                </h4>
                <a href="{{ route('purchase-orders.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">Lihat PO →</a>
            </div>
            <div class="p-0 flex-1 relative min-h-[250px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">PO Number</th>
                                <th class="px-5 py-3 font-medium">Supplier</th>
                                <th class="px-5 py-3 font-medium">Tanggal</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($recentPos as $po)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-5 py-3 font-mono font-bold text-white text-xs">
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="hover:text-indigo-400 transition-colors">{{ $po->po_number }}</a>
                                </td>
                                <td class="px-5 py-3 text-gray-300">{{ $po->supplier->name }}</td>
                                <td class="px-5 py-3 text-gray-400">{{ $po->order_date ? $po->order_date->format('d M Y') : '-' }}</td>
                                <td class="px-5 py-3">
                                    @php
                                        $colors = [
                                            'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                            'ordered' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                            'received' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                            'cancelled' => 'bg-red-500/10 text-red-500 border-red-500/20',
                                        ];
                                        $labels = [
                                            'pending' => 'Pending',
                                            'ordered' => 'Dipesan',
                                            'received' => 'Diterima',
                                            'cancelled' => 'Dibatalkan',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold border {{ $colors[$po->status] ?? 'bg-gray-500/10 text-gray-400 border-gray-500/20' }}">
                                        {{ $labels[$po->status] ?? ucfirst($po->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                    <i data-lucide="file-x" class="w-8 h-8 mx-auto mb-2 text-gray-600"></i>
                                    Belum ada data Purchase Order.
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

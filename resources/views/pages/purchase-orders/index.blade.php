@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('breadcrumb', 'Purchase Orders')

@section('content')
<div class="space-y-6" x-data="{
    searchQuery: '',
    statusFilter: ''
}">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="file-text" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1 tracking-tight">{{ $purchaseOrders->count() }}</div>
            <div class="text-gray-400 text-sm">Total PO</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4"><i data-lucide="clock" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1 tracking-tight">{{ $purchaseOrders->where('status', 'pending')->count() }}</div>
            <div class="text-gray-400 text-sm">Menunggu</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center mb-4"><i data-lucide="truck" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1 tracking-tight">{{ $purchaseOrders->where('status', 'ordered')->count() }}</div>
            <div class="text-gray-400 text-sm">Dipesan</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-4"><i data-lucide="check-square" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1 tracking-tight">{{ $purchaseOrders->where('status', 'received')->count() }}</div>
            <div class="text-gray-400 text-sm">Diterima</div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm flex flex-col lg:flex-row justify-between items-center gap-4">
        <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
            <div class="relative w-full sm:w-64">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                <input type="text" x-model="searchQuery" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500" placeholder="Cari No. PO atau Supplier...">
            </div>
            <select x-model="statusFilter" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="ordered">Dipesan</option>
                <option value="received">Diterima</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>
        <a href="{{ route('purchase-orders.create') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20 w-full lg:w-auto">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat PO Baru
        </a>
    </div>

    {{-- Purchase Orders Table --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden text-sm">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">No. PO & Tanggal</th>
                        <th class="px-5 py-4 font-medium">Supplier</th>
                        <th class="px-5 py-4 font-medium text-right">Total Nominal</th>
                        <th class="px-5 py-4 font-medium text-center">Status</th>
                        <th class="px-5 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($purchaseOrders as $po)
                    <tr x-show="(searchQuery === '' || '{{ strtolower($po->po_number) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($po->supplier->name ?? '') }}'.includes(searchQuery.toLowerCase())) && (statusFilter === '' || statusFilter === '{{ $po->status }}')" 
                        class="hover:bg-gray-700/20 transition-colors group cursor-pointer"
                        @click="window.location.href = '{{ route('purchase-orders.show', $po) }}'">
                        <td class="px-5 py-4">
                            <div class="font-bold text-white">{{ $po->po_number }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $po->date->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-white font-medium">{{ $po->supplier->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $po->supplier->contact_person ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="text-white font-bold">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $badgeClass = match($po->status) {
                                    'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                    'ordered' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'received' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                    'cancelled' => 'bg-red-500/10 text-red-400 border-red-500/20',
                                    default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                };
                                $statusLabel = match($po->status) {
                                    'pending' => 'Pending',
                                    'ordered' => 'Dipesan',
                                    'received' => 'Diterima',
                                    'cancelled' => 'Batal',
                                    default => ucfirst($po->status),
                                };
                            @endphp
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase border {{ $badgeClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors" title="Lihat Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12 text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-full bg-gray-900/50 flex items-center justify-center text-gray-600">
                                    <i data-lucide="file-text" class="w-8 h-8"></i>
                                </div>
                                <p class="text-sm font-medium">Belum ada data Purchase Order</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

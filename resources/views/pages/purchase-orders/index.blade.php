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

    {{-- Stats Grid (Simplified) --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-500 uppercase mb-1">Total PO</div>
            <div class="text-2xl font-black text-white leading-none">{{ $purchaseOrders->count() }}</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 border-l-4 border-l-amber-500 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-500 uppercase mb-1">Pending</div>
            <div class="text-2xl font-black text-amber-500 leading-none">{{ $purchaseOrders->where('status', 'pending')->count() }}</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 border-l-4 border-l-indigo-500 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-500 uppercase mb-1">Dipesan</div>
            <div class="text-2xl font-black text-indigo-500 leading-none">{{ $purchaseOrders->where('status', 'ordered')->count() }}</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 border-l-4 border-l-emerald-500 rounded-xl p-4 shadow-sm">
            <div class="text-xs font-bold text-gray-500 uppercase mb-1">Diterima</div>
            <div class="text-2xl font-black text-emerald-500 leading-none">{{ $purchaseOrders->where('status', 'received')->count() }}</div>
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
            <i data-lucide="plus-square" class="w-4 h-4"></i> Buat Purchase Order
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden text-sm">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">No. PO & Tanggal</th>
                        <th class="px-5 py-4 font-medium">Supplier</th>
                        <th class="px-5 py-4 font-medium text-right">Total Amount</th>
                        <th class="px-5 py-4 font-medium text-center">Status</th>
                        <th class="px-5 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($purchaseOrders as $po)
                    <tr x-show="(searchQuery === '' || '{{ strtolower($po->po_number) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($po->supplier->name ?? '') }}'.includes(searchQuery.toLowerCase())) && (statusFilter === '' || statusFilter === '{{ $po->status }}')" 
                        class="hover:bg-gray-700/20 transition-colors cursor-pointer group"
                        @click="window.location.href = '{{ route('purchase-orders.show', $po) }}'">
                        <td class="px-5 py-4">
                            <div class="font-black text-white">{{ $po->po_number }}</div>
                            <div class="text-[10px] text-gray-500 font-mono mt-0.5">{{ $po->date->format('d M Y') }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-white font-bold">{{ $po->supplier->name ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500">Contact: {{ $po->supplier->contact_person ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="text-indigo-400 font-black tracking-tight text-base">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $badgeClass = match($po->status) {
                                    'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                                    'ordered' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
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
                            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border {{ $badgeClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('purchase-orders.show', $po) }}" class="p-2 bg-gray-900 rounded-lg text-gray-400 hover:text-white transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-12 text-gray-500 font-medium">Belum ada Purchase Order</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

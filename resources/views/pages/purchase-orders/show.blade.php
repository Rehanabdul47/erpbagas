@extends('layouts.app')

@section('title', 'Detail Purchase Order')
@section('breadcrumb', 'PO / Detail')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12">
    
    {{-- Header Card --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-6 bg-gradient-to-br from-gray-800 to-gray-900 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shadow-inner border border-indigo-500/20">
                    <i data-lucide="file-text" class="w-7 h-7"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-white leading-none tracking-tight">{{ $purchaseOrder->po_number }}</h2>
                    <p class="text-gray-500 text-sm font-medium mt-1 uppercase tracking-widest">Tanggal: {{ $purchaseOrder->date->format('d F Y') }}</p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2">
                @php
                    $statusClass = match($purchaseOrder->status) {
                        'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                        'ordered' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                        'received' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                        'cancelled' => 'bg-red-500/10 text-red-500 border-red-500/20',
                        default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                    };
                @endphp
                <span class="px-4 py-1.5 rounded-full text-xs font-black uppercase border {{ $statusClass }}">
                    {{ ucfirst($purchaseOrder->status) }}
                </span>
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-widest">Dibuat oleh: {{ $purchaseOrder->user->name }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 border-t border-gray-700/50">
            <div class="p-6 border-b md:border-b-0 md:border-r border-gray-700/50 space-y-4">
                <div class="flex items-center gap-3 text-indigo-400">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                    <h4 class="text-xs font-black uppercase tracking-widest">Informasi Supplier</h4>
                </div>
                <div class="space-y-2">
                    <div class="text-lg font-black text-white tracking-tight">{{ $purchaseOrder->supplier->name }}</div>
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i> {{ $purchaseOrder->supplier->contact_person ?: '-' }}
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-400">
                            <i data-lucide="phone" class="w-3.5 h-3.5"></i> {{ $purchaseOrder->supplier->phone ?: '-' }}
                        </div>
                        <p class="text-xs text-gray-500 leading-relaxed mt-2 italic">
                            {{ $purchaseOrder->supplier->address ?: 'Tidak ada alamat tercatat.' }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4 bg-gray-900/10">
                <div class="flex items-center gap-3 text-indigo-400">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <h4 class="text-xs font-black uppercase tracking-widest">Catatan & Ringkasan</h4>
                </div>
                <div class="space-y-4">
                    <div class="p-3 bg-gray-900/50 border border-gray-700/50 rounded-xl text-xs text-gray-300 min-h-[60px]">
                        {{ $purchaseOrder->notes ?: 'Tidak ada catatan tambahan.' }}
                    </div>
                    <div class="flex justify-between items-center text-gray-400">
                        <span class="text-xs font-bold uppercase tracking-widest">Metode Pembayaran</span>
                        <span class="text-sm font-black text-white">Invoice (Term)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-xl overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-900/80 text-[10px] font-black uppercase tracking-widest text-gray-500 border-b border-gray-700/50">
                <tr>
                    <th class="px-6 py-4">Item (SKU)</th>
                    <th class="px-6 py-4 text-center">Jumlah</th>
                    <th class="px-6 py-4 text-right">Harga Unit</th>
                    <th class="px-6 py-4 text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                @foreach($purchaseOrder->items as $item)
                <tr class="hover:bg-gray-700/20 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-bold text-white text-sm">{{ $item->product->name }}</div>
                        <div class="text-[10px] font-mono text-gray-500 uppercase">{{ $item->product->sku }}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2.5 py-1 bg-gray-900 rounded-lg text-xs font-black text-white border border-gray-700 shadow-inner">
                            {{ number_format($item->quantity, 0) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="text-xs font-medium text-gray-400">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="text-sm font-black text-white tracking-tight">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-900/50">
                <tr>
                    <td colspan="3" class="px-6 py-5 text-right font-black text-gray-500 uppercase tracking-widest text-[10px]">Grand Total</td>
                    <td class="px-6 py-5 text-right">
                        <div class="text-xl font-black text-indigo-400 tracking-tighter">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Actions Bar --}}
    <div class="bg-gray-900/40 border border-gray-700/50 rounded-2xl p-4 flex flex-wrap justify-between items-center gap-4">
        <a href="{{ route('purchase-orders.index') }}" class="flex items-center gap-2 text-sm font-bold text-gray-400 hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Daftar
        </a>

        <div class="flex gap-3">
            @if($purchaseOrder->status !== 'received' && $purchaseOrder->status !== 'cancelled')
                <form action="{{ route('purchase-orders.update-status', $purchaseOrder) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="px-5 py-2.5 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all border border-red-500/20 active:scale-95" onclick="return confirm('Batalkan PO ini?')">
                        Batalkan PO
                    </button>
                </form>

                @if($purchaseOrder->status === 'pending')
                <form action="{{ route('purchase-orders.update-status', $purchaseOrder) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="ordered">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">
                        Kirim Pesanan
                    </button>
                </form>
                @endif

                @if($purchaseOrder->status === 'ordered')
                <form action="{{ route('purchase-orders.update-status', $purchaseOrder) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="received">
                    <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-lg active:scale-95" onclick="return confirm('Apakah barang sudah diterima? Stok akan bertambah otomatis.')">
                        Terima & Masuk Stok
                    </button>
                </form>
                @endif
            @endif

            <button onclick="window.print()" class="px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all border border-gray-700 flex items-center gap-2 active:scale-95">
                <i data-lucide="printer" class="w-4 h-4"></i> Print PO
            </button>
        </div>
    </div>
</div>

@push('scripts')
<style>
    @media print {
        #sidebar, #navbar, #breadcrumb, .actions-bar, button, footer { display: none !important; }
        body { background: white !important; color: black !important; }
        .bg-gray-800, .bg-gray-900 { background: white !important; }
        .border-gray-700 { border-color: #eee !important; }
        .text-white { color: black !important; }
        main { margin-left: 0 !important; }
        #page-content { padding: 0 !important; margin: 0 !important; }
    }
</style>
@endpush
@endsection

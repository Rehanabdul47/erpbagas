@extends('layouts.app')

@section('title', 'Detail Purchase Order')
@section('breadcrumb', 'PO / Detail')

@section('content')
@php
    $gs = $global_settings ?? [];
    $storeName = $gs['invoice_store_name'] ?? ($gs['company_name'] ?? 'GUPALA BATIK');
    $tagline = $gs['invoice_tagline'] ?? '';
    $invoiceAddress = $gs['invoice_address'] ?? ($gs['address'] ?? '');
    $invoicePhone = $gs['invoice_phone'] ?? ($gs['contact_phone'] ?? '');
@endphp
<div class="print-header hidden">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #1f2937; padding-bottom: 15px; margin-bottom: 25px;">
        <div style="text-align: left;">
            <h2 style="font-size: 24px; font-weight: 900; text-transform: uppercase; margin: 0; color: #111827;">{{ $storeName }}</h2>
            @if($tagline)<div style="font-size: 12px; color: #4b5563; margin-top: 4px;">{{ $tagline }}</div>@endif
            @if($invoiceAddress)<div style="font-size: 12px; color: #6b7280; margin-top: 4px; max-width: 300px;">{{ $invoiceAddress }}</div>@endif
            @if($invoicePhone)<div style="font-size: 12px; color: #6b7280; margin-top: 2px;">Telp: {{ $invoicePhone }}</div>@endif
        </div>
        <div style="text-align: right;">
            <h1 style="font-size: 28px; font-weight: 900; color: #111827; margin: 0; letter-spacing: -1px;">PURCHASE ORDER</h1>
            <div style="font-size: 14px; font-weight: bold; color: #4f46e5; margin-top: 4px;">#{{ $purchaseOrder->po_number }}</div>
        </div>
    </div>
</div>
<div class="max-w-5xl mx-auto space-y-6 pb-20">
    
    {{-- Main Container Card --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-xl overflow-hidden flex flex-col">
        
        {{-- Header Header --}}
        <div class="p-8 bg-gradient-to-br from-gray-800 to-gray-900 flex flex-col md:flex-row justify-between items-start md:items-center gap-8 border-b border-gray-700/50">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shadow-inner border border-indigo-500/20">
                    <i data-lucide="file-text" class="w-8 h-8"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-white leading-none tracking-tight">{{ $purchaseOrder->po_number }}</h2>
                    <p class="text-gray-500 text-sm font-bold mt-2 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-indigo-500/50"></i> {{ $purchaseOrder->date->format('d F Y') }}
                    </p>
                </div>
            </div>
            
            <div class="flex flex-col items-end gap-3 w-full md:w-auto">
                @php
                    $statusClass = match($purchaseOrder->status) {
                        'pending' => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
                        'ordered' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                        'received' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                        'cancelled' => 'bg-red-500/10 text-red-500 border-red-500/20',
                        default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                    };
                @endphp
                <span class="px-5 py-2 rounded-full text-[11px] font-black uppercase border tracking-widest shadow-sm {{ $statusClass }}">
                    {{ ucfirst($purchaseOrder->status) }}
                </span>
                <div class="text-[10px] text-gray-500 font-black uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="user" class="w-3.5 h-3.5"></i> Pembuat: {{ $purchaseOrder->user->name }}
                </div>
            </div>
        </div>

        {{-- Entity Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 border-b border-gray-700/50">
            <div class="p-8 border-b md:border-b-0 md:border-r border-gray-700/50 space-y-6">
                <div class="flex items-center gap-3 text-indigo-400">
                    <i data-lucide="truck" class="w-4 h-4"></i>
                    <h4 class="text-[10px] font-black uppercase tracking-widest">Informasi Supplier</h4>
                </div>
                <div class="space-y-4">
                    <div class="text-2xl font-black text-white tracking-tight leading-none">{{ $purchaseOrder->supplier->name ?? '-' }}</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase">Kontak Person</span>
                            <div class="text-sm font-bold text-gray-300">{{ $purchaseOrder->supplier->contact_person ?: '-' }}</div>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-gray-500 uppercase">No. Telepon</span>
                            <div class="text-sm font-bold text-gray-300">{{ $purchaseOrder->supplier->phone ?: '-' }}</div>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-gray-700/30">
                        <span class="text-[10px] font-bold text-gray-500 uppercase block mb-2">Alamat Pengiriman</span>
                        <p class="text-xs text-gray-400 leading-relaxed italic">
                            {{ $purchaseOrder->supplier->address ?: 'Alamat tidak tersedia.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-8 bg-gray-900/10 space-y-6">
                <div class="flex items-center gap-3 text-indigo-400">
                    <i data-lucide="info" class="w-4 h-4"></i>
                    <h4 class="text-[10px] font-black uppercase tracking-widest">Catatan & Meta Data</h4>
                </div>
                <div class="space-y-6">
                    <div class="bg-gray-900/50 border border-gray-700/50 rounded-2xl p-4 text-xs text-gray-400 min-h-[100px] leading-relaxed italic shadow-inner">
                        {{ $purchaseOrder->notes ?: 'Tidak ada catatan tambahan untuk pesanan ini.' }}
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm border-l-4 border-l-indigo-500">
                            <div class="text-[9px] font-black text-gray-500 uppercase mb-1">Status Pembayaran</div>
                            <div class="text-xs font-black text-white">Invoice (Kredit)</div>
                        </div>
                        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm border-l-4 border-l-indigo-500">
                            <div class="text-[9px] font-black text-gray-500 uppercase mb-1">Total Unit Barang</div>
                            <div class="text-xs font-black text-white">{{ $purchaseOrder->items->sum('quantity') }} Items</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="p-0 overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-900/80 text-[10px] font-black uppercase tracking-widest text-gray-500 border-b border-gray-700/50">
                    <tr>
                        <th class="px-8 py-5">Deskripsi Produk</th>
                        <th class="px-8 py-5 text-center">Jumlah (Qty)</th>
                        <th class="px-8 py-5 text-right">Harga Satuan</th>
                        <th class="px-8 py-5 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @foreach($purchaseOrder->items as $item)
                    <tr class="hover:bg-gray-700/10 transition-colors">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-lg bg-gray-900 border border-gray-700 shrink-0 flex items-center justify-center">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset($item->product->image) }}" class="w-full h-full object-cover rounded-lg">
                                    @else
                                        <i data-lucide="package" class="w-5 h-5 text-gray-700"></i>
                                    @endif
                                </div>
                                <div class="min-w-0 flex flex-col items-start">
                                    <div class="font-bold text-white text-sm truncate">{{ $item->supplierProduct ? $item->supplierProduct->name : ($item->product ? $item->product->name : 'Unknown Item') }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($item->supplierProduct && $item->supplierProduct->sku)
                                            <span class="text-[10px] font-mono text-gray-500 uppercase tracking-tighter">{{ $item->supplierProduct->sku }}</span>
                                        @endif
                                        @if($item->product)
                                            <span class="text-[9px] bg-indigo-500/20 text-indigo-400 px-1.5 py-0.5 rounded uppercase font-bold tracking-widest border border-indigo-500/30">Auto-Stok: {{ $item->product->name }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-sm font-black text-gray-200">{{ number_format($item->quantity, 0) }} {{ $item->supplierProduct ? $item->supplierProduct->unit : '' }}</span>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="text-xs font-medium text-gray-400">Rp {{ number_format($item->cost_price, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-8 py-6 text-right">
                            <div class="text-sm font-black text-white tracking-tight">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-900/50">
                    <tr>
                        <td colspan="3" class="px-8 py-8 text-right font-black text-gray-500 uppercase tracking-widest text-xs">Grand Total Keseluruhan</td>
                        <td class="px-8 py-8 text-right">
                            <div class="text-3xl font-black text-indigo-400 tracking-tighter leading-none">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="print-signatures hidden pt-12 mt-12 mb-8" style="page-break-inside: avoid;">
            <div style="display: flex; justify-content: space-between; width: 100%; padding: 0 40px;">
                <div style="text-align: center; width: 200px;">
                    <p style="font-size: 12px; font-weight: bold; color: #4b5563; margin-bottom: 80px;">Pemasok (Supplier)</p>
                    <p style="font-size: 14px; font-weight: bold; color: #111827; text-decoration: underline;">{{ $purchaseOrder->supplier->name ?? '.......................' }}</p>
                </div>
                <div style="text-align: center; width: 200px;">
                    <p style="font-size: 12px; font-weight: bold; color: #4b5563; margin-bottom: 80px;">Dipesan Oleh</p>
                    <p style="font-size: 14px; font-weight: bold; color: #111827; text-decoration: underline;">{{ $storeName }}</p>
                </div>
                <div style="text-align: center; width: 200px;">
                    <p style="font-size: 12px; font-weight: bold; color: #4b5563; margin-bottom: 80px;">Manajemen / Keuangan</p>
                    <p style="font-size: 14px; font-weight: bold; color: #111827;">(......................................)</p>
                </div>
            </div>
        </div>
    </div>


    {{-- Sticky Actions Panel --}}
    <div class="bg-gray-900/60 border border-gray-700/50 backdrop-blur-md rounded-2xl p-5 flex flex-wrap justify-between items-center gap-6 shadow-2xl">
        <a href="{{ route('purchase-orders.index') }}" class="flex items-center gap-3 text-xs font-black text-gray-500 hover:text-white transition-all uppercase tracking-widest group">
            <i data-lucide="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-1"></i> Kembali ke Daftar
        </a>

        <div class="flex gap-4">
            @if($purchaseOrder->status !== 'received' && $purchaseOrder->status !== 'cancelled')
                <form id="cancel-po-form" action="{{ route('purchase-orders.update-status', $purchaseOrder) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="button" 
                        @click="$dispatch('global-confirm', { 
                            title: 'BATALKAN PESANAN', 
                            message: 'Apakah Anda yakin ingin membatalkan Purchase Order ini? Status akan berubah menjadi Cancelled.',
                            onConfirm: () => { document.getElementById('cancel-po-form').submit(); },
                            confirmText: 'BATALKAN PO'
                        })"
                        class="px-6 py-3 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all border border-red-500/20 active:scale-95 shadow-lg shadow-red-900/10">
                        Batalkan PO
                    </button>
                </form>

                @if($purchaseOrder->status === 'pending')
                <form action="{{ route('purchase-orders.update-status', $purchaseOrder) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="ordered">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-xl shadow-indigo-900/20 active:scale-95 flex items-center gap-2">
                        <i data-lucide="send" class="w-4 h-4"></i> Kirim Pesanan
                    </button>
                </form>
                @endif

                @if($purchaseOrder->status === 'ordered')
                <form id="receive-po-form" action="{{ route('purchase-orders.update-status', $purchaseOrder) }}" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" value="received">
                    <button type="button" 
                        @click="$dispatch('global-confirm', { 
                            title: 'TERIMA BARANG', 
                            message: 'Apakah barang sudah diterima? Stok inventaris akan bertambah otomatis secara sistem.',
                            onConfirm: () => { document.getElementById('receive-po-form').submit(); },
                            confirmText: 'TERIMA'
                        })"
                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-xl shadow-emerald-900/20 active:scale-95 flex items-center gap-2">
                        <i data-lucide="package-check" class="w-4 h-4"></i> Terima & Input Stok
                    </button>
                </form>
                @endif
            @endif

            <button onclick="window.print()" class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all border border-gray-700 flex items-center gap-2 active:scale-95 hover:shadow-lg transition-all duration-300">
                <i data-lucide="printer" class="w-4 h-4"></i> Cetak PO
            </button>
        </div>
    </div>
</div>

@push('scripts')
<style>
    @media print {
        #sidebar, #navbar, #breadcrumb, .sticky-actions, button, footer, .sticky { display: none !important; }
        body { background: white !important; color: black !important; font-family: 'Inter', sans-serif, Arial; margin: 0; padding: 0;}
        main { margin-left: 0 !important; margin-top: 0 !important; padding: 0 !important; }
        
        .max-w-5xl { max-width: 100% !important; margin: 0 !important; padding: 0 !important; border: none !important; box-shadow: none !important; background: transparent !important; }
        .bg-gray-800, .bg-gray-900, .bg-gradient-to-br, .bg-gray-900\/50, .bg-gray-800\/50 { background: white !important; }
        .border-gray-700, .border-gray-700\/50, .border-gray-700\/30 { border-color: #e5e7eb !important; }
        .text-white, .text-gray-200, .text-gray-300 { color: #111827 !important; }
        .text-gray-400, .text-gray-500 { color: #4b5563 !important; }
        .text-indigo-400 { color: #4f46e5 !important; }
        
        /* Clear the heavy top header since we have .print-header now */
        .p-8.bg-gradient-to-br { display: none !important; } 
        
        /* Adjust layout spacing */
        .grid-cols-1.md\:grid-cols-2 { display: flex !important; width: 100% !important; border-bottom: 1px solid #e5e7eb !important; }
        .grid-cols-1.md\:grid-cols-2 > div { width: 50% !important; padding: 20px 0 !important; border: none !important; }
        .grid-cols-1.md\:grid-cols-2 > div:first-child { padding-right: 20px !important; }
        .grid-cols-1.md\:grid-cols-2 > div:last-child { padding-left: 20px !important; }
        
        /* Table styles */
        table { border-collapse: collapse !important; width: 100% !important; border: 1px solid #e5e7eb !important; }
        th { background: #f3f4f6 !important; color: #374151 !important; border-bottom: 2px solid #e5e7eb !important; padding: 12px 15px !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        td { border-bottom: 1px solid #e5e7eb !important; padding: 12px 15px !important; }
        tfoot td { background: #f9fafb !important; border-top: 2px solid #e5e7eb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        
        /* Show print-specific elements */
        .print-header { display: block !important; }
        .print-signatures { display: block !important; }
        
        /* Utilities */
        .shadow-xl, .shadow-sm, .shadow-inner { box-shadow: none !important; }
        .rounded-2xl, .rounded-xl, .rounded-lg { border-radius: 0 !important; }
        img { filter: grayscale(100%); max-width: 40px !important; max-height: 40px !important; }
        .bg-indigo-500\/10, .bg-indigo-500\/20 { background: #edf2f7 !important; color: #4a5568 !important; border: 1px solid #e2e8f0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@endpush
@endsection

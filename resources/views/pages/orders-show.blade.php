@extends('layouts.app')

@section('title', 'Detail Pesanan E-Commerce')
@section('breadcrumb', 'Detail Pesanan')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    {{-- Header & Status --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-2xl p-6 shadow-sm">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <h2 class="text-2xl font-black text-white">#{{ $order->order_number }}</h2>
                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest {{ 
                        $order->status == 'delivered' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 
                        ($order->status == 'cancelled' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-amber-500/10 text-amber-500 border border-amber-500/20')
                    }}">
                        {{ str_replace('_', ' ', $order->status) }}
                    </span>
                </div>
                <div class="text-sm text-gray-400 flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                    Pesanan dibuat pada {{ $order->order_date->format('d M Y, H:i') }}
                </div>
            </div>
            <div class="flex gap-3 w-full md:w-auto">
                <a href="{{ route('orders.index') }}" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm font-bold transition-all">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
                </a>
                <button class="flex-1 md:flex-none flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-600/20">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Label
                </button>
            </div>
        </div>

        {{-- Status Progress (Simplified version of Shopee's timeline) --}}
        <div class="relative py-4">
            <div class="absolute top-[3.25rem] left-0 w-full h-0.5 bg-gray-700"></div>
            <div class="relative flex justify-between">
                @php
                    $stages = ['pending', 'ready_to_ship', 'shipped', 'delivered'];
                    $currentIndex = array_search($order->status, $stages);
                    if ($currentIndex === false) $currentIndex = -1;
                @endphp
                @foreach(['Waktu Pesanan', 'Siap Dikirim', 'Dalam Perjalanan', 'Pesanan Selesai'] as $index => $label)
                <div class="flex flex-col items-center group relative z-10">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all border-4 {{ $index <= $currentIndex ? 'bg-emerald-500 border-emerald-500/20 text-white' : 'bg-gray-800 border-gray-700 text-gray-500' }}">
                        <i data-lucide="{{ ['check', 'package', 'truck', 'flag'][$index] }}" class="w-4 h-4"></i>
                    </div>
                    <span class="mt-3 text-[10px] font-bold uppercase tracking-wider {{ $index <= $currentIndex ? 'text-emerald-400' : 'text-gray-500' }}">{{ $label }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Products & Payments --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Produk --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-700/50 bg-gray-900/50 flex items-center gap-2">
                    <i data-lucide="box" class="w-5 h-5 text-indigo-400"></i>
                    <h3 class="font-bold text-white">Rincian Produk</h3>
                </div>
                <div class="p-0 overflow-x-auto">
                    <table class="w-full text-left text-sm min-w-[500px]">
                        <thead class="bg-gray-900/30 text-gray-500 text-[10px] uppercase tracking-widest border-b border-gray-700/50">
                            <tr>
                                <th class="px-6 py-3 font-bold">Produk</th>
                                <th class="px-6 py-3 font-bold text-center">Harga Satuan</th>
                                <th class="px-6 py-3 font-bold text-center">Jumlah</th>
                                <th class="px-6 py-3 font-bold text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @foreach($order->items as $item)
                            <tr class="group hover:bg-gray-700/20">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg bg-gray-900 border border-gray-700 flex items-center justify-center shrink-0 overflow-hidden">
                                            @if($item->product && $item->product->image)
                                                <img src="{{ asset('storage/' . $item->product->image) }}" class="w-full h-full object-cover">
                                            @else
                                                <i data-lucide="image" class="w-6 h-6 text-gray-700"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-white leading-tight mb-1">{{ $item->product ? $item->product->name : 'Produk Tidak Terdaftar' }}</div>
                                            <div class="text-[10px] text-gray-500 font-mono">SKU: {{ $item->product ? $item->product->sku : '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-gray-300 font-mono">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center text-gray-300">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 text-right font-bold text-white font-mono">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Informasi Pembayaran (Financial Breakdown) --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-700/50 bg-gray-900/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="receipt" class="w-5 h-5 text-emerald-400"></i>
                        <h3 class="font-bold text-white">Informasi Penghasilan</h3>
                    </div>
                </div>
                <div class="p-6 space-y-4">
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400 uppercase text-[10px] font-bold tracking-wider">Subtotal Pesanan (Harga Produk)</span>
                            <span class="text-white font-bold font-mono">Rp {{ number_format($order->subtotal_product ?: $order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400 uppercase text-[10px] font-bold tracking-wider">Ongkos Kirim dibayar Pembeli</span>
                            <span class="text-white font-mono font-medium">Rp {{ number_format($order->shipping_fee_buyer_paid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-400 italic font-medium uppercase text-[10px] tracking-wider">Potongan Ongkos Kirim (Shopee)</span>
                            <span class="text-emerald-400 font-mono">-Rp {{ number_format($order->shipping_fee_discount, 0, ',', '.') }}</span>
                        </div>
                        <div class="h-px bg-gray-700/50 my-2"></div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-300 font-black uppercase text-[10px] tracking-widest">Biaya Lainnya</span>
                            @php 
                                $biayaLain = ($order->admin_fee + $order->service_fee + $order->processing_fee);
                                $subtotalProduk = $order->subtotal_product ?: $order->total_amount;
                                $penghasilan = $order->total_income ?: ($subtotalProduk - $biayaLain);
                            @endphp
                            <span class="text-red-400 font-bold font-mono">-Rp {{ number_format($biayaLain, 0, ',', '.') }}</span>
                        </div>
                        <div class="pl-4 space-y-2 border-l-2 border-gray-700/50 mt-1">
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Biaya Administrasi</span>
                                <span>-Rp {{ number_format($order->admin_fee, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Biaya Layanan</span>
                                <span>-Rp {{ number_format($order->service_fee, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500">
                                <span>Biaya Proses Pesanan</span>
                                <span>-Rp {{ number_format($order->processing_fee, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-indigo-500/5 px-6 py-6 border-t border-indigo-500/10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="text-indigo-300 font-black uppercase tracking-widest text-xs flex items-center gap-2">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                        Estimasi Total Penghasilan
                    </div>
                    <div class="text-4xl font-black text-indigo-400 font-mono tracking-tighter">Rp {{ number_format($penghasilan, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        {{-- Right Column: Shipping & Logs --}}
        <div class="space-y-6">
            {{-- Delivery Info --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-700/50 bg-gray-900/50 flex items-center gap-2">
                    <i data-lucide="truck" class="w-5 h-5 text-amber-500"></i>
                    <h3 class="font-bold text-white">Informasi Pengiriman</h3>
                </div>
                <div class="p-6 space-y-6">
                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 block">Alamat Pengiriman</label>
                        <div class="text-[13px] text-gray-200 leading-relaxed bg-gray-900/50 p-4 rounded-xl border border-gray-700/50">
                            <div class="font-bold text-indigo-400 mb-1.5 uppercase tracking-wide">{{ $order->customer_name }}</div>
                            {{ $order->shipping_address }}
                            <div class="mt-2 text-xs text-gray-500 font-mono">{{ $order->customer_phone }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-2 block">Jasa Kirim & Resi</label>
                        <div class="p-4 bg-gray-900/50 rounded-xl border border-gray-700/50 space-y-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-500/20">
                                    <i data-lucide="package-2" class="w-5 h-5"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest">Platform Kurir</div>
                                    <div class="text-sm font-black text-white">{{ $order->shipping_courier }}</div>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-gray-700/50">
                                <div class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1.5">No. Resi / AWB</div>
                                <div class="flex items-center justify-between gap-2 bg-gray-800 px-3 py-2 rounded-lg border border-gray-700">
                                    <span class="text-xs font-mono font-bold text-indigo-400">{{ $order->receipt_number ?: 'Belum ada resi' }}</span>
                                    @if($order->receipt_number)
                                        <button class="text-gray-500 hover:text-white transition-colors" onclick="navigator.clipboard.writeText('{{ $order->receipt_number }}')">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-700/50 bg-gray-900/50 flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-indigo-400"></i>
                    <h3 class="font-bold text-white">Log Aktivitas</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-6 relative ml-2">
                        <div class="absolute top-1 bottom-1 left-0 w-0.5 bg-gray-700/50"></div>
                        
                        <div class="relative pl-6">
                            <div class="absolute left-[-5px] top-1.5 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-500/10"></div>
                            <div class="text-[10px] font-bold text-white uppercase tracking-widest mb-1">Update Status: {{ str_replace('_', ' ', $order->status) }}</div>
                            <div class="text-[9px] text-gray-500">{{ $order->updated_at->format('d M Y, H:i') }}</div>
                        </div>

                        <div class="relative pl-6">
                            <div class="absolute left-[-5px] top-1.5 w-2.5 h-2.5 rounded-full bg-gray-600"></div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Pesanan Diterima di Sistem</div>
                            <div class="text-[9px] text-gray-500">{{ $order->order_date->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

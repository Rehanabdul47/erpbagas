@extends('layouts.app')

@section('title', 'TikTok Orders')
@section('breadcrumb', 'TikTok Orders')

@section('content')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <div>
        <h3 class="text-2xl font-black text-white flex items-center gap-2">
            <i data-lucide="shopping-bag" class="w-6 h-6 text-orange-400"></i>
            TikTok Orders
        </h3>
        <p class="text-sm text-gray-400 mt-1">Kelola dan proses pesanan dari TikTok Shop langsung dari sistem ini.</p>
    </div>
</div>

<div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
    <div class="p-4 border-b border-gray-700/50 flex justify-between items-center bg-gray-900/30">
        <form method="GET" action="{{ route('digital-marketing.orders.index') }}" class="flex gap-2">
            <select name="status" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">Semua Status</option>
                <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Belum Dibayar</option>
                <option value="AWAITING_SHIPMENT" {{ request('status') === 'AWAITING_SHIPMENT' ? 'selected' : '' }}>Perlu Dikirim</option>
                <option value="AWAITING_COLLECTION" {{ request('status') === 'AWAITING_COLLECTION' ? 'selected' : '' }}>Menunggu Kurir</option>
                <option value="IN_TRANSIT" {{ request('status') === 'IN_TRANSIT' ? 'selected' : '' }}>Dalam Perjalanan</option>
                <option value="DELIVERED" {{ request('status') === 'DELIVERED' ? 'selected' : '' }}>Terkirim</option>
                <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>Selesai</option>
                <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </form>
    </div>
    
    <div class="overflow-x-auto w-full">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-900/50 text-gray-400 text-[10px] uppercase font-bold tracking-wider border-b border-gray-700/50">
                <tr>
                    <th class="px-5 py-3">Order ID / Tanggal</th>
                    <th class="px-5 py-3">Customer</th>
                    <th class="px-5 py-3">Item</th>
                    <th class="px-5 py-3">Total Amount</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-700/20 transition-colors">
                    <td class="px-5 py-3">
                        <div class="font-mono font-bold text-orange-400 text-xs">{{ $order->platform_order_id }}</div>
                        <div class="text-[10px] text-gray-500 mt-1">{{ optional($order->order_created_at)->format('d M Y, H:i') }}</div>
                    </td>
                    <td class="px-5 py-3">
                        <div class="text-white font-medium">{{ $order->recipient_name }}</div>
                        <div class="text-xs text-gray-400 truncate max-w-[200px]" title="{{ $order->full_address }}">{{ $order->city }}, {{ $order->province }}</div>
                    </td>
                    <td class="px-5 py-3">
                        @foreach($order->items->take(2) as $item)
                        <div class="text-xs text-gray-300 truncate max-w-[200px]" title="{{ $item->product_name }}">
                            {{ $item->quantity }}x {{ $item->product_name }}
                        </div>
                        @endforeach
                        @if($order->items->count() > 2)
                        <div class="text-[10px] text-indigo-400 mt-0.5">+ {{ $order->items->count() - 2 }} item lainnya</div>
                        @endif
                    </td>
                    <td class="px-5 py-3 font-bold text-emerald-400">
                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        <div class="text-[10px] text-gray-500 font-normal">via {{ $order->payment_method }}</div>
                    </td>
                    <td class="px-5 py-3 text-center">
                        @php
                            $badgeColor = match($order->status) {
                                'UNPAID' => 'gray',
                                'AWAITING_SHIPMENT' => 'amber',
                                'AWAITING_COLLECTION' => 'blue',
                                'IN_TRANSIT' => 'indigo',
                                'DELIVERED', 'COMPLETED' => 'emerald',
                                'CANCELLED' => 'red',
                                default => 'gray'
                            };
                        @endphp
                        <span class="inline-flex px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-{{ $badgeColor }}-500/10 text-{{ $badgeColor }}-400 border border-{{ $badgeColor }}-500/20">
                            {{ str_replace('_', ' ', $order->status) }}
                        </span>
                        
                        @if($order->is_stock_deducted)
                        <div class="text-[9px] text-emerald-500/80 mt-1"><i data-lucide="check" class="w-3 h-3 inline"></i> Stok Terpotong</div>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-right">
                        @if($order->status === 'AWAITING_SHIPMENT')
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('digital-marketing.orders.print-resi', $order->id) }}" target="_blank" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white rounded text-xs font-bold transition-colors border border-gray-600 flex items-center gap-1.5">
                                    <i data-lucide="printer" class="w-3 h-3"></i> Cetak AWB
                                </a>
                                <form method="POST" action="{{ route('digital-marketing.orders.confirm-shipment', $order->id) }}" class="m-0" onsubmit="return confirm('Konfirmasi bahwa barang ini siap dikirim/di-pickup kurir?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded text-xs font-bold shadow-sm transition-colors flex items-center gap-1.5">
                                        <i data-lucide="truck" class="w-3 h-3"></i> Confirm RTS
                                    </button>
                                </form>
                            </div>
                        @elseif($order->shipping_document_url)
                            <a href="{{ $order->shipping_document_url }}" target="_blank" class="px-3 py-1.5 bg-gray-700/50 hover:bg-gray-600 text-gray-300 rounded text-xs font-medium transition-colors border border-gray-600 flex items-center gap-1.5 inline-flex">
                                <i data-lucide="file-text" class="w-3 h-3"></i> Lihat AWB
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12 text-gray-500">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="inbox" class="w-8 h-8 opacity-20"></i>
                            <span>Belum ada pesanan TikTok masuk.</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-4 border-t border-gray-700/50">
        {{ $orders->links() }}
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'E-Commerce Orders')
@section('breadcrumb', 'Orders')

@section('content')
<div x-data="{
    showModal: false,
    orderData: { id: '', order_number: '', current_status: '', receipt_number: '' },
    openStatusModal(order) {
        this.orderData = {
            id: order.id,
            order_number: order.order_number,
            current_status: order.status,
            receipt_number: order.receipt_number || ''
        };
        this.showModal = true;
    }
}">
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 auto-shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 flex items-center gap-3">
        <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Header & Stats --}}
    <div class="flex flex-col md:flex-row gap-6 mb-6">
        <div class="bg-gray-800 border-l-4 border-indigo-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm flex-1">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
                    <i data-lucide="shopping-bag" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Total Pesanan</div>
                    <div class="text-2xl font-black text-white">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 border-l-4 border-amber-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm flex-1">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                    <i data-lucide="clock" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Menunggu/Pending</div>
                    <div class="text-2xl font-black text-amber-500">{{ number_format($stats['pending']) }}</div>
                </div>
            </div>
        </div>
        <div class="bg-gray-800 border-l-4 border-emerald-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm flex-1">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
                    <i data-lucide="truck" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Sedang Dikirim</div>
                    <div class="text-2xl font-black text-emerald-400">{{ number_format($stats['shipped']) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions & Filters --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <form id="filter-form" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <h2 class="text-lg font-bold text-white mr-2 flex items-center gap-2">
                    <i data-lucide="globe" class="w-5 h-5 text-indigo-400"></i> E-Commerce Orders
                </h2>
                
                <select name="platform" onchange="document.getElementById('filter-form').submit()"
                        class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="all">Semua Platform</option>
                    <option value="Shopee" {{ $platform == 'Shopee' ? 'selected' : '' }}>Shopee</option>
                    <option value="Tokopedia" {{ $platform == 'Tokopedia' ? 'selected' : '' }}>Tokopedia</option>
                    <option value="TikTok Shop" {{ $platform == 'TikTok Shop' ? 'selected' : '' }}>TikTok Shop</option>
                </select>
                
                <select name="status" onchange="document.getElementById('filter-form').submit()"
                        class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="all">Semua Status</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="ready_to_ship" {{ $status == 'ready_to_ship' ? 'selected' : '' }}>Siap Kirim</option>
                    <option value="shipped" {{ $status == 'shipped' ? 'selected' : '' }}>Dikirim</option>
                    <option value="delivered" {{ $status == 'delivered' ? 'selected' : '' }}>Selesai/Terkirim</option>
                </select>
            </form>

            <form action="{{ route('orders.sync') }}" method="POST" class="w-full lg:w-auto m-0" 
                  x-data="{ isSyncing: false }" 
                  @submit="isSyncing = true">
                @csrf
                <button type="submit" 
                        :disabled="isSyncing"
                        :class="isSyncing ? 'opacity-75 cursor-not-allowed hidden bg-indigo-500' : 'bg-indigo-600 hover:bg-indigo-500 shadow-sm shadow-indigo-600/20 active:scale-95'"
                        class="w-full lg:w-auto flex items-center justify-center gap-2 px-4 py-2 text-white rounded-lg text-sm font-bold transition-all">
                    <i data-lucide="refresh-cw" x-show="!isSyncing" class="w-4 h-4"></i>
                    <i data-lucide="loader-2" x-show="isSyncing" x-cloak class="w-4 h-4 animate-spin hidden" style="display: none;"></i>
                    <span x-text="isSyncing ? 'Menghubungi API...' : 'Sync API (Simulasi)'"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- Order Table --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">Platform & Order No</th>
                        <th class="px-5 py-4 font-medium">Tanggal</th>
                        <th class="px-5 py-4 font-medium">Pelanggan</th>
                        <th class="px-5 py-4 font-medium text-right">Total Nominal</th>
                        <th class="px-5 py-4 font-medium">Pengiriman</th>
                        <th class="px-5 py-4 font-medium text-center">Status</th>
                        <th class="px-5 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-700/20 transition-colors group">
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-white mb-0.5">{{ $order->order_number }}</span>
                                <span class="text-xs text-indigo-400 flex items-center gap-1"><i data-lucide="shopping-bag" class="w-3 h-3"></i> {{ $order->platform }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-300 font-medium">
                            {{ $order->order_date->format('d M Y H:i') }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-200 mb-0.5">{{ $order->customer_name }}</span>
                                <span class="text-xs text-gray-500">{{ $order->customer_phone ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <span class="font-bold text-emerald-400">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            <div class="text-[10px] text-gray-500 mt-0.5">{{ $order->items->count() }} Item</div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span class="text-gray-300 font-bold text-xs">{{ $order->shipping_courier }}</span>
                                <span class="text-xs text-indigo-300 font-mono mt-0.5">{{ $order->receipt_number ?? 'Belum ada resi' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if($order->status == 'pending')
                                <span class="px-2 py-1 rounded-lg text-xs font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">Pending</span>
                            @elseif($order->status == 'ready_to_ship')
                                <span class="px-2 py-1 rounded-lg text-xs font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">Siap Kirim</span>
                            @elseif($order->status == 'shipped')
                                <span class="px-2 py-1 rounded-lg text-xs font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Dikirim</span>
                            @elseif($order->status == 'delivered')
                                <span class="px-2 py-1 rounded-lg text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Terkirim</span>
                            @else
                                <span class="px-2 py-1 rounded-lg text-xs font-bold bg-red-500/10 text-red-500 border border-red-500/20">Batal</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="openStatusModal({{ json_encode($order) }})" class="p-2 rounded-lg text-white bg-indigo-600 hover:bg-indigo-500 transition-colors shadow-sm focus:ring-2 focus:ring-indigo-500 outline-none" title="Update Status & Resi">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-12 text-gray-500">
                        <i data-lucide="package-search" class="w-10 h-10 mx-auto mb-3 text-gray-600"></i>
                        Belum ada data pesanan E-Commerce. Klik Sync API untuk simulasi data.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/30">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    {{-- Modal Update Status --}}
    <div x-show="showModal" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showModal = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="package-check" class="w-5 h-5 text-indigo-400"></i> Update Status Pesanan
                </h3>
                <button type="button" @click="showModal = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" :action="'/orders/' + orderData.id + '/status'">
                @csrf @method('PUT')
                <div class="p-5 flex flex-col gap-4">
                    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-3 text-sm flex justify-between items-center mb-1">
                        <span class="text-gray-400 uppercase font-bold text-xs tracking-wider">No. Pesanan</span>
                        <span class="text-white font-mono font-bold" x-text="orderData.order_number"></span>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Status Pesanan</label>
                        <select name="status" x-model="orderData.current_status" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="pending">Pending / Menunggu Pembayaran</option>
                            <option value="ready_to_ship">Siap Kirim (Pick-up)</option>
                            <option value="shipped">Sedang Dikirim (Input Resi)</option>
                            <option value="delivered">Terkirim / Selesai</option>
                            <option value="cancelled">Dibatalkan</option>
                        </select>
                    </div>

                    <div class="space-y-1.5" x-show="['ready_to_ship', 'shipped', 'delivered'].includes(orderData.current_status)">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Nomor Resi / AWB</label>
                        <input type="text" name="receipt_number" x-model="orderData.receipt_number" class="w-full bg-gray-900 border border-gray-700 text-white font-mono rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" placeholder="Masukkan nomor resi...">
                        <p class="text-[10px] text-gray-500 italic mt-1">Stok akan otomatis dikurangi jika status berubah menjadi 'Dikirim'.</p>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-900/20 active:scale-95 flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

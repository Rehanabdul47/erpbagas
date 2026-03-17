@extends('layouts.app')

@section('title', 'E-Commerce Orders')
@section('breadcrumb', 'Orders')

@section('content')
<div x-data="{
    showModal: false,
    showUploadModal: false,
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

    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 flex items-start gap-3 shadow-sm">
        <i data-lucide="alert-circle" class="w-5 h-5 auto-shrink-0 mt-0.5"></i>
        <ul class="text-sm list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
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
                    @foreach($activePlatforms as $pms)
                        <option value="{{ $pms->name }}" {{ $platform == $pms->name ? 'selected' : '' }}>{{ $pms->name }}</option>
                    @endforeach
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

            <button type="button" 
                    @click="showUploadModal = true"
                    class="w-full lg:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 shadow-sm shadow-indigo-600/20 active:scale-95 text-white rounded-lg text-sm font-bold transition-all">
                <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                <span>Upload Order E-Commerce</span>
            </button>
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
                                <a href="{{ route('orders.show', $order->id) }}" class="font-bold text-white hover:text-indigo-400 transition-colors mb-0.5">{{ $order->order_number }}</a>
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
                                <a href="{{ route('orders.show', $order->id) }}" class="p-2 rounded-lg text-white bg-gray-700 hover:bg-gray-600 transition-colors shadow-sm" title="Lihat Detail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <button type="button" @click="openStatusModal({{ json_encode($order) }})" class="p-2 rounded-lg text-white bg-indigo-600 hover:bg-indigo-500 transition-colors shadow-sm focus:ring-2 focus:ring-indigo-500 outline-none" title="Update Status & Resi">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-12 text-gray-500">
                        <i data-lucide="package-search" class="w-10 h-10 mx-auto mb-3 text-gray-600"></i>
                        Belum ada data pesanan E-Commerce. Klik "Upload Order E-Commerce" untuk proses impor data.
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

    {{-- Modal Import Excel/CSV --}}
    <div x-show="showUploadModal" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showUploadModal = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col pt-1">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2 text-lg">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-500/30">
                        <i data-lucide="upload-cloud" class="w-4 h-4"></i>
                    </div>
                    Import Order E-Commerce
                </h3>
                <button type="button" @click="showUploadModal = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <form action="{{ route('orders.import') }}" method="POST" enctype="multipart/form-data"
                  x-data="{ fileSelected: false, fileName: '', isSubmitting: false }" 
                  @submit="isSubmitting = true">
                @csrf
                <div class="p-6">
                    <div class="mb-5 space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Pilih Platform Tujuan</label>
                        <select name="platform_id" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer">
                            <option value="">-- Pilih E-Commerce --</option>
                            @foreach($activePlatforms as $pms)
                                <option value="{{ $pms->id }}">{{ $pms->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1 mb-1.5 block">File Laporan Order</label>
                        <label for="order_file" 
                               class="flex flex-col items-center justify-center w-full h-40 border-2 border-gray-600 border-dashed rounded-xl cursor-pointer bg-gray-900/30 hover:bg-gray-800/80 hover:border-indigo-500/50 transition-all duration-300 group"
                               :class="fileSelected ? 'border-indigo-500 bg-indigo-500/5' : ''">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                
                                <template x-if="!fileSelected">
                                    <div class="flex flex-col items-center">
                                        <i data-lucide="upload" class="w-8 h-8 text-gray-500 group-hover:text-indigo-400 transition-colors mb-3"></i>
                                        <p class="mb-1 text-sm text-gray-300 font-bold"><span class="text-indigo-400">Klik untuk upload</span></p>
                                        <p class="text-xs text-gray-500 uppercase tracking-widest font-mono">XLSX, XLS, CSV (Max 10MB)</p>
                                    </div>
                                </template>

                                <template x-if="fileSelected">
                                    <div class="flex flex-col items-center text-center">
                                        <i data-lucide="file-spreadsheet" class="w-10 h-10 text-emerald-400 mb-2"></i>
                                        <p class="text-sm font-bold text-white max-w-[250px] truncate" x-text="fileName"></p>
                                        <p class="mt-2 text-xs text-gray-400 hover:text-red-400 transition-colors" @click.prevent="fileSelected = false; fileName = ''; $refs.fileInput.value = ''">
                                            Batal / Pilih Ulang
                                        </p>
                                    </div>
                                </template>

                            </div>
                            <input id="order_file" name="order_file" type="file" class="hidden" x-ref="fileInput" required
                                   accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                                   @change="fileSelected = $event.target.files.length > 0; fileName = $event.target.files.length > 0 ? $event.target.files[0].name : ''" />
                        </label>
                    </div>

                    {{-- Status Mapping Info --}}
                    <div class="mt-4 p-4 bg-indigo-500/5 border border-indigo-500/20 rounded-xl">
                        <div class="flex items-center gap-2 mb-3 text-indigo-400 border-b border-indigo-500/10 pb-2">
                            <i data-lucide="info" class="w-4 h-4"></i>
                            <span class="text-[10px] font-bold uppercase tracking-widest pl-1">Panduan Auto-Mapping Status</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-orange-500/10 rounded-lg border border-orange-500/20 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                    <span class="text-orange-200 text-[10px] font-black tracking-wider uppercase">Shopee</span>
                                </div>
                                <ul class="text-[10px] text-gray-400 space-y-1 pl-1">
                                    <li class="flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3 h-3 text-orange-500/50"></i> Belum Bayar, Perlu Dikirim</li>
                                    <li class="flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3 h-3 text-orange-500/50"></i> Dikirim, Selesai, Pengembalian</li>
                                </ul>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center gap-1.5 px-2 py-1 bg-gray-600/20 rounded-lg border border-gray-500/20 w-fit">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                    <span class="text-white text-[10px] font-black tracking-wider uppercase">TikTok Shop</span>
                                </div>
                                <ul class="text-[10px] text-gray-400 space-y-1 pl-1">
                                    <li class="flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3 h-3 text-gray-500"></i> Dalam Proses, Perlu dikirim</li>
                                    <li class="flex items-center gap-1.5"><i data-lucide="chevron-right" class="w-3 h-3 text-gray-500"></i> Dikirim, Selesai, Dibatalkan</li>
                                </ul>
                            </div>
                        </div>
                        <p class="mt-3 text-[9px] text-indigo-400/70 italic border-t border-indigo-500/10 pt-2 text-center">
                            *Sistem akan otomatis mengonversi status di atas ke status internal sistem.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showUploadModal = false" class="px-5 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Tutup</button>
                    <button type="submit" 
                            :disabled="!fileSelected || isSubmitting"
                            :class="!fileSelected || isSubmitting ? 'bg-indigo-600/50 cursor-not-allowed text-white/70' : 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/20 active:scale-95 text-white'"
                            class="px-6 py-2 rounded-xl font-bold transition-all text-sm flex items-center gap-2">
                        <i data-lucide="hard-drive-upload" x-show="!isSubmitting" class="w-4 h-4"></i>
                        <i data-lucide="loader-2" x-show="isSubmitting" x-cloak class="w-4 h-4 animate-spin" style="display: none;"></i>
                        <span x-text="isSubmitting ? 'Mengunggah...' : 'Upload & Proses'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

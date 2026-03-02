@extends('layouts.app')

@section('title', 'Point of Sale')
@section('breadcrumb', 'Point of Sale')

@section('content')
<div x-data="posSystem()" class="flex flex-col gap-4 md:gap-6 lg:h-[calc(100vh-110px)]">
    
    {{-- Top Navigation Tabs --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm shrink-0 overflow-x-auto hide-scrollbar">
        <div class="flex w-max">
            <button @click="activeTab = 'kasir'" :class="activeTab === 'kasir' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800/80' : 'text-gray-400 hover:text-white hover:bg-gray-800/50 border-b-2 border-transparent'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
                <i data-lucide="monitor" class="w-4 h-4"></i> Kasir (POS)
            </button>
            <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800/80' : 'text-gray-400 hover:text-white hover:bg-gray-800/50 border-b-2 border-transparent'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4"></i> Riwayat Transaksi
            </button>
        </div>
    </div>

    {{-- Main POS View --}}
    <div x-show="activeTab === 'kasir'" class="flex-1 flex flex-col lg:flex-row gap-4 md:gap-6 min-h-0">
        
        {{-- Left Section: Products Catalog --}}
        <div class="flex-1 bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col overflow-hidden min-h-[600px] lg:min-h-0">
            <!-- Search & Filter Header -->
            <div class="p-4 border-b border-gray-700/50 flex flex-col sm:flex-row gap-3 bg-gray-800/50 shrink-0">
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="text" x-model="searchQuery" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500 focus:bg-gray-900" placeholder="Cari nama produk atau barcode...">
                </div>
                <div class="relative w-full sm:w-48 shrink-0">
                    <select x-model="selectedCategory" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg pl-3 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none cursor-pointer focus:bg-gray-900">
                        <option value="">Semua Kategori</option>
                        @foreach($products->pluck('category.name')->unique() as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                </div>
            </div>
            
            <!-- Products Grid Container -->
            <div class="flex-1 overflow-y-auto p-4 scrollbar-thin scrollbar-thumb-indigo-500/20 hover:scrollbar-thumb-indigo-500/50 scrollbar-track-transparent bg-gray-900/20">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 auto-rows-max">
                    @foreach($products as $p)
                    <div x-show="matchProduct('{{ strtolower($p->name) }}', '{{ strtolower($p->sku) }}', '{{ $p->category->name }}')" 
                         @click="addToCart({{ json_encode($p) }})" 
                         class="bg-gray-800 border border-gray-700/50 rounded-xl p-3 cursor-pointer hover:border-indigo-500/50 hover:bg-indigo-500/5 hover:-translate-y-1 transition-all group flex flex-col h-full shadow-sm hover:shadow-indigo-500/10 active:scale-95">
                        
                        <!-- Image Container -->
                        <div class="aspect-square bg-gray-900 rounded-lg overflow-hidden flex items-center justify-center mb-3 relative group-hover:shadow-inner">
                            @if($p->image)
                                <img src="{{ Str::startsWith($p->image, 'http') ? $p->image : asset($p->image) }}" alt="{{ $p->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <i data-lucide="package" class="w-10 h-10 text-gray-600 group-hover:text-indigo-400 transition-colors"></i>
                            @endif
                            
                            <!-- Stock Overlay badge -->
                            <div class="absolute top-2 right-2 px-1.5 py-0.5 rounded text-[10px] font-bold shadow-sm backdrop-blur-md {{ $p->stock <= 5 ? 'bg-red-500/80 text-white' : 'bg-gray-900/60 text-gray-200' }}">
                                {{ $p->stock }} {{ $p->unit ?? 'pcs' }}
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="flex-1 flex flex-col justify-between">
                            <h4 class="font-bold text-gray-200 text-sm mb-2 line-clamp-2 leading-tight group-hover:text-indigo-400 transition-colors" title="{{ $p->name }}">{{ $p->name }}</h4>
                            <div class="flex justify-between items-end mt-auto">
                                <div class="font-bold text-indigo-400 tracking-tight">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Empty State -->
                <div x-show="!hasMatchingProducts()" x-cloak class="h-full flex flex-col items-center justify-center text-gray-500 py-12">
                    <i data-lucide="search-x" class="w-16 h-16 opacity-50 mb-4"></i>
                    <p class="text-lg font-medium">Produk tidak ditemukan</p>
                    <p class="text-sm">Coba kata kunci pencarian yang lain.</p>
                </div>
            </div>
        </div>

        {{-- Right Section: Shopping Cart Sidebar --}}
        <div class="w-full lg:w-[400px] xl:w-[420px] bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col overflow-hidden shrink-0 min-h-[600px] lg:min-h-0">
            <!-- Cart Header -->
            <div class="p-4 border-b border-gray-700/50 shrink-0 bg-gray-800">
                <h4 class="font-bold text-lg text-white flex items-center justify-between m-0">
                    <div class="flex items-center gap-2">
                        <i data-lucide="shopping-cart" class="w-5 h-5 text-indigo-400"></i> Detail Pesanan
                    </div>
                    <span class="bg-indigo-500/20 shadow-inner px-2 py-1 rounded-md text-indigo-400 text-xs font-bold border border-indigo-500/20" x-text="totalItems + ' Item'"></span>
                </h4>
            </div>
            
            <!-- Customer Selection -->
            <div class="p-4 border-b border-gray-700/50 shrink-0 bg-gray-900/30 flex gap-2">
                <div class="relative flex-1">
                    <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <select x-model="customerId" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none shadow-inner">
                        <option value="" disabled {{ $customers->isEmpty() ? 'selected' : '' }}>Pilih Pelanggan</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                </div>
                <button @click="modals.customer = true" class="px-3 bg-gray-700 hover:bg-gray-600 border border-gray-600 text-white rounded-lg transition-colors flex items-center justify-center shadow-sm" title="Tambah Customer Baru">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto p-4 scrollbar-thin scrollbar-thumb-indigo-500/20 hover:scrollbar-thumb-indigo-500/50 scrollbar-track-transparent bg-gray-900/10">
                
                <template x-if="cart.length > 0">
                    <div class="space-y-3">
                        <template x-for="item in cart" :key="item.id">
                            <div class="bg-gray-800/80 border border-gray-700/50 rounded-xl p-3.5 flex items-center gap-4 shadow-sm relative group overflow-hidden hover:border-indigo-500/30 transition-all duration-300">
                                <!-- Remove Action -->
                                <button @click="removeFromCart(item.id)" 
                                        class="absolute right-0 top-0 bottom-0 px-4 bg-red-500/10 hover:bg-red-500 text-red-500 hover:text-white opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-center justify-center border-l border-red-500/20 z-10" 
                                        title="Hapus Item">
                                    <i data-lucide="trash-2" class="w-5 h-5"></i>
                                </button>
                                
                                <!-- Item Meta -->
                                <div class="flex-1 min-w-0 group-hover:pr-10 transition-all duration-300">
                                    <h5 class="text-[13px] font-black text-gray-100 truncate mb-1 leading-none" x-text="item.name"></h5>
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-gray-900 border border-gray-700 text-gray-400 uppercase" x-text="formatRupiah(item.price)"></span>
                                        <span class="text-[10px] text-gray-500 font-medium">/ unit</span>
                                    </div>
                                </div>

                                <!-- Qty & Price -->
                                <div class="flex flex-col items-end gap-2.5 shrink-0 group-hover:translate-x-[-10px] transition-all duration-300">
                                    <div class="font-black text-[13px] text-indigo-400 tracking-tight" x-text="'Rp ' + formatRupiah(item.price * item.quantity)"></div>
                                    <div class="flex items-center bg-gray-950/50 border border-gray-700 rounded-lg overflow-hidden h-8 shadow-inner">
                                        <button @click="updateQty(item.id, -1)" 
                                                class="w-8 h-8 flex items-center justify-center hover:bg-red-500/20 hover:text-red-400 text-gray-500 transition-colors disabled:opacity-20" 
                                                :disabled="item.quantity <= 1">
                                            <i data-lucide="minus" class="w-3 h-3"></i>
                                        </button>
                                        <div class="min-w-[28px] text-center text-xs font-black text-white" x-text="item.quantity"></div>
                                        <button @click="updateQty(item.id, 1)" 
                                                class="w-8 h-8 flex items-center justify-center hover:bg-emerald-500/20 hover:text-emerald-400 text-gray-500 transition-colors disabled:opacity-20" 
                                                :disabled="item.quantity >= item.stock">
                                            <i data-lucide="plus" class="w-3 h-3"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="cart.length === 0">
                    <div class="h-full flex flex-col items-center justify-center text-gray-500 opacity-80 min-h-[150px]">
                        <i data-lucide="shopping-bag" class="w-14 h-14 mb-4 opacity-50"></i>
                        <p class="text-sm font-medium">Keranjang masih kosong</p>
                        <p class="text-xs text-center mt-2 max-w-[200px] opacity-70">Klik produk di katalog untuk menambah pesanan.</p>
                    </div>
                </template>
            </div>

            <!-- Discount / Promo Section -->
            <div class="p-4 border-t border-gray-700/50 shrink-0 bg-gray-900/30">
                <label class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 block">Kode Promo / Redeem</label>
                <div class="flex gap-2 relative">
                    <input type="text" x-model="redeemCodeInput" @keyup.enter="applyRedeemCode()" class="flex-1 bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all uppercase placeholder:normal-case font-mono font-bold shadow-inner" placeholder="Masukkan kode">
                    <template x-if="appliedRedeemCode">
                        <button @click="removeRedeemCode()" class="absolute right-[85px] top-1/2 -translate-y-1/2 text-gray-500 hover:text-red-500 bg-gray-900 py-1 pl-1 pr-2">
                             <i data-lucide="x-circle" class="w-4 h-4"></i>
                        </button>
                    </template>
                    <button @click="applyRedeemCode()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors border border-gray-600 shrink-0 shadow-sm active:scale-95 disabled:opacity-50 disabled:active:scale-100">Cek</button>
                </div>
                <!-- Validation msg -->
                <div x-show="redeemMsg" x-cloak class="text-xs font-medium mt-2 flex items-center gap-1.5" :class="redeemStatus === 'success' ? 'text-emerald-400' : (redeemStatus === 'error' ? 'text-red-400' : 'text-gray-400')">
                    <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i> <span x-text="redeemMsg"></span>
                </div>
            </div>

            <!-- Total Processing block -->
            <div class="p-5 bg-gray-800 border-t border-gray-700/50 shrink-0 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.1)] z-20">
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm text-gray-400 font-medium">
                        <span>Subtotal</span>
                        <span x-text="'Rp ' + formatRupiah(subtotal)"></span>
                    </div>
                    
                    <template x-if="appliedRedeemCode">
                        <div class="flex justify-between text-sm text-emerald-400 font-medium items-center">
                            <span class="flex items-center gap-2">
                                Diskon 
                                <span class="bg-emerald-500/20 text-emerald-400 text-[10px] font-mono px-2 py-0.5 rounded border border-emerald-500/30" x-text="appliedRedeemCode.code"></span>
                            </span>
                            <span x-text="'-Rp ' + formatRupiah(discountAmount)"></span>
                        </div>
                    </template>
                    
                    <div class="border-t border-gray-700/50 pt-3 mt-1 flex justify-between items-end">
                        <span class="text-white font-bold text-lg">Total</span>
                        <span class="text-2xl font-black text-indigo-400 tracking-tight" x-text="'Rp ' + formatRupiah(finalTotal)"></span>
                    </div>
                </div>
                
                <button @click="openCheckoutModal" :disabled="cart.length === 0" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 disabled:bg-gray-700 disabled:text-gray-500 disabled:shadow-none text-white rounded-xl text-base font-bold transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98] flex justify-center items-center gap-2">
                    <i data-lucide="credit-card" class="w-5 h-5"></i> Lanjut Pembayaran
                </button>
            </div>
        </div>
    </div>

    {{-- Tab: History View --}}
    <div x-show="activeTab === 'history'" x-cloak style="display: none;" class="flex-1 bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col min-h-[600px] lg:min-h-0 overflow-hidden">
        <div class="p-5 border-b border-gray-700/50 flex flex-col sm:flex-row justify-between sm:items-center gap-4 bg-gray-800 shrink-0">
            <h3 class="m-0 text-xl font-bold text-white flex items-center gap-2">
                <i data-lucide="receipt" class="w-5 h-5 text-indigo-400"></i> Riwayat Transaksi Kasir
            </h3>
            <form method="GET" action="{{ route('pos.index') }}" class="flex items-center gap-2">
                <div class="relative w-full sm:w-48 shrink-0">
                    <select name="branch_id" onchange="this.form.submit()" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-3 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none cursor-pointer shadow-inner">
                        <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                </div>
            </form>
        </div>
        
        <div class="flex-1 overflow-y-auto p-5 scrollbar-thin scrollbar-thumb-indigo-500/20 hover:scrollbar-thumb-indigo-500/50 scrollbar-track-transparent bg-gray-900/10">
            <div class="border border-gray-700/50 rounded-xl overflow-hidden shadow-sm bg-gray-800">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/80 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                            <tr>
                                <th class="px-5 py-3 font-medium">Invoice</th>
                                <th class="px-5 py-3 font-medium">Waktu</th>
                                <th class="px-5 py-3 font-medium">Customer</th>
                                <th class="px-5 py-3 font-medium">Cabang</th>
                                <th class="px-5 py-3 font-medium">Item Terjual</th>
                                <th class="px-5 py-3 font-medium">Total Amount</th>
                                <th class="px-5 py-3 font-medium">Metode</th>
                                <th class="px-5 py-3 font-medium text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($posHistory as $sale)
                            <tr class="hover:bg-indigo-500/5 transition-colors group cursor-pointer">
                                <td class="px-5 py-3 font-bold text-indigo-400 font-mono text-sm tracking-tight">#{{ $sale->invoice_no }}</td>
                                <td class="px-5 py-3 text-gray-400 text-xs">{{ $sale->created_at->format('d M Y, H:i') }}</td>
                                <td class="px-5 py-3 font-bold text-white">{{ $sale->customer->name }}</td>
                                <td class="px-5 py-3 text-gray-400 text-xs flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3 text-indigo-500/50"></i>
                                    {{ $sale->branch ? $sale->branch->name : 'Pusat' }}
                                </td>
                                <td class="px-5 py-3 text-gray-300"><span class="bg-gray-700 px-2 py-0.5 rounded text-xs">{{ $sale->items->count() }} Item</span></td>
                                <td class="px-5 py-3 font-bold text-white">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 rounded border text-[10px] font-bold {{ str_contains(strtolower($sale->notes), 'tunai') ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' }}">
                                        {{ str_replace('POS Transaction via ', '', $sale->notes) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <button @click="printInvoice({{ $sale->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors opacity-50 group-hover:opacity-100" title="Cetak Struk">
                                        <i data-lucide="printer" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-16 text-gray-500">
                                    <div class="flex flex-col items-center gap-3">
                                        <i data-lucide="inbox" class="w-12 h-12 opacity-30 text-gray-400"></i>
                                        <span class="text-base font-medium">Belum ada riwayat transaksi POS</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Checkout Modal --}}
    <div x-show="modals.checkout" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.checkout = false" class="bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col pointer-events-auto transform transition-all">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between shrink-0 bg-gray-800/80 backdrop-blur-md">
                <h3 class="font-bold text-white flex items-center gap-2 text-lg">
                    <i data-lucide="credit-card" class="w-5 h-5 text-indigo-400"></i> Selesaikan Pembayaran
                </h3>
                <button @click="modals.checkout = false" class="p-1 text-gray-400 hover:text-white bg-gray-900/50 hover:bg-gray-700 rounded-lg transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <div class="p-5 flex flex-col gap-6 bg-gray-800">
                <!-- Total Display -->
                <div class="bg-gray-900 border border-gray-700 rounded-xl p-6 text-center shadow-inner relative overflow-hidden">
                    <div class="absolute inset-0 bg-indigo-500/5 pointer-events-none"></div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1 relative z-10">Total Tagihan</div>
                    <div class="text-4xl font-black text-indigo-400 tracking-tighter relative z-10" x-text="'Rp ' + formatRupiah(finalTotal)"></div>
                </div>
                
                <!-- Payment Method Selector -->
                <div class="space-y-2">
                    <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="paymentMethod = 'Tunai'; calculateChange()" :class="paymentMethod === 'Tunai' ? 'bg-indigo-600 text-white border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-gray-900/50 text-gray-300 border-gray-700 hover:bg-gray-800 hover:text-white hover:border-gray-600'" class="px-4 py-3 rounded-xl border text-sm font-bold transition-all flex items-center justify-center gap-2">
                            <i data-lucide="banknote" class="w-4 h-4"></i> Tunai (Cash)
                        </button>
                        <button type="button" @click="paymentMethod = 'Transfer'; calculateChange()" :class="paymentMethod === 'Transfer' ? 'bg-indigo-600 text-white border-indigo-500 ring-2 ring-indigo-500/20' : 'bg-gray-900/50 text-gray-300 border-gray-700 hover:bg-gray-800 hover:text-white hover:border-gray-600'" class="px-4 py-3 rounded-xl border text-sm font-bold transition-all flex items-center justify-center gap-2">
                            <i data-lucide="smartphone" class="w-4 h-4"></i> QRIS / Transfer
                        </button>
                    </div>
                </div>
                
                <!-- Cash Input (only if Tunai) -->
                <div x-show="paymentMethod === 'Tunai'" x-collapse>
                    <div class="space-y-3 pt-2">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Nominal Diterima</label>
                            <input type="number" x-model.number="cashReceived" @input="calculateChange()" class="w-full bg-gray-900 border-2 border-gray-600 text-white rounded-xl px-4 py-3 text-xl font-bold font-mono text-right focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all placeholder-gray-600" placeholder="0">
                        </div>
                        
                        <!-- Quick Cash Buttons -->
                        <div class="grid grid-cols-4 gap-2">
                            <button @click="quickCash(10000)" class="py-2 bg-gray-700/50 hover:bg-gray-600 border border-gray-600 rounded-lg text-xs font-bold text-gray-200 transition-colors">10rb</button>
                            <button @click="quickCash(20000)" class="py-2 bg-gray-700/50 hover:bg-gray-600 border border-gray-600 rounded-lg text-xs font-bold text-gray-200 transition-colors">20rb</button>
                            <button @click="quickCash(50000)" class="py-2 bg-gray-700/50 hover:bg-gray-600 border border-gray-600 rounded-lg text-xs font-bold text-gray-200 transition-colors">50rb</button>
                            <button @click="quickCash(100000)" class="py-2 bg-gray-700/50 hover:bg-gray-600 border border-gray-600 rounded-lg text-xs font-bold text-gray-200 transition-colors">100rb</button>
                        </div>

                        <!-- Change Amount Display -->
                        <div class="p-4 rounded-xl flex justify-between items-center border transition-colors duration-300 mt-4 shadow-inner" :class="cashChange >= 0 ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-red-500/10 border-red-500/30'">
                            <span class="text-sm font-bold uppercase tracking-wider" :class="cashChange >= 0 ? 'text-emerald-500' : 'text-red-500'" x-text="cashChange >= 0 ? 'Kembalian' : 'Kurang'"></span>
                            <span class="text-xl font-black font-mono tracking-tight" :class="cashChange >= 0 ? 'text-emerald-400' : 'text-red-400'" x-text="'Rp ' + formatRupiah(Math.abs(cashChange))"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="p-5 border-t border-gray-700/50 bg-gray-900 shrink-0">
                <button @click="processCheckout()" :disabled="processing || (paymentMethod === 'Tunai' && cashChange < 0)" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-500 disabled:bg-gray-700 disabled:text-gray-500 disabled:cursor-not-allowed text-white rounded-xl text-base font-bold transition-all shadow-lg shadow-indigo-600/20 flex justify-center items-center gap-2 active:scale-[0.98]">
                    <template x-if="processing">
                        <i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i>
                    </template>
                    <template x-if="!processing">
                        <i data-lucide="zap" class="w-5 h-5"></i>    
                    </template>
                    <span x-text="processing ? 'Memproses...' : 'Selesaikan Transaksi'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- Success Modal --}}
    <div x-show="modals.success" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/90 backdrop-blur-md z-[200] flex items-center justify-center p-4 transition-opacity">
        <div class="bg-gray-800 border-[0.5px] border-b-4 border-gray-700 border-b-emerald-500 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col items-center text-center p-8 transform transition-all scale-100 relative">
            
            <!-- Confetti abstract shapes background -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
                <div class="absolute top-10 left-10 w-4 h-4 rounded-full bg-emerald-500"></div>
                <div class="absolute bottom-20 right-10 w-3 h-3 rounded-full bg-indigo-500"></div>
                <div class="absolute top-20 right-20 w-4 h-4 rotate-45 bg-amber-500"></div>
            </div>

            <div class="w-24 h-24 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-6 shadow-[0_0_30px_rgba(16,185,129,0.3)] border border-emerald-500/50 z-10">
                <i data-lucide="check-circle" class="w-12 h-12"></i>
            </div>
            <h2 class="text-2xl font-black text-white mb-2 tracking-tight z-10">Transaksi Berhasil!</h2>
            <p class="text-gray-400 text-sm mb-8 z-10">
                Invoice <span class="font-bold text-indigo-400 font-mono tracking-wider px-2 py-0.5 bg-gray-900 rounded inline-block" x-text="successInvoice"></span> telah tercatat disistem.
            </p>
            
            <div class="grid grid-cols-2 gap-3 w-full z-10">
                <button @click="printInvoice(successSaleId)" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 border border-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg flex items-center justify-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak Struk
                </button>
                <button @click="window.location.reload()" class="w-full py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm font-bold transition-all border border-gray-600">
                    Selesai
                </button>
            </div>
        </div>
    </div>

    {{-- Add Customer Modal --}}
    <div x-show="modals.customer" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.customer = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="user-plus" class="w-5 h-5 text-indigo-400"></i> Tambah Customer Baru
                </h3>
                <button @click="modals.customer = false" class="text-gray-400 hover:text-white transition-colors bg-gray-900/50 hover:bg-gray-700 p-1.5 rounded-lg"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>
            <form @submit.prevent="saveCustomer" class="p-5 flex flex-col gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" x-model="custForm.name" required class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600 focus:bg-gray-900" placeholder="Nama customer">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Email</label>
                    <input type="email" x-model="custForm.email" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600 focus:bg-gray-900" placeholder="customer@email.com">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">No. Telepon</label>
                    <input type="text" x-model="custForm.phone" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600 focus:bg-gray-900" placeholder="08xxxxxxxxxxx">
                </div>
                
                <div class="mt-2 flex gap-3">
                    <button type="button" @click="modals.customer = false" class="flex-1 py-2.5 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-lg text-sm font-bold transition-colors border border-gray-600">Batal</button>
                    <button type="submit" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-bold transition-colors shadow-lg shadow-indigo-600/20 active:scale-95" :disabled="savingCustomer" x-text="savingCustomer ? 'Menyimpan...' : 'Simpan Customer'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.has('branch_id') ? 'history' : 'kasir';

    Alpine.data('posSystem', () => ({
        activeTab: initialTab,
        searchQuery: '',
        selectedCategory: '',
        cart: [],
        customerId: '{{ $customers->first()->id ?? '' }}',
        
        // Checkout state
        paymentMethod: 'Tunai',
        cashReceived: '',
        cashChange: 0,
        processing: false,
        successInvoice: '',
        successSaleId: null,
        
        // Redeem / Promo state
        redeemCodeInput: '',
        appliedRedeemCode: null,
        redeemMsg: '',
        redeemStatus: '', // none, loading, success, error
        
        // Modals
        modals: {
            checkout: false,
            success: false,
            customer: false
        },
        
        // Customer Form
        custForm: { name: '', email: '', phone: '' },
        savingCustomer: false,

        init() {
            this.$watch('cart', () => this.calculateChange());
        },

        // --- Catalog Logic ---
        matchProduct(name, sku, cat) {
            const query = this.searchQuery.toLowerCase();
            const matchSearch = query === '' || name.includes(query) || sku.includes(query);
            const matchCat = this.selectedCategory === '' || cat === this.selectedCategory;
            return matchSearch && matchCat;
        },

        hasMatchingProducts() {
            if (this.searchQuery === '' && this.selectedCategory === '') return true;
            return true;
        },

        // --- Cart Logic ---
        addToCart(product) {
            const existing = this.cart.find(item => item.id === product.id);
            if (existing) {
                if (existing.quantity >= product.stock) return;
                existing.quantity++;
            } else {
                if (product.stock > 0) {
                    this.cart.push({
                        id: product.id,
                        name: product.name,
                        price: parseFloat(product.price),
                        quantity: 1,
                        stock: product.stock
                    });
                }
            }
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        removeFromCart(id) {
            this.cart = this.cart.filter(item => item.id !== id);
        },

        updateQty(id, delta) {
            const item = this.cart.find(i => i.id === id);
            if (item) {
                const newQty = item.quantity + delta;
                if (newQty > 0 && newQty <= item.stock) {
                    item.quantity = newQty;
                }
            }
        },

        get totalItems() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        get discountAmount() {
            if (!this.appliedRedeemCode) return 0;
            
            let amount = 0;
            let val = Math.abs(parseFloat(this.appliedRedeemCode.value));
            if (this.appliedRedeemCode.type === 'percentage') {
                amount = (this.subtotal * val) / 100;
            } else {
                amount = val;
            }
            return Math.min(amount, this.subtotal);
        },

        get finalTotal() {
            return Math.max(0, this.subtotal - this.discountAmount);
        },

        // --- Promo Logic ---
        applyRedeemCode() {
            if (!this.redeemCodeInput.trim()) return;
            
            this.redeemStatus = 'loading';
            this.redeemMsg = 'Memvalidasi kode...';

            fetch("{{ route('pos.apply-redeem-code') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ 
                    code: this.redeemCodeInput.trim().toUpperCase(), 
                    customer_id: this.customerId 
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.appliedRedeemCode = data.code;
                    this.redeemStatus = 'success';
                    this.redeemMsg = 'Kode berhasil diterapkan!';
                } else {
                    this.appliedRedeemCode = null;
                    this.redeemStatus = 'error';
                    this.redeemMsg = data.message || 'Kode tidak valid.';
                }
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
            })
            .catch(err => {
                this.appliedRedeemCode = null;
                this.redeemStatus = 'error';
                this.redeemMsg = 'Koneksi server terputus.';
            });
        },

        removeRedeemCode() {
            this.appliedRedeemCode = null;
            this.redeemCodeInput = '';
            this.redeemStatus = '';
            this.redeemMsg = '';
        },

        // --- Checkout Logic ---
        openCheckoutModal() {
            if (this.cart.length === 0) return;
            this.modals.checkout = true;
            this.cashReceived = ''; 
            this.calculateChange();
            setTimeout(() => {
                const input = document.querySelector('input[x-model\\.number="cashReceived"]');
                if(input && this.paymentMethod === 'Tunai') input.focus();
            }, 100);
        },

        quickCash(amount) {
            this.cashReceived = amount;
            this.calculateChange();
        },

        calculateChange() {
            const received = parseFloat(this.cashReceived) || 0;
            this.cashChange = received - this.finalTotal;
        },

        processCheckout() {
            if (this.paymentMethod === 'Tunai' && this.cashChange < 0) return;
            if (this.processing) return;

            this.processing = true;
            const received = parseFloat(this.cashReceived) || 0;

            const data = {
                customer_id: this.customerId,
                items: this.cart.map(item => ({ product_id: item.id, quantity: item.quantity })),
                payment_method: this.paymentMethod,
                amount_paid: this.paymentMethod === 'Tunai' ? received : this.finalTotal,
                redeem_code: this.appliedRedeemCode ? this.appliedRedeemCode.code : null,
                _token: "{{ csrf_token() }}"
            };

            fetch("{{ route('pos.checkout') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                this.processing = false;
                if (res.success) {
                    this.modals.checkout = false;
                    this.successInvoice = res.invoice_no;
                    this.successSaleId = res.sale_id;
                    this.modals.success = true;
                } else {
                    this.$dispatch('global-alert', {
                        type: 'error',
                        title: 'TRANSAKSI GAGAL',
                        message: res.message || 'Gagal memproses transaksi. Silakan cek stok atau input Anda.',
                        confirmText: 'MENGERTI'
                    });
                }
            })
            .catch(err => {
                this.processing = false;
                    this.$dispatch('global-alert', {
                        type: 'error',
                        title: 'KONEKSI TERPUTUS',
                        message: 'Terjadi kesalahan saat menghubungkan ke server. Silakan coba beberapa saat lagi.',
                        confirmText: 'TUTUP'
                    });
            });
        },

        printInvoice(saleId) {
            const url = `{{ route('pos.invoice', ':id') }}`.replace(':id', saleId);
            const win = window.open(url + '?print=true', '_blank');
            if (win) win.focus();
        },

        // --- Customer Logic ---
        saveCustomer() {
            if (!this.custForm.name) return;
            this.savingCustomer = true;

            fetch("{{ route('pos.customer.store') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: this.custForm.name,
                    email: this.custForm.email,
                    phone: this.custForm.phone,
                    _token: "{{ csrf_token() }}"
                })
            })
            .then(res => res.json())
            .then(res => {
                this.savingCustomer = false;
                if (res.success) {
                    const select = document.querySelector('select[x-model="customerId"]');
                    if (select) {
                        const option = new Option(res.customer.name, res.customer.id);
                        select.add(option);
                        this.customerId = res.customer.id;
                    }
                    this.modals.customer = false;
                    this.custForm = { name: '', email: '', phone: '' };
                } else {
                    this.$dispatch('global-alert', {
                        type: 'error',
                        title: 'GAGAL SIMPAN',
                        message: 'Gagal menyimpan data customer baru. Silakan periksa kelengkapan data.',
                        confirmText: 'KEMBALI'
                    });
                }
            })
            .catch(err => {
                this.savingCustomer = false;
                alert('Terjadi kesalahan sistem');
            });
        },

        formatRupiah(num) {
            return new Intl.NumberFormat('id-ID').format(num);
        }
    }));
});
</script>
@endpush
@endsection

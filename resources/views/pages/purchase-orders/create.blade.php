@extends('layouts.app')

@section('title', 'Buat Purchase Order')
@section('breadcrumb', 'PO / Buat Baru')

@section('content')
<div x-data="poStore()" class="space-y-6">
    
    {{-- Main Form Card --}}
    <form action="{{ route('purchase-orders.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- Order Details --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-6 shadow-sm">
                    <h3 class="text-white font-bold mb-5 flex items-center gap-2">
                        <i data-lucide="info" class="w-5 h-5 text-indigo-400"></i> Informasi Pesanan
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="space-y-1.5 w-full">
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Supplier</label>
                                <select name="supplier_id" x-model="supplier_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none" required>
                                    <option value="">-- Pilih Supplier --</option>
                                    @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-[10px] text-gray-500 pl-1">Barang yang bisa di-order bergantung pada katalog supplier yang dipilih.</div>
                            </div>
                            <div class="space-y-1.5 shrink-0 w-full md:w-32">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Tanggal Pesanan</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                        </div>
                    </div>

                    {{-- Items Selector --}}
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h4 class="text-sm font-bold text-gray-300">Daftar Item Barang</h4>
                            <button type="button" @click="openProductSelector()" class="flex items-center gap-2 px-3 py-1.5 bg-indigo-600/10 text-indigo-400 hover:bg-indigo-600 hover:text-white rounded-lg text-xs font-bold transition-all border border-indigo-500/20">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Item Barang
                            </button>
                        </div>

                        <div class="overflow-x-auto border border-gray-700/50 rounded-xl">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-gray-900 text-gray-400 text-[10px] uppercase font-bold tracking-widest">
                                    <tr>
                                        <th class="px-4 py-3">Nama Barang</th>
                                        <th class="px-4 py-3 w-28">Kuantitas</th>
                                        <th class="px-4 py-3 w-40 text-right">Harga Beli</th>
                                        <th class="px-4 py-3 w-40 text-right">Subtotal</th>
                                        <th class="px-4 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700/50">
                                    <template x-for="(item, index) in cart" :key="item.id">
                                        <tr class="hover:bg-gray-700/10 transition-colors">
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-white text-sm" x-text="item.name"></div>
                                                <div class="text-[10px] text-gray-500 font-mono" x-text="item.sku"></div>
                                                <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id || ''">
                                                <input type="hidden" :name="'items['+index+'][supplier_product_id]'" :value="item.id">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" step="0.01" min="0.01" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500 outline-none text-center font-bold">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" :name="'items['+index+'][cost_price]'" x-model.number="item.cost_price" min="0" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-2 py-1.5 text-xs focus:ring-2 focus:ring-indigo-500 outline-none text-right font-bold">
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <span class="text-white font-black" x-text="'Rp ' + formatRupiah(item.quantity * item.cost_price)"></span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <button type="button" @click="removeFromCart(index)" class="text-gray-500 hover:text-red-400 transition-colors">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="cart.length === 0">
                                        <tr>
                                            <td colspan="5" class="py-10 text-center text-gray-500 italic">Belum ada item yang dipilih.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Sidebar --}}
            <div class="space-y-6">
                <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-6 shadow-sm h-fit">
                    <h3 class="text-white font-bold mb-5 flex items-center gap-2">
                        <i data-lucide="calculator" class="w-5 h-5 text-indigo-400"></i> Ringkasan Biaya
                    </h3>

                    <div class="space-y-4 mb-6">
                        <div class="flex justify-between items-center text-gray-400">
                            <span class="text-xs uppercase font-bold tracking-widest">Jumlah Item</span>
                            <span class="text-white font-bold" x-text="cart.length"></span>
                        </div>
                        <div class="flex justify-between items-center text-gray-400">
                            <span class="text-xs uppercase font-bold tracking-widest">Total Kuantitas</span>
                            <span class="text-white font-bold" x-text="calculateTotalQty()"></span>
                        </div>
                        <div class="pt-4 border-t border-gray-700/50">
                            <div class="text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1 text-right">Total Estimasi Tagihan</div>
                            <div class="text-2xl font-black text-white text-right tracking-tight" x-text="'Rp ' + formatRupiah(calculateTotalAmount())"></div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Catatan Tambahan</label>
                            <textarea name="notes" rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-xs focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" placeholder="Keterangan tambahan untuk purchase order..."></textarea>
                        </div>
                        <button type="submit" :disabled="cart.length === 0" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-900/20 transition-all flex items-center justify-center gap-2 active:scale-95">
                            <i data-lucide="save" class="w-4 h-4"></i> Simpan Purchase Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Modal: Item Selector --}}
    <div x-show="showItemModal" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showItemModal = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[80vh]">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white">Pilih Barang</h3>
                <button type="button" @click="showItemModal = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-4 border-b border-gray-700/50 bg-gray-900/10">
                <div class="relative mb-3">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="text" x-model="productSearch" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Cari SKU atau Nama Barang...">
                </div>
                <div x-show="isLoading" class="text-xs text-indigo-400 font-bold animate-pulse text-center">Memuat barang dari supplier...</div>
                <div x-show="!isLoading && supplierProducts.length === 0" class="text-xs text-red-400 font-bold text-center">Supplier ini belum memiliki katalog barang.</div>
            </div>
            <div class="flex-1 overflow-y-auto p-4 scrollbar-thin scrollbar-thumb-gray-700 min-h-[50vh]">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <template x-for="p in supplierProducts" :key="p.id">
                        <div x-show="matchProduct(p.name, p.sku)"
                             @click="addToCart(p); showItemModal = false"
                             class="bg-gray-900/50 border border-gray-700/50 rounded-xl p-3 flex items-center gap-3 cursor-pointer hover:border-indigo-500/50 hover:bg-indigo-500/10 transition-all group">
                            <div class="w-10 h-10 rounded-lg bg-gray-800 flex items-center justify-center shrink-0 border border-gray-700">
                                <i data-lucide="package" class="w-5 h-5 text-gray-600"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-white text-xs truncate" x-text="p.name"></div>
                                <div class="text-[10px] text-gray-500 font-mono" x-html="p.sku ? p.sku : '<i>Tidak ada SKU</i>'"></div>
                            </div>
                            <div class="ml-auto text-right">
                                <div class="text-[10px] font-bold text-indigo-400">Rp <span x-text="formatRupiah(p.price)"></span></div>
                                <div class="text-[9px] text-gray-500">/ <span x-text="p.unit"></span></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function poStore() {
    return {
        supplier_id: '',
        isLoading: false,
        supplierProducts: [],
        showItemModal: false,
        productSearch: '',
        cart: [],
        
        init() {
            this.$watch('supplier_id', (value) => {
                this.cart = []; // Reset cart when supplier changes
                if (value) {
                    this.fetchProducts(value);
                } else {
                    this.supplierProducts = [];
                }
            });
        },

        async fetchProducts(id) {
            this.isLoading = true;
            this.supplierProducts = [];
            try {
                const response = await fetch(`/api/suppliers/${id}/products`);
                if(response.ok) {
                    const data = await response.json();
                    this.supplierProducts = data.products;
                    setTimeout(() => lucide.createIcons(), 100);
                }
            } catch (error) {
                console.error("Failed to fetch products", error);
                alert("Gagal memuat barang supplier.");
            } finally {
                this.isLoading = false;
            }
        },

        openProductSelector() {
            if (!this.supplier_id) {
                alert("Silakan pilih supplier terlebih dahulu!");
                return;
            }
            this.showItemModal = true;
            setTimeout(() => lucide.createIcons(), 100);
        },
        
        matchProduct(name, sku) {
            const query = this.productSearch.toLowerCase();
            const n = name ? name.toLowerCase() : '';
            const s = sku ? sku.toLowerCase() : '';
            return query === '' || n.includes(query) || s.includes(query);
        },

        addToCart(product) {
            const existing = this.cart.find(i => i.id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({
                    id: product.id,
                    product_id: product.product_id,
                    name: product.name,
                    sku: product.sku,
                    quantity: 1,
                    cost_price: parseFloat(product.price) || 0
                });
            }
            setTimeout(() => lucide.createIcons(), 50);
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        calculateTotalQty() {
            return this.cart.reduce((sum, item) => sum + Number(item.quantity), 0);
        },

        calculateTotalAmount() {
            return this.cart.reduce((sum, item) => sum + (Number(item.quantity) * Number(item.cost_price)), 0);
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
    }
}
</script>
@endpush
@endsection

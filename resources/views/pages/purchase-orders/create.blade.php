@extends('layouts.app')

@section('title', 'Buat Purchase Order')
@section('breadcrumb', 'PO / Buat Baru')

@section('content')
<div x-data="poStore()" class="flex flex-col lg:flex-row gap-6">
    
    {{-- Product Catalog / Selection --}}
    <div class="flex-1 space-y-4">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-4 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-center mb-4">
                <h3 class="font-black text-white flex items-center gap-2"><i data-lucide="package" class="w-5 h-5 text-indigo-400"></i> Pilih Produk</h3>
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="text" x-model="searchQuery" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500" placeholder="Cari nama atau SKU...">
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 max-h-[600px] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-700">
                @foreach($products as $p)
                <div x-show="matchProduct('{{ strtolower($p->name) }}', '{{ strtolower($p->sku) }}')" 
                     @click="addToCart({{ json_encode($p) }})"
                     class="bg-gray-900/50 border border-gray-700/50 rounded-xl p-3 cursor-pointer hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all group active:scale-95">
                    <div class="aspect-square rounded-lg bg-gray-800 mb-3 overflow-hidden flex items-center justify-center">
                        @if($p->image)
                            <img src="{{ asset('storage/'.$p->image) }}" class="w-full h-full object-cover">
                        @else
                            <i data-lucide="package" class="w-8 h-8 text-gray-700 group-hover:text-indigo-500/50 transition-colors"></i>
                        @endif
                    </div>
                    <div class="font-bold text-white text-xs truncate mb-1">{{ $p->name }}</div>
                    <div class="flex justify-between items-center">
                        <div class="text-[10px] font-mono text-gray-500">{{ $p->sku }}</div>
                        <div class="text-[10px] font-bold text-indigo-400">Rp {{ number_format($p->cogs, 0, ',', '.') }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- PO Summary / Cart --}}
    <div class="w-full lg:w-[400px] shrink-0 space-y-4">
        <form action="{{ route('purchase-orders.store') }}" method="POST" class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-lg overflow-hidden flex flex-col min-h-[500px]">
            @csrf
            
            <div class="p-4 border-b border-gray-700/50 bg-gray-900/20">
                <h3 class="font-black text-white flex items-center gap-2 mb-4"><i data-lucide="shopping-cart" class="w-5 h-5 text-indigo-400"></i> Rincian Pesanan</h3>
                
                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Supplier</label>
                        <select name="supplier_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none appearance-none" required>
                            <option value="" disabled selected>Pilih Supplier</option>
                            @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Tanggal Pesanan</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[200px] bg-gray-900/10">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="bg-gray-900/80 border border-gray-700/50 rounded-xl p-3 space-y-3">
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-white text-xs truncate" x-text="item.name"></div>
                                <div class="text-[10px] text-gray-500 font-mono" x-text="item.sku"></div>
                            </div>
                            <button type="button" @click="removeFromCart(index)" class="text-gray-500 hover:text-red-400 transition-colors"><i data-lucide="x" class="w-4 h-4"></i></button>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-600 uppercase tracking-tighter">Qty</label>
                                <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" step="0.01" min="0.01" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500 outline-none text-center font-bold">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] font-black text-gray-600 uppercase tracking-tighter">Harga Beli</label>
                                <input type="number" :name="'items['+index+'][cost_price]'" x-model="item.cost_price" min="0" class="w-full bg-gray-800 border border-gray-700 text-white rounded-lg px-2 py-1 text-xs focus:ring-1 focus:ring-indigo-500 outline-none text-right font-bold">
                            </div>
                            <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.id">
                        </div>
                        <div class="text-right text-[11px] font-black text-indigo-400" x-text="'Subtotal: Rp ' + formatRupiah(item.quantity * item.cost_price)"></div>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-600 py-10 opacity-50">
                    <i data-lucide="package-plus" class="w-12 h-12 mb-2"></i>
                    <p class="text-xs font-bold uppercase tracking-widest">Belum ada item</p>
                </div>
            </div>

            <div class="p-4 border-t border-gray-700/50 bg-gray-900/30 space-y-4">
                <div class="space-y-1">
                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Catatan Internal</label>
                    <textarea name="notes" placeholder="Opsional: Keterangan tambahan..." class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-indigo-500 outline-none" rows="2"></textarea>
                </div>

                <div class="pt-2">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Estimasi</span>
                        <span class="text-xl font-black text-white tracking-tighter" x-text="'Rp ' + formatRupiah(calculateTotal())"></span>
                    </div>
                    <button type="submit" :disabled="cart.length === 0" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 disabled:bg-gray-700 disabled:opacity-50 text-white rounded-xl font-black uppercase tracking-widest text-sm shadow-lg shadow-indigo-900/20 transition-all flex items-center justify-center gap-2 active:scale-[0.98]">
                        <i data-lucide="send" class="w-4 h-4"></i> Simpan Purchase Order
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function poStore() {
    return {
        searchQuery: '',
        cart: [],
        
        matchProduct(name, sku) {
            const q = this.searchQuery.toLowerCase();
            return q === '' || name.includes(q) || sku.includes(q);
        },

        addToCart(product) {
            const existing = this.cart.find(i => i.id === product.id);
            if (existing) {
                existing.quantity++;
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    quantity: 1,
                    cost_price: product.cogs || 0
                });
            }
            // Trigger lucide icons refresh if needed (for items in list)
            setTimeout(() => lucide.createIcons(), 50);
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        calculateTotal() {
            return this.cart.reduce((total, item) => total + (item.quantity * item.cost_price), 0);
        },

        formatRupiah(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
    }
}
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'Mulai Produksi')
@section('breadcrumb')
<a href="{{ route('production.index') }}" class="text-gray-400 hover:text-white transition-colors">Produksi</a>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Mulai Produksi</span>
@endsection

@section('content')
<div class="max-w-4xl" x-data="productionForm()">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
            <i data-lucide="settings" class="w-5 h-5"></i>
        </div>
        <h3 class="text-xl font-bold text-white m-0">Mulai Produksi Baru</h3>
    </div>

    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-6 text-red-500 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-500 flex items-center gap-3">
        <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium inline-block">{{ session('error') }}</span>
    </div>
    @endif

    <form method="POST" action="{{ route('production.store') }}" id="production-form" class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden p-6 relative">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="space-y-1.5 md:col-span-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Produk Final <span class="text-red-500">*</span></label>
                <div class="relative">
                    <select name="product_id" id="product_id" x-model="productId" @change="calculateBom()" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-4 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none cursor-pointer" required>
                        <option value="">Pilih produk yang akan diproduksi...</option>
                        @foreach($finalProducts as $fp)
                        <option value="{{ $fp->id }}" data-bom="{{ json_encode($fp->billOfMaterialsAsFinal->map(function($b) { return ['name' => $b->rawMaterial->name, 'req_qty' => $b->quantity, 'stock' => $b->rawMaterial->stock]; })) }}">
                            {{ $fp->sku }} — {{ $fp->name }} (Stok Final: {{ $fp->stock }})
                        </option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                </div>
            </div>
            
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Jumlah Produksi <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i data-lucide="hash" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="number" name="quantity" id="quantity" x-model.number="quantity" @input="calculateBom()" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" value="{{ old('quantity', 1) }}" min="1" required>
                </div>
            </div>
            
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Tanggal Mulai <span class="text-red-500">*</span></label>
                <div class="relative">
                    <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="date" name="date" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" value="{{ old('date', date('Y-m-d')) }}" required>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Penjahit Vendor (Opsional)</label>
                <div class="relative">
                    <select name="tailor_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-4 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">-- Internal / Tanpa Vendor --</option>
                        @foreach($tailors as $t)
                            <option value="{{ $t->id }}" {{ old('tailor_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="scissors" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                </div>
            </div>
            
            <div class="space-y-1.5 md:col-span-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Estimasi Biaya Produksi / Jahit (Opsional)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 text-sm font-medium">Rp</span>
                    <input type="number" name="cost" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" value="{{ old('cost', 0) }}" min="0" step="100" placeholder="Misal: 150000">
                </div>
                <p class="text-[10px] text-gray-500 mt-1">Biaya ini akan ditarik dari Kas ketika status produksi menjadi Selesai.</p>
            </div>
            
            <!-- BoM Auto Calculator -->
            <div class="md:col-span-2 bg-gray-900/50 border border-gray-700 rounded-xl p-5 mt-2">
                <div class="flex items-center gap-2 mb-4">
                    <i data-lucide="clipboard-list" class="w-4 h-4 text-indigo-400"></i>
                    <h4 class="font-bold text-gray-200 text-sm">Kebutuhan Bahan Baku (BoM)</h4>
                </div>
                
                <div id="bom-preview" class="text-sm" x-html="bomHtml">
                    <div class="text-gray-500 italic text-sm flex items-center gap-2">
                        <i data-lucide="mouse-pointer-click" class="w-4 h-4"></i> Silakan pilih produk final terlebih dahulu.
                    </div>
                </div>
            </div>

            <div class="space-y-1.5 md:col-span-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Catatan Tambahan</label>
                <textarea name="notes" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all resize-y min-h-[100px]" placeholder="Catatan opsional untuk proses produksi lini ini...">{{ old('notes') }}</textarea>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 pt-5 border-t border-gray-700/50">
            <button type="submit" id="btn-submit" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-medium transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98]">
                <i data-lucide="play-circle" class="w-4 h-4"></i> Mulai Proses Produksi
            </button>
            <a href="{{ route('production.index') }}" class="flex-1 sm:flex-none flex items-center justify-center px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition-all">
                Batal
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productionForm', () => ({
        productId: '{{ old('product_id', '') }}',
        quantity: {{ old('quantity', 1) }},
        bomHtml: `<div class="text-gray-500 italic text-sm flex items-center gap-2"><i data-lucide="mouse-pointer-click" class="w-4 h-4"></i> Silakan pilih produk final terlebih dahulu.</div>`,

        init() {
            if(this.productId) {
                this.$nextTick(() => { this.calculateBom(); });
            }
        },

        calculateBom() {
            const select = document.getElementById('product_id');
            const qtyInput = this.quantity;
            const btnSubmit = document.getElementById('btn-submit');
            
            if(!this.productId) {
                this.bomHtml = '<div class="text-gray-500 italic text-sm flex items-center gap-2"><i data-lucide="mouse-pointer-click" class="w-4 h-4"></i> Silakan pilih produk final terlebih dahulu.</div>';
                btnSubmit.disabled = false;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                return;
            }

            const option = select.options[select.selectedIndex];
            const bomsStr = option.getAttribute('data-bom');
            
            if (!bomsStr) return;
            
            const boms = JSON.parse(bomsStr);
            const produceQty = parseInt(qtyInput) || 0;
            
            if(boms.length === 0) {
                this.bomHtml = '<div class="bg-amber-500/10 border border-amber-500/20 text-amber-500 px-4 py-3 rounded-lg flex gap-3 text-sm items-start"><i data-lucide="alert-triangle" class="w-4 h-4 mt-0.5 shrink-0"></i><div><strong>Produk ini belum memiliki Bill of Materials (Resep Bahan).</strong><br>Produksi akan tetap tercatat tanpa secara otomatis memotong bahan baku.</div></div>';
                btnSubmit.disabled = false;
                this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                return;
            }

            let html = '<div class="overflow-x-auto border border-gray-700/50 rounded-lg"><table class="w-full text-left">';
            html += '<thead class="bg-gray-800/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50"><tr><th class="px-4 py-2.5 font-medium">Bahan Baku</th><th class="px-4 py-2.5 font-medium">Dibutuhkan</th><th class="px-4 py-2.5 font-medium">Stok Tersedia</th><th class="px-4 py-2.5 font-medium">Status</th></tr></thead>';
            html += '<tbody class="divide-y divide-gray-700/50">';
            
            let hasError = false;

            boms.forEach(b => {
                const totalNeeded = b.req_qty * produceQty;
                const isEnough = b.stock >= totalNeeded;
                if(!isEnough) hasError = true;
                
                const statusHtml = isEnough 
                    ? '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold border inline-flex bg-emerald-500/10 text-emerald-400 border-emerald-500/20">Cukup</span>' 
                    : '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold border inline-flex bg-red-500/10 text-red-400 border-red-500/20">Kurang</span>';

                html += `<tr class="hover:bg-gray-800/30 transition-colors">
                    <td class="px-4 py-2.5 font-medium text-white text-sm">${b.name}</td>
                    <td class="px-4 py-2.5 text-gray-300 font-mono text-xs">${totalNeeded}</td>
                    <td class="px-4 py-2.5 text-gray-300 font-mono text-xs">${b.stock}</td>
                    <td class="px-4 py-2.5">${statusHtml}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';

            if(hasError) {
                html += '<div class="mt-3 text-red-400 flex items-center gap-2 text-xs font-medium"><i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i> Stok bahan baku tidak mencukupi untuk memproduksi kuantitas ini.</div>';
            }

            btnSubmit.disabled = false;
            this.bomHtml = html;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        }
    }));
});
</script>
@endpush
@endsection

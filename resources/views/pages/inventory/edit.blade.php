@extends('layouts.app')

@section('title', 'Edit Produk')
@section('breadcrumb')
<div class="flex items-center gap-2 text-sm">
    <a href="{{ route('inventory.index') }}" class="text-gray-400 hover:text-white transition-colors">Inventory</a>
    <span class="text-gray-600">/</span>
    <span class="text-white font-medium">Edit {{ $product->name }}</span>
</div>
@endsection

@section('content')
<div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm p-6 w-full max-w-4xl" 
     x-data="{
         type: '{{ old('type', $product->type) }}',
         imageMode: 'url',
         boms: {!! old('boms') ? json_encode(array_values(old('boms'))) : ($product->billOfMaterialsAsFinal->count() > 0 ? json_encode($product->billOfMaterialsAsFinal->map(function($b) { return ['raw_material_id' => $b->raw_material_id, 'quantity' => $b->quantity]; })->toArray()) : json_encode([['raw_material_id' => '', 'quantity' => 1]])) !!},
         rawMaterials: {{ json_encode($rawMaterials) }},
         addBom() {
             this.boms.push({ raw_material_id: '', quantity: 1 });
         },
         removeBom(index) {
             this.boms.splice(index, 1);
         }
     }">
     
    <div class="flex items-center gap-3 mb-6 pb-6 border-b border-gray-700/50">
        <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
            <i data-lucide="edit" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-white flex items-center gap-2">Edit Produk <span class="bg-gray-700 text-gray-300 px-2 py-0.5 rounded text-sm font-mono">{{ $product->sku }}</span></h3>
            <p class="text-xs text-gray-400">Update master data produk di sistem inventaris.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-6 text-red-400 text-sm">
        <div class="flex items-center gap-2 font-bold mb-2">
            <i data-lucide="alert-circle" class="w-4 h-4"></i> Terdapat Kesalahan:
        </div>
        <ul class="list-disc list-inside space-y-1 ml-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('inventory.update', $product) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">SKU <span class="text-red-500">*</span></label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600">
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600">
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Kategori <span class="text-red-500">*</span></label>
                <select name="category_id" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lokasi</label>
                <input type="text" name="location" value="{{ old('location', $product->location) }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600">
            </div>
            
            <div class="space-y-2 md:col-span-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tipe Produk <span class="text-red-500">*</span></label>
                <select name="type" x-model="type" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="final_product">🌟 Final Product (Barang Jadi / Siap Jual)</option>
                    <option value="raw_material">🧱 Raw Material (Bahan Baku Produksi)</option>
                </select>
            </div>

            <!-- Bill of Materials Section -->
            <div x-show="type === 'final_product'" x-transition class="md:col-span-2 bg-gray-900/50 border border-indigo-500/20 rounded-xl p-5 shadow-inner">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5 pb-4 border-b border-gray-800">
                    <div>
                        <h4 class="font-bold text-white flex items-center gap-2"><i data-lucide="clipboard-list" class="w-4 h-4 text-indigo-400"></i> Bill of Materials (BoM)</h4>
                        <p class="text-xs text-gray-400 mt-1">Komposisi bahan baku yang dibutuhkan untuk memproduksi 1 unit produk ini.</p>
                    </div>
                    <button type="button" @click="addBom()" class="shrink-0 flex items-center gap-2 px-3 py-1.5 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white rounded-lg text-xs font-medium transition-colors border border-indigo-500/30">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Bahan
                    </button>
                </div>
                
                <div class="space-y-3">
                    <template x-for="(bom, index) in boms" :key="index">
                        <div class="flex flex-col sm:flex-row items-end gap-3 bg-gray-800 p-3 rounded-lg border border-gray-700/50">
                            <div class="w-full sm:flex-1 space-y-1.5">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Bahan Baku (Raw Material)</label>
                                <select :name="`boms[${index}][raw_material_id]`" x-model="bom.raw_material_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                    <option value="">Pilih raw material...</option>
                                    <template x-for="rm in rawMaterials" :key="rm.id">
                                        <option :value="rm.id" x-text="`${rm.sku} — ${rm.name}`"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="w-full sm:w-32 space-y-1.5">
                                <label class="text-[10px] font-bold text-gray-500 uppercase">Kuantitas</label>
                                <input type="number" :name="`boms[${index}][quantity]`" x-model="bom.quantity" min="0.01" step="0.01" placeholder="Jumlah"
                                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            </div>
                            <button type="button" @click="removeBom(index)" class="shrink-0 w-full sm:w-auto px-3 py-2.5 bg-red-500/10 text-red-400 hover:bg-red-500 hover:text-white rounded-lg transition-colors border border-red-500/20 flex justify-center items-center" title="Hapus baris">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </template>
                    <div x-show="boms.length === 0" class="text-center py-6 text-sm text-gray-500 border-2 border-dashed border-gray-700 rounded-lg">
                        Tidak ada bahan baku yang dipilih. Klik "Tambah Bahan" untuk menyusun resep.
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Stok Saat Ini (Hanya Baca)</label>
                <input type="number" value="{{ $product->stock }}" disabled
                       class="w-full bg-gray-900/50 border border-gray-700 text-gray-400 rounded-xl px-4 py-3 text-sm cursor-not-allowed">
                <p class="text-[10px] text-indigo-400">Gunakan fitur Stok Masuk/Keluar atau Opname untuk mengubah stok.</p>
            </div>
            
            <div class="space-y-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Safety Stock <span class="text-red-500">*</span></label>
                <input type="number" name="safety_stock" value="{{ old('safety_stock', $product->safety_stock) }}" min="0" required
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
            </div>
            
            @hasanyrole('Executive|Manager')
            <div class="space-y-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="price" value="{{ old('price', $product->price) }}" min="0" step="1000" required
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
            </div>
            <div class="space-y-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">COGS (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="cogs" value="{{ old('cogs', $product->cogs) }}" min="0" step="1000" required
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
            </div>
            @else
            <input type="hidden" name="price" value="{{ $product->price }}">
            <input type="hidden" name="cogs" value="{{ $product->cogs }}">
            @endhasanyrole

            <div class="md:col-span-2 bg-gray-900/50 border border-dashed border-gray-700 rounded-xl p-5">
                <label class="text-xs font-bold text-white uppercase tracking-wider mb-4 block">🖼️ Gambar Produk</label>
                
                @if($product->image)
                <div class="flex items-center gap-4 p-3 bg-gray-800 rounded-lg border border-gray-700/50 mb-5">
                    <img src="{{ Str::startsWith($product->image, 'http') ? $product->image : asset($product->image) }}" class="w-12 h-12 object-cover rounded shadow">
                    <div>
                        <div class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Gambar Saat Ini</div>
                        <div class="text-sm text-gray-200 truncate max-w-xs">{{ basename($product->image) }}</div>
                    </div>
                </div>
                @endif

                <div class="flex items-center gap-1 bg-gray-800 p-1 rounded-lg border border-gray-700 w-fit mb-4">
                    <button type="button" @click="imageMode = 'url'" :class="imageMode === 'url' ? 'bg-gray-600 text-white shadow-sm' : 'text-gray-400 hover:text-white'" class="px-3 py-1 text-xs font-medium rounded-md transition-all">Ubah via URL</button>
                    <button type="button" @click="imageMode = 'upload'" :class="imageMode === 'upload' ? 'bg-gray-600 text-white shadow-sm' : 'text-gray-400 hover:text-white'" class="px-3 py-1 text-xs font-medium rounded-md transition-all">Upload File Baru</button>
                </div>
                
                <div x-show="imageMode === 'url'" x-transition class="space-y-2">
                    <div class="relative">
                        <i data-lucide="link" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                        <input type="url" name="image" value="{{ old('image', $product->image) }}" placeholder="https://example.com/image.jpg"
                               :disabled="imageMode !== 'url'"
                               class="w-full bg-gray-900 border border-gray-700 text-white pl-10 pr-4 py-3 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600">
                    </div>
                </div>
                
                <div x-show="imageMode === 'upload'" x-transition class="space-y-2" style="display: none;">
                    <input type="file" name="image_file" accept="image/*"
                           :disabled="imageMode !== 'upload'"
                           class="w-full bg-gray-900 border border-gray-700 text-white px-2 py-2 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                </div>
            </div>

            <div class="space-y-2 md:col-span-2">
                <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Batch Produksi / ID Pembelian</label>
                <input type="text" name="batch" value="{{ old('batch', $product->batch) }}"
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600">
            </div>
        </div>
        
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-6 border-t border-gray-700/50 mt-8">
            <a href="{{ route('inventory.index') }}" class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-sm font-medium transition-colors text-center border border-gray-700">Cancel</a>
            <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-medium transition-colors flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/20">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

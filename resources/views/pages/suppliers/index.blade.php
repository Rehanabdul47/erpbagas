@extends('layouts.app')

@section('title', 'Manage Suppliers')
@section('breadcrumb', 'Suppliers')

@section('content')
<div class="space-y-6" x-data="{
    searchQuery: '',
    showModalAdd: false,
    showModalEdit: false,
    showModalAddProduct: false,
    showModalEditProduct: false,
    expandedSupplier: null,
    activeSupplier: null,
    editSupplier: { id: '', name: '', contact_person: '', phone: '', email: '', address: '' },
    editProduct: { id: '', name: '', sku: '', product_id: '', price: '', unit: '' },
    openEditModal(supplier) {
        this.editSupplier = { ...supplier };
        this.showModalEdit = true;
    },
    openAddProductModal(supplier) {
        this.activeSupplier = supplier;
        this.showModalAddProduct = true;
    },
    openEditProductModal(supplier, product) {
        this.activeSupplier = supplier;
        this.editProduct = { ...product };
        this.showModalEditProduct = true;
    },
    toggleExpand(id) {
        this.expandedSupplier = this.expandedSupplier === id ? null : id;
    }
}">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Header Actions --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="relative w-full sm:w-64">
            <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
            <input type="text" x-model="searchQuery" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500" placeholder="Cari supplier...">
        </div>
        <button @click="showModalAdd = true" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20 w-full sm:w-auto">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Supplier
        </button>
    </div>

    {{-- Suppliers Table --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden border border-gray-700/50 rounded-lg text-sm">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">Nama Supplier</th>
                        <th class="px-5 py-4 font-medium">Kontak</th>
                        <th class="px-5 py-4 font-medium">Email / Telepon</th>
                        <th class="px-5 py-4 font-medium">Alamat</th>
                        <th class="px-5 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($suppliers as $s)
                    <tr x-show="searchQuery === '' || '{{ strtolower($s->name) }}'.includes(searchQuery.toLowerCase())" class="hover:bg-gray-700/20 transition-colors group">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-700 flex items-center justify-center text-indigo-400 font-black">
                                    {{ strtoupper(substr($s->name, 0, 1)) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="font-bold text-white">{{ $s->name }}</span>
                                    <button @click="toggleExpand({{ $s->id }})" class="mt-1 text-[10px] font-bold text-indigo-400 hover:text-indigo-300 transition-colors flex items-center gap-1 uppercase tracking-wider text-left">
                                        <i :data-lucide="expandedSupplier === {{ $s->id }} ? 'chevron-up' : 'chevron-down'" class="w-3 h-3"></i>
                                        <span x-text="expandedSupplier === {{ $s->id }} ? 'Tutup Katalog' : 'Lihat Katalog ({{ $s->supplierProducts->count() }} Barang)'"></span>
                                    </button>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-300">{{ $s->contact_person ?: '-' }}</td>
                        <td class="px-5 py-4">
                            <div class="text-white font-medium">{{ $s->phone ?: '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $s->email ?: '-' }}</div>
                        </td>
                        <td class="px-5 py-4 text-gray-400 max-w-xs truncate">{{ $s->address ?: '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openEditModal({{ json_encode($s) }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="{{ route('suppliers.destroy', $s) }}" 
                                    @submit.prevent="$dispatch('global-confirm', { 
                                        title: 'HAPUS SUPPLIER', 
                                        message: 'Yakin ingin menghapus supplier {{ addslashes($s->name) }}? Hubungan supply barang mungkin terganggu.',
                                        actionUrl: '{{ route('suppliers.destroy', $s) }}',
                                        actionMethod: 'DELETE',
                                        confirmText: 'HAPUS'
                                    })" 
                                    class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    
                    {{-- Sub-row for Supplier Products --}}
                    <tr x-show="expandedSupplier === {{ $s->id }}" class="bg-gray-800/50 border-b border-gray-700/50" x-cloak>
                        <td colspan="5" class="p-0">
                            <div class="px-8 py-5 border-l-2 border-indigo-500 bg-gray-900/30 shadow-inner">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                        <i data-lucide="package" class="w-4 h-4 text-indigo-400"></i> Katalog Barang Supplier
                                    </h4>
                                    <button @click="openAddProductModal({{ json_encode($s) }})" class="px-3 py-1.5 bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white rounded-lg text-xs font-medium transition-all flex items-center gap-1.5 border border-indigo-500/30">
                                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Barang
                                    </button>
                                </div>
                                
                                @if($s->supplierProducts->isEmpty())
                                    <div class="text-center py-6 text-gray-500 text-sm italic bg-gray-800/50 border border-gray-700/50 rounded-xl">
                                        Belum ada barang di katalog ini.
                                    </div>
                                @else
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($s->supplierProducts as $sp)
                                        <div class="bg-gray-800 border border-gray-700 rounded-xl p-3 flex justify-between items-center group relative overflow-hidden">
                                            <div class="pr-12">
                                                <div class="font-bold text-sm text-white truncate">{{ $sp->name }}</div>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    @if($sp->sku) <span class="text-[10px] text-gray-500 font-mono">{{ $sp->sku }}</span> @endif
                                                    @if($sp->product) <span class="text-[10px] text-indigo-400 bg-indigo-500/10 px-1.5 rounded">{{ $sp->product->name }}</span> @endif
                                                </div>
                                                <div class="text-xs font-bold text-emerald-400 mt-1.5">Rp {{ number_format($sp->price, 0, ',', '.') }} <span class="text-gray-500 font-normal">/ {{ $sp->unit }}</span></div>
                                            </div>
                                            
                                            <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-800 pl-2">
                                                <button @click="openEditProductModal({{ json_encode($s) }}, {{ json_encode($sp) }})" class="p-1.5 text-gray-400 hover:text-indigo-400 transition-colors" title="Edit Barang">
                                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                                <form method="POST" action="{{ route('suppliers.products.destroy', [$s, $sp]) }}"
                                                    @submit.prevent="$dispatch('global-confirm', { 
                                                        title: 'HAPUS BARANG', 
                                                        message: 'Yakin ingin menghapus {{ addslashes($sp->name) }} dari katalog?',
                                                        actionUrl: '{{ route('suppliers.products.destroy', [$s, $sp]) }}',
                                                        actionMethod: 'DELETE',
                                                        confirmText: 'HAPUS'
                                                    })" 
                                                    class="m-0">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-400 transition-colors" title="Hapus Barang">
                                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-12 text-gray-500 font-medium">Belum ada data supplier</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Add Supplier --}}
    <div x-show="showModalAdd" x-cloak class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4">
        <div @click.away="showModalAdd = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2"><i data-lucide="plus-circle" class="w-5 h-5 text-indigo-400"></i> Tambah Supplier</h3>
                <button @click="showModalAdd = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('suppliers.store') }}">
                @csrf
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Nama Perusahaan / Toko</label>
                        <input type="text" name="name" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Kontak Person</label>
                            <input type="text" name="contact_person" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">No. Telepon</label>
                            <input type="text" name="phone" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Alamat Email</label>
                        <input type="email" name="email" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Alamat Lengkap</label>
                        <textarea name="address" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" rows="3"></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModalAdd = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Supplier --}}
    <div x-show="showModalEdit" x-cloak class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4">
        <div @click.away="showModalEdit = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2"><i data-lucide="edit-2" class="w-5 h-5 text-indigo-400"></i> Edit Supplier</h3>
                <button @click="showModalEdit = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" :action="'/suppliers/' + editSupplier.id">
                @csrf @method('PUT')
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Nama Perusahaan / Toko</label>
                        <input type="text" name="name" x-model="editSupplier.name" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Kontak Person</label>
                            <input type="text" name="contact_person" x-model="editSupplier.contact_person" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">No. Telepon</label>
                            <input type="text" name="phone" x-model="editSupplier.phone" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Alamat Email</label>
                        <input type="email" name="email" x-model="editSupplier.email" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Alamat Lengkap</label>
                        <textarea name="address" x-model="editSupplier.address" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" rows="3"></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModalEdit = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95">Update Supplier</button>
                </div>
            </form>
        </div>
    </div>
    {{-- Modal Add Supplier Product --}}
    <div x-show="showModalAddProduct" x-cloak class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4">
        <div @click.away="showModalAddProduct = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="package-plus" class="w-5 h-5 text-indigo-400"></i> Tambah Barang <span class="text-gray-400 font-normal ml-1" x-text="activeSupplier?.name"></span>
                </h3>
                <button @click="showModalAddProduct = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" :action="'/suppliers/' + activeSupplier?.id + '/products'">
                @csrf
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Nama Barang (Supplier)</label>
                        <input type="text" name="name" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required placeholder="Contoh: Kain Katun Merah Roll">
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 border-l border-indigo-500 pl-2 ml-1">Relasi Stok Gudang (Opsional)</label>
                        <select name="product_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">-- Tidak dihubungkan --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">[{{ $p->sku }}] {{ $p->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-500 pl-1 mt-1">Pilih ini jika ingin stok bertambah otomatis ketika Purchase Order barang ini diterima.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">SKU / Kode (Supplier)</label>
                            <input type="text" name="sku" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Misal: S-KTR-01">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Harga Beli</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                <input type="number" name="price" min="0" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Satuan</label>
                        <input type="text" name="unit" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required placeholder="Contoh: pcs, roll, meter, kg">
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModalAddProduct = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95">Simpan Katalog</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Supplier Product --}}
    <div x-show="showModalEditProduct" x-cloak class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4">
        <div @click.away="showModalEditProduct = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-5 h-5 text-indigo-400"></i> Edit Barang <span class="text-gray-400 font-normal ml-1" x-text="activeSupplier?.name"></span>
                </h3>
                <button @click="showModalEditProduct = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" :action="'/suppliers/' + activeSupplier?.id + '/products/' + editProduct.id">
                @csrf @method('PUT')
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Nama Barang (Supplier)</label>
                        <input type="text" name="name" x-model="editProduct.name" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 border-l border-indigo-500 pl-2 ml-1">Relasi Stok Gudang (Opsional)</label>
                        <select name="product_id" x-model="editProduct.product_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">-- Tidak dihubungkan --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">[{{ $p->sku }}] {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">SKU / Kode (Supplier)</label>
                            <input type="text" name="sku" x-model="editProduct.sku" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Harga Beli</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">Rp</span>
                                <input type="number" name="price" x-model="editProduct.price" min="0" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl pl-9 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-1">Satuan</label>
                        <input type="text" name="unit" x-model="editProduct.unit" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModalEditProduct = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95">Update Katalog</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

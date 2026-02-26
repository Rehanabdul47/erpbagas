@extends('layouts.app')

@section('title', 'Inventory')
@section('breadcrumb', 'Inventory')

@section('content')
<div x-data="{ 
    activeTab: new URLSearchParams(window.location.search).get('tab') || 'stock-list',
    search: '',
    category: '',
    status: '',
    type: '',
    showStockIn: false,
    showStockOut: false,
    showOpnameModal: false,
    opnameProduct: { id: '', name: '', sysStock: 0, physStock: 0 }
}">

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 flex items-center gap-3">
        <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="package" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $stats['totalSku'] }}</div>
            <div class="text-gray-400 text-sm">Total SKU</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="layers" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['totalStock']) }}</div>
            <div class="text-gray-400 text-sm">Total Stok</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4"><i data-lucide="alert-triangle" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $stats['lowStock'] }}</div>
            <div class="text-gray-400 text-sm">Stok Rendah</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center mb-4"><i data-lucide="x-circle" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $stats['outOfStock'] }}</div>
            <div class="text-gray-400 text-sm">Stok Habis</div>
        </div>
    </div>

    {{-- Tabs Container --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm mb-6 flex flex-col">
        <div class="flex overflow-x-auto border-b border-gray-700/50 hide-scrollbar">
            <button @click="activeTab = 'stock-list'" :class="activeTab === 'stock-list' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-700/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/20'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">📦 Daftar Stok</button>
            <button @click="activeTab = 'movement'" :class="activeTab === 'movement' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-700/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/20'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">🔄 Riwayat Pergerakan</button>
            <button @click="activeTab = 'opname'" :class="activeTab === 'opname' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-700/20' : 'text-gray-400 hover:text-white hover:bg-gray-700/20'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">📋 Stock Opname</button>
        </div>

        {{-- Tab: Daftar Stok --}}
        <div x-show="activeTab === 'stock-list'" class="p-5">
            <div class="flex flex-wrap items-center gap-3 mb-5">
                <input x-model="search" type="text" placeholder="Cari SKU atau Produk..." class="w-full sm:w-64 bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500">
                <select x-model="category" class="w-full sm:w-48 bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                <select x-model="status" class="w-full sm:w-40 bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="">Semua Status</option>
                    <option value="ok">✅ Aman</option>
                    <option value="low">⚠️ Rendah</option>
                    <option value="out">🔴 Habis</option>
                </select>
                <select x-model="type" class="w-full sm:w-48 bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="">Semua Tipe</option>
                    <option value="final_product">🌟 Final Product</option>
                    <option value="raw_material">🧱 Raw Material</option>
                </select>
                <div class="sm:ml-auto flex flex-wrap gap-2 w-full sm:w-auto">
                    <a href="{{ route('inventory.create') }}" class="flex items-center justify-center gap-2 px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors"><i data-lucide="plus" class="w-4 h-4"></i> Tambah</a>
                    <button @click="showStockIn = true" class="flex items-center justify-center gap-2 px-3 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition-colors"><i data-lucide="arrow-down-circle" class="w-4 h-4"></i> Masuk</button>
                    <button @click="showStockOut = true" class="flex items-center justify-center gap-2 px-3 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-sm font-medium transition-colors"><i data-lucide="arrow-up-circle" class="w-4 h-4"></i> Keluar</button>
                </div>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 font-medium w-12"></th>
                            <th class="px-4 py-3 font-medium">SKU</th>
                            <th class="px-4 py-3 font-medium">Produk</th>
                            <th class="px-4 py-3 font-medium">Tipe</th>
                            <th class="px-4 py-3 font-medium">Kategori</th>
                            <th class="px-4 py-3 font-medium">Stok</th>
                            <th class="px-4 py-3 font-medium">Safety</th>
                            @hasanyrole('Manager|Executive|SuperAdmin')
                            <th class="px-4 py-3 font-medium">Harga</th>
                            <th class="px-4 py-3 font-medium">COGS</th>
                            @endhasanyrole
                            <th class="px-4 py-3 font-medium">Lokasi</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($products as $p)
                        <tr x-show="(search === '' || '{{ strtolower($p->sku) }}'.includes(search.toLowerCase()) || '{{ strtolower($p->name) }}'.includes(search.toLowerCase())) && (category === '' || category === '{{ $p->category->name }}') && (status === '' || status === '{{ $p->status }}') && (type === '' || type === '{{ $p->type }}')" class="hover:bg-gray-700/20 transition-colors">
                            <td class="px-4 py-3">
                                @if($p->image)
                                    <img src="{{ Str::startsWith($p->image, 'http') ? $p->image : asset($p->image) }}" alt="{{ $p->name }}" class="w-8 h-8 object-cover rounded border border-gray-700">
                                @else
                                    <div class="w-8 h-8 bg-gray-900 rounded flex items-center justify-center text-gray-500 border border-gray-700">
                                        <i data-lucide="package" class="w-4 h-4"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold text-white">{{ $p->sku }}</td>
                            <td class="px-4 py-3 text-gray-300 font-medium">{{ $p->name }}</td>
                            <td class="px-4 py-3">
                                @if($p->type === 'final_product')
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">🌟 Final Product</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold bg-gray-500/10 text-gray-400 border border-gray-500/20">🧱 Raw Material</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-400">{{ $p->category->name }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 h-1.5 bg-gray-700 rounded-full overflow-hidden flex">
                                        @php $pct = $p->safety_stock > 0 ? min(100, ($p->stock / ($p->safety_stock * 3)) * 100) : 100; @endphp
                                        <div style="width:{{ $pct }}%" class="h-full {{ $p->status === 'ok' ? 'bg-emerald-500' : ($p->status === 'low' ? 'bg-amber-500' : 'bg-red-500') }} rounded-full"></div>
                                    </div>
                                    <span class="font-bold text-white">{{ $p->stock }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-400">{{ $p->safety_stock }}</td>
                            @hasanyrole('Manager|Executive|SuperAdmin')
                            <td class="px-4 py-3 text-gray-300">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-gray-300">Rp {{ number_format($p->cogs, 0, ',', '.') }}</td>
                            @endhasanyrole
                            <td class="px-4 py-3 text-gray-400">{{ $p->location }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold 
                                    {{ $p->status === 'ok' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 
                                       ($p->status === 'low' ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20') }} border">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('inventory.barcode', $p) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors" title="Barcode"><i data-lucide="barcode" class="w-4 h-4"></i></a>
                                    <a href="{{ route('inventory.edit', $p) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Edit"><i data-lucide="edit" class="w-4 h-4"></i></a>
                                    <form action="{{ route('inventory.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')" class="m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="12" class="text-center py-10 text-gray-500">Belum ada data produk</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab: Movement History --}}
        <div x-show="activeTab === 'movement'" x-cloak class="p-5" style="display: none;">
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr><th class="px-4 py-3 font-medium">Tanggal</th><th class="px-4 py-3 font-medium">SKU</th><th class="px-4 py-3 font-medium">Produk</th><th class="px-4 py-3 font-medium">Tipe</th><th class="px-4 py-3 font-medium">Qty</th><th class="px-4 py-3 font-medium">Referensi</th><th class="px-4 py-3 font-medium">Metode</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($movements as $m)
                        <tr class="hover:bg-gray-700/20 transition-colors">
                            <td class="px-4 py-3 text-gray-400">{{ $m->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 font-bold text-white">{{ $m->product->sku }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ $m->product->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold border {{ $m->type === 'in' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20' }}">
                                    {{ $m->type === 'in' ? '📥 Masuk' : '📤 Keluar' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-white">{{ $m->quantity }}</td>
                            <td class="px-4 py-3 text-gray-400">{{ $m->reference }}</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-[10px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20">{{ $m->method }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-10 text-gray-500">Belum ada riwayat pergerakan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab: Stock Opname --}}
        <div x-show="activeTab === 'opname'" x-cloak class="p-5" style="display: none;">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h4 class="text-white font-bold text-lg mb-1">📋 Stock Opname — Reinkonsiliasi Stok</h4>
                    <p class="text-xs text-gray-400">* Mengubah stok langsung sesuai jumlah fisik terbaru di gudang.</p>
                </div>
            </div>
            
            <div class="overflow-x-auto w-full mb-8">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr><th class="px-4 py-3 font-medium">Produk</th><th class="px-4 py-3 font-medium">Stok Sistem</th><th class="px-4 py-3 font-medium">Stok Fisik</th><th class="px-4 py-3 font-medium">Selisih</th><th class="px-4 py-3 font-medium text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @foreach($products as $p)
                        <tr x-data="{ sysStock: {{ $p->stock }}, physStock: {{ $p->stock }}, diff: 0 }" x-effect="diff = physStock - sysStock" class="hover:bg-gray-700/20 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-bold text-white">{{ $p->name }}</div>
                                <div class="text-xs text-gray-500 font-mono mt-0.5">SKU: {{ $p->sku }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-300 font-medium" x-text="sysStock"></td>
                            <td class="px-4 py-3">
                                <input type="number" x-model.number="physStock" class="w-24 bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            </td>
                            <td class="px-4 py-3 font-bold" :class="diff > 0 ? 'text-emerald-400' : (diff < 0 ? 'text-red-400' : 'text-gray-500')" x-text="diff > 0 ? '+' + diff : diff"></td>
                            <td class="px-4 py-3 text-right">
                                <button @click="opnameProduct = { id: {{ $p->id }}, name: '{{ addslashes($p->name) }}', sysStock: sysStock, physStock: physStock }; showOpnameModal = true" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-medium transition-colors border border-indigo-500/50 shadow-sm shadow-indigo-600/20">Adjust</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-700/50 pt-6">
                <h5 class="flex items-center gap-3 font-bold text-white mb-4">
                    <span>📖 Riwayat Opname Terakhir</span>
                    <span class="bg-gray-700 text-gray-300 px-2 py-0.5 rounded-full text-[10px]">{{ $opnameHistory->count() }}</span>
                </h5>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                            <tr><th class="px-4 py-3 font-medium">Waktu</th><th class="px-4 py-3 font-medium">Produk</th><th class="px-4 py-3 font-medium">Sistem</th><th class="px-4 py-3 font-medium">Fisik</th><th class="px-4 py-3 font-medium">Selisih</th><th class="px-4 py-3 font-medium">Catatan</th><th class="px-4 py-3 font-medium">Oleh</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($opnameHistory as $oh)
                            <tr class="hover:bg-gray-700/20 transition-colors">
                                <td class="px-4 py-3 text-gray-400">{{ $oh->created_at->format('d M, H:i') }}</td>
                                <td class="px-4 py-3 font-bold text-gray-200">{{ $oh->product->name }}</td>
                                <td class="px-4 py-3 text-gray-400">{{ $oh->system_stock }}</td>
                                <td class="px-4 py-3 font-bold text-white">{{ $oh->physical_stock }}</td>
                                <td class="px-4 py-3 font-bold {{ $oh->difference >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ ($oh->difference > 0 ? '+' : '') . $oh->difference }}
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 max-w-[200px] truncate" title="{{ $oh->notes ?? '-' }}">{{ $oh->notes ?? '-' }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-1 flex w-fit items-center rounded-lg bg-gray-800 border border-gray-700 text-gray-400 text-xs">{{ $oh->user->name }}</span></td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-10 text-gray-500">Belum ada riwayat opname</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Stock Opname Adjustment --}}
    <div x-show="showOpnameModal" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showOpnameModal = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2"><i data-lucide="clipboard-check" class="w-5 h-5 text-indigo-400"></i> Konfirmasi Opname</h3>
                <button type="button" @click="showOpnameModal = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('inventory.stock-opname') }}">
                @csrf
                <input type="hidden" name="product_id" x-bind:value="opnameProduct.id">
                <input type="hidden" name="physical_stock" x-bind:value="opnameProduct.physStock">
                <div class="p-5">
                    <div class="bg-gray-900 border border-gray-700/50 rounded-xl p-4 text-sm text-gray-300 mb-5 shadow-inner">
                        <div class="mb-2">Produk: <span class="font-bold text-white text-base" x-text="opnameProduct.name"></span></div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-700/50">
                            <span class="text-gray-400">Stok Sistem:</span>
                            <span class="font-medium" x-text="opnameProduct.sysStock"></span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-indigo-400 font-bold">Stok Fisik Baru:</span>
                            <span class="font-bold text-lg text-white" x-text="opnameProduct.physStock"></span>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Catatan (Alasan Selisih)</label>
                        <textarea name="notes" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" placeholder="Contoh: Barang rusak, salah catat..." rows="2"></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="showOpnameModal = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors border border-indigo-500 shadow-sm">Simpan Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Stock Masuk --}}
    <div x-show="showStockIn" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showStockIn = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2"><i data-lucide="arrow-down-circle" class="w-5 h-5 text-emerald-400"></i> Stok Masuk</h3>
                <button type="button" @click="showStockIn = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('inventory.stock-in') }}">
                @csrf
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Produk</label>
                        <select name="product_id" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                            <option value="">Pilih produk...</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }} (stok: {{ $p->stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5 flex flex-col">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Jumlah</label>
                        <div class="relative">
                            <i data-lucide="package-plus" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-emerald-500"></i>
                            <input type="number" name="quantity" min="1" required class="w-full bg-gray-900 border border-gray-700 text-white pl-10 pr-4 py-3 rounded-xl focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5 flex flex-col">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Referensi</label>
                        <input type="text" name="reference" placeholder="PO ID / catatan" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all placeholder-gray-600">
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="showStockIn = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">Simpan Stok Masuk</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Stock Keluar --}}
    <div x-show="showStockOut" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showStockOut = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2"><i data-lucide="arrow-up-circle" class="w-5 h-5 text-amber-500"></i> Stok Keluar</h3>
                <button type="button" @click="showStockOut = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('inventory.stock-out') }}">
                @csrf
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5 flex flex-col">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Produk</label>
                        <select name="product_id" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                            <option value="">Pilih produk...</option>
                            @foreach($products->where('stock', '>', 0) as $p)
                            <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }} (stok: {{ $p->stock }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5 flex flex-col">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Jumlah</label>
                        <div class="relative">
                            <i data-lucide="package-minus" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-amber-500"></i>
                            <input type="number" name="quantity" min="1" required class="w-full bg-gray-900 border border-gray-700 text-white pl-10 pr-4 py-3 rounded-xl focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5 flex flex-col">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Referensi</label>
                        <input type="text" name="reference" placeholder="Order ID / catatan" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-amber-500 outline-none transition-all placeholder-gray-600">
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="showStockOut = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">Proses Stok Keluar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

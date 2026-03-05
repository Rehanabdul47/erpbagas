@extends('layouts.app')

@section('title', 'Vendor Produksi')
@section('breadcrumb', 'Vendor Produksi')

@section('content')
<div class="space-y-6" x-data="tailorsPage()">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-500 flex items-center gap-3">
        <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif
    
    @if($errors->any())
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 text-red-500 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-6">
        <div class="bg-gray-800 border-b-4 border-indigo-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="scissors" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ collect($tailors)->count() }}</div>
            <div class="text-gray-400 text-sm">Total Penjahit Vendors</div>
        </div>
        <div class="bg-gray-800 border-b-4 border-emerald-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="package" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ collect($tailors)->sum('productions_count') }}</div>
            <div class="text-gray-400 text-sm">Total Produksi Aktif Dikerjakan</div>
        </div>
        <div class="bg-gray-800 border-b-4 border-amber-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm sm:col-span-2 md:col-span-1">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4"><i data-lucide="wallet" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">Rp {{ number_format(collect($tailors)->sum('active_cost'), 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Estimasi Total Tagihan Aktif</div>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col p-5">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
            <div class="relative w-full sm:max-w-xs">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                <input type="text" x-model="searchQuery" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500" placeholder="Cari penjahit...">
            </div>
            <button @click="openAddModal" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20 w-full sm:w-auto">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Vendor Produksi
            </button>
        </div>

        <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr><th class="px-5 py-3 font-medium">Nama Vendor</th><th class="px-5 py-3 font-medium">No. Telepon</th><th class="px-5 py-3 font-medium">Alamat</th><th class="px-5 py-3 font-medium">Produksi Aktif</th><th class="px-5 py-3 font-medium">Estimasi Tagihan</th><th class="px-5 py-3 font-medium text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($tailors as $tailor)
                    <tr x-show="matchSearch('{{ strtolower($tailor->name) }}')" class="hover:bg-indigo-500/5 transition-colors group">
                        <td class="px-5 py-3 font-bold text-white">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
                                    <i data-lucide="scissors" class="w-4 h-4"></i>
                                </div>
                                {{ $tailor->name }}
                            </div>
                        </td>
                        <td class="px-5 py-3 text-gray-300">{{ $tailor->phone ?? '-' }}</td>
                        <td class="px-5 py-3 text-gray-400 text-xs whitespace-normal max-w-[200px]">{{ $tailor->address ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 flex w-fit rounded-full text-xs font-bold border {{ $tailor->productions_count > 0 ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-gray-500/10 text-gray-400 border-gray-500/20' }}">
                                {{ $tailor->productions_count }} Pesanan
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            @if($tailor->active_cost > 0)
                                <span class="font-bold text-amber-400">Rp {{ number_format($tailor->active_cost, 0, ',', '.') }}</span>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                <button @click="openEditModal({{ $tailor->id }}, '{{ addslashes($tailor->name) }}', '{{ addslashes($tailor->phone) }}', '{{ addslashes($tailor->address) }}')" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="{{ route('tailors.destroy', $tailor) }}" 
                                    @submit.prevent="$dispatch('global-confirm', { 
                                        title: 'HAPUS VENDOR', 
                                        message: 'Apakah Anda yakin ingin menghapus data penjahit {{ addslashes($tailor->name) }}? Informasi produksi vendor ini akan ikut terhapus.',
                                        actionUrl: '{{ route('tailors.destroy', $tailor) }}',
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
                    @empty
                    <tr><td colspan="5" class="text-center py-10 text-gray-500"><div class="flex flex-col items-center gap-2"><i data-lucide="scissors" class="w-8 h-8 opacity-50"></i><span>Belum ada vendor penjahit</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal: Tambah/Edit Penjahit --}}
    <div x-show="modals.show" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.show = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="scissors" class="w-5 h-5 text-indigo-400"></i>
                    <span x-text="modals.mode === 'add' ? 'Tambah Vendor Produksi' : 'Edit Vendor Produksi'"></span>
                </h3>
                <button @click="modals.show = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="form.action" method="POST">
                @csrf
                <template x-if="modals.mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Nama Lengkap / Vendor <span class="text-red-500">*</span></label>
                        <input type="text" name="name" x-model="form.name" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Masukkan nama" required autofocus>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">No. Telepon / WA</label>
                        <input type="text" name="phone" x-model="form.phone" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" placeholder="08xxxxxxxx">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Alamat</label>
                        <textarea name="address" x-model="form.address" rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" placeholder="Alamat lengkap bengkel kerja"></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="modals.show = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20" x-text="modals.mode === 'add' ? 'Simpan' : 'Update'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tailorsPage', () => ({
        searchQuery: '',
        modals: {
            show: false,
            mode: 'add'
        },
        form: {
            action: '{{ route('tailors.store') }}',
            name: '',
            phone: '',
            address: ''
        },

        matchSearch(name) {
            if (this.searchQuery === '') return true;
            return name.includes(this.searchQuery.toLowerCase());
        },

        openAddModal() {
            this.modals.mode = 'add';
            this.form.action = '{{ route('tailors.store') }}';
            this.form.name = '';
            this.form.phone = '';
            this.form.address = '';
            this.modals.show = true;
        },

        openEditModal(id, name, phone, address) {
            this.modals.mode = 'edit';
            this.form.action = `/tailors/${id}`;
            this.form.name = name;
            this.form.phone = phone || '';
            this.form.address = address || '';
            this.modals.show = true;
        }
    }));
});
</script>
@endpush
@endsection

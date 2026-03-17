@extends('layouts.app')

@section('title', 'Manajemen Data Platform')
@section('breadcrumb', 'Data Platform E-Commerce')

@section('content')
<div x-data="platformData()">
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {{-- Shopee Mapping Guide --}}
        <div class="bg-gradient-to-br from-orange-500/10 to-transparent border border-orange-500/20 rounded-2xl p-5 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i data-lucide="shopping-bag" class="w-16 h-16 text-orange-500"></i>
            </div>
            <h3 class="text-orange-400 font-bold mb-4 flex items-center gap-2 text-sm uppercase tracking-widest">
                <span class="w-2 h-2 rounded-full bg-orange-500"></span> Shopee Status Mapping
            </h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs py-1 border-b border-orange-500/10">
                    <span class="text-gray-400">Belum Bayar</span>
                    <span class="text-indigo-400 font-medium">Pending</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1 border-b border-orange-500/10">
                    <span class="text-gray-400">Perlu Dikirim</span>
                    <span class="text-amber-400 font-medium">Ready to Ship</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1 border-b border-orange-500/10">
                    <span class="text-gray-400">Dikirim</span>
                    <span class="text-blue-400 font-medium">Shipped</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1 border-b border-orange-500/10">
                    <span class="text-gray-400">Selesai</span>
                    <span class="text-emerald-400 font-medium">Delivered</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1">
                    <span class="text-gray-400">Pengembalian/Batal</span>
                    <span class="text-red-400 font-medium">Cancelled</span>
                </div>
            </div>
        </div>

        {{-- TikTok Mapping Guide --}}
        <div class="bg-gradient-to-br from-gray-500/10 to-transparent border border-gray-500/20 rounded-2xl p-5 relative overflow-hidden text-white">
            <div class="absolute top-0 right-0 p-4 opacity-10">
                <i data-lucide="music" class="w-16 h-16 text-white"></i>
            </div>
            <h3 class="text-white font-bold mb-4 flex items-center gap-2 text-sm uppercase tracking-widest">
                <span class="w-2 h-2 rounded-full bg-white"></span> TikTok Shop Mapping
            </h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between text-xs py-1 border-b border-white/10">
                    <span class="text-gray-400">Dalam Proses</span>
                    <span class="text-indigo-400 font-medium">Pending</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1 border-b border-white/10">
                    <span class="text-gray-400">Perlu dikirim</span>
                    <span class="text-amber-400 font-medium">Ready to Ship</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1 border-b border-white/10">
                    <span class="text-gray-400">Dikirim</span>
                    <span class="text-blue-400 font-medium">Shipped</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1 border-b border-white/10">
                    <span class="text-gray-400">Selesai</span>
                    <span class="text-emerald-400 font-medium">Delivered</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1">
                    <span class="text-gray-400">Dibatalkan / Gagal</span>
                    <span class="text-red-400 font-medium">Cancelled</span>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i data-lucide="layout-list" class="w-6 h-6 text-indigo-400"></i> Daftar Platform E-Commerce
        </h2>
        <button @click="openModal('create')" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-600/20 active:scale-95 w-full sm:w-auto">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Platform
        </button>
    </div>

    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                <tr>
                    <th class="px-5 py-4 font-medium">Nama Platform</th>
                    <th class="px-5 py-4 font-medium">Deskripsi</th>
                    <th class="px-5 py-4 font-medium">Status</th>
                    <th class="px-5 py-4 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                @forelse($platforms as $platform)
                <tr class="hover:bg-indigo-500/5 transition-colors group">
                    <td class="px-5 py-4">
                        <div class="font-bold text-white">{{ $platform->name }}</div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-xs text-gray-400 max-w-[300px] truncate" title="{{ $platform->description }}">{{ $platform->description ?: '-' }}</div>
                    </td>
                    <td class="px-5 py-4">
                        @if($platform->is_active)
                        <span class="text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-lg text-xs font-bold">Aktif</span>
                        @else
                        <span class="text-red-400 bg-red-500/10 px-2 py-1 rounded-lg text-xs font-bold">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openModal('edit', {{ json_encode($platform) }})" class="p-2 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                            
                            <form action="{{ route('digital-platforms.destroy', $platform) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin ingin menghapus platform ini?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-12 text-gray-500">Belum ada data platform.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal: Create / Edit --}}
    <div x-show="modals.form" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.form = false" class="bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="layout-list" class="w-5 h-5 text-indigo-400"></i>
                    <span x-text="isEdit ? 'Edit Platform' : 'Tambah Platform Baru'"></span>
                </h3>
                <button @click="modals.form = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <form :action="formAction" method="POST" class="flex-1 flex flex-col">
                @csrf
                <template x-if="isEdit">
                    @method('PUT')
                </template>
                
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Platform <span class="text-red-400">*</span></label>
                        <input type="text" name="name" x-model="formData.name" required placeholder="Contoh: Tokopedia" class="w-full bg-gray-900/50 border border-gray-700 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Deskripsi (Opsional)</label>
                        <textarea name="description" x-model="formData.description" rows="3" placeholder="Deskripsi mengenai platform ini..." class="w-full bg-gray-900/50 border border-gray-700 text-white text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5 resize-none"></textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="formData.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            <span class="ml-3 text-sm font-medium text-gray-300" x-text="formData.is_active ? 'Platform Aktif' : 'Platform Nonaktif'"></span>
                        </label>
                    </div>
                </div>

                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="modals.form = false" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors" x-text="isEdit ? 'Simpan Perubahan' : 'Simpan'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('platformData', () => ({
        modals: {
            form: false
        },
        isEdit: false,
        formAction: '{{ route('digital-platforms.store') }}',
        formData: {
            name: '',
            description: '',
            is_active: true
        },

        openModal(mode, data = null) {
            this.isEdit = mode === 'edit';
            
            if (this.isEdit && data) {
                this.formAction = `/digital-platforms/${data.id}`;
                this.formData = {
                    name: data.name,
                    description: data.description || '',
                    is_active: !!data.is_active
                };
            } else {
                this.formAction = '{{ route('digital-platforms.store') }}';
                this.formData = {
                    name: '',
                    description: '',
                    is_active: true
                };
            }

            this.modals.form = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        }
    }));
});
</script>
@endpush
@endsection

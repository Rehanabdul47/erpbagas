@extends('layouts.app')

@section('title', 'Manajemen Cabang')
@section('breadcrumb', 'Cabang / Toko Offline')

@section('content')
<div x-data="branchData()">
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i data-lucide="store" class="w-6 h-6 text-indigo-400"></i> Daftar Cabang / Toko
        </h2>
        <a href="{{ route('branches.create') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-600/20 active:scale-95 w-full sm:w-auto">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Cabang
        </a>
    </div>

    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                <tr>
                    <th class="px-5 py-4 font-medium">Nama Cabang</th>
                    <th class="px-5 py-4 font-medium">Tipe</th>
                    <th class="px-5 py-4 font-medium">Kontak</th>
                    <th class="px-5 py-4 font-medium">Status</th>
                    <th class="px-5 py-4 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                @forelse($branches as $branch)
                <tr @click="viewBranchDetail({{ json_encode($branch) }})" class="hover:bg-indigo-500/5 transition-colors cursor-pointer group">
                    <td class="px-5 py-4">
                        <div class="font-bold text-white">{{ $branch->name }}</div>
                        <div class="text-xs text-gray-400 mt-1 max-w-[200px] truncate" title="{{ $branch->address }}">{{ $branch->address ?: '-' }}</div>
                    </td>
                    <td class="px-5 py-4">
                        <span class="px-2 py-1 rounded-lg text-[10px] font-bold uppercase {{ $branch->type === 'main' ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-gray-700 text-gray-300' }}">
                            {{ $branch->type === 'main' ? 'Pusat' : 'Cabang' }}
                        </span>
                    </td>
                    <td class="px-5 py-4 font-mono text-gray-300">{{ $branch->phone ?: '-' }}</td>
                    <td class="px-5 py-4">
                        @if($branch->is_active)
                        <span class="text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-lg text-xs font-bold">Aktif</span>
                        @else
                        <span class="text-red-400 bg-red-500/10 px-2 py-1 rounded-lg text-xs font-bold">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('branches.edit', $branch) }}" @click.stop class="p-2 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors"><i data-lucide="edit-2" class="w-4 h-4"></i></a>
                            
                            <form action="{{ route('branches.destroy', $branch) }}" method="POST" class="m-0" onsubmit="return confirm('Yakin ingin menghapus cabang ini?');" @click.stop>
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-12 text-gray-500">Belum ada data cabang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal: Detail View --}}
    <div x-show="modals.detail" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.detail = false" class="bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2" x-html="detail.title"></h3>
                <button @click="modals.detail = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-5 overflow-y-auto max-h-[70vh]" x-html="detail.content">
            </div>
            <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end">
                <button @click="modals.detail = false" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('branchData', () => ({
        modals: {
            detail: false
        },
        detail: { title: '', content: '' },

        viewBranchDetail(b) {
            this.detail.title = '<i data-lucide="store" class="w-5 h-5 text-indigo-400"></i> Detail Cabang / Toko';
            
            const typeLabel = b.type === 'main' ? 'Pusat' : 'Cabang';
            const typeClass = b.type === 'main' ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-gray-700 text-gray-300 border-gray-600';
            const statusLabel = b.is_active ? 'Aktif' : 'Nonaktif';
            const statusClass = b.is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-400 border-red-500/20';

            this.detail.content = `
                <div class="text-center mb-6">
                    <div class="w-20 h-20 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mx-auto mb-3 shadow-lg shadow-indigo-500/5 border border-indigo-500/20">
                        <i data-lucide="store" class="w-10 h-10"></i>
                    </div>
                    <h4 class="text-xl font-bold text-white mb-1">${b.name}</h4>
                    <div class="flex items-center justify-center gap-2 mt-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border ${typeClass}">${typeLabel.toUpperCase()}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border ${statusClass}">${statusLabel.toUpperCase()}</span>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 gap-4">
                    <div class="bg-gray-900/50 border border-gray-700/50 p-4 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5"></i> Kepala Toko / Manager
                        </div>
                        <div class="text-gray-300 text-sm font-bold">${b.manager_name || '<span class="italic text-gray-500 font-normal">Belum ditentukan</span>'}</div>
                    </div>

                    <div class="bg-gray-900/50 border border-gray-700/50 p-4 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Alamat Lengkap
                        </div>
                        <div class="text-gray-300 text-sm leading-relaxed">${b.address || '<span class="italic text-gray-500">Alamat belum diatur</span>'}</div>
                    </div>

                    <div class="bg-gray-900/50 border border-gray-700/50 p-4 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="phone" class="w-3.5 h-3.5"></i> No. Telepon / Kontak
                        </div>
                        <div class="text-gray-300 text-sm font-mono">${b.phone || '<span class="italic text-gray-500">Tidak ada nomor telepon</span>'}</div>
                    </div>

                    <div class="bg-gray-900/50 border border-gray-700/50 p-4 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i data-lucide="calendar" class="w-3.5 h-3.5"></i> Terdaftar Pada
                        </div>
                        <div class="text-gray-300 text-sm">${new Date(b.created_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}</div>
                    </div>
                </div>
            `;
            this.modals.detail = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        }
    }));
});
</script>
@endpush
@endsection

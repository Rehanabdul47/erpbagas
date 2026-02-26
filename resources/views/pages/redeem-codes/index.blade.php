@extends('layouts.app')

@section('title', 'Redeem Codes')
@section('breadcrumb', 'Redeem Codes')

@section('content')
<div class="space-y-6" x-data="redeemCodesPage()">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col p-5">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
            <h3 class="text-xl font-bold text-white mb-0 flex items-center gap-2">
                <i data-lucide="sparkles" class="w-6 h-6 text-amber-400"></i>
                Daftar Kode Redeem
            </h3>
            <button @click="openCreateModal" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20 w-full sm:w-auto">
                <i data-lucide="plus" class="w-4 h-4"></i> Tambah Kode
            </button>
        </div>

        <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg text-sm">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-3 font-medium">Kode</th>
                        <th class="px-5 py-3 font-medium">Tipe & Nilai</th>
                        <th class="px-5 py-3 font-medium">Total Kuota</th>
                        <th class="px-5 py-3 font-medium">Limit Per User</th>
                        <th class="px-5 py-3 font-medium">Masa Berlaku</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($codes as $code)
                    <tr class="hover:bg-indigo-500/5 transition-colors group">
                        <td class="px-5 py-3">
                            <div class="font-bold text-white tracking-wider font-mono text-base">{{ $code->code }}</div>
                            <div class="text-xs text-gray-500 mt-0.5 truncate max-w-[200px]">{{ $code->description ?? '-' }}</div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2.5 py-1 flex w-fit rounded-full text-xs font-bold border {{ $code->type === 'percentage' ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' }}">
                                {{ $code->type === 'percentage' ? number_format($code->value, 0) . '%' : 'Rp ' . number_format($code->value, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-bold text-gray-300 font-mono text-sm">
                                <span class="text-white">{{ $code->used_count }}</span> <span class="text-gray-500 mx-1">/</span> {{ $code->max_uses > 0 ? $code->max_uses : '∞' }}
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="font-bold text-gray-300 font-mono text-sm">
                                {{ $code->max_uses_per_customer > 0 ? $code->max_uses_per_customer . 'x' : '∞' }}
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="text-xs text-gray-400 space-y-0.5 font-mono">
                                <div><span class="text-gray-500 mr-1">Dari:</span> {{ $code->valid_from ? $code->valid_from->format('d M Y') : 'Selamanya' }}</div>
                                <div><span class="text-gray-500 mr-1">Hingga:</span> {{ $code->valid_until ? $code->valid_until->format('d M Y') : 'Selamanya' }}</div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @if($code->isValid())
                                <span class="px-2.5 py-1 flex w-fit rounded-full text-[10px] font-bold border bg-emerald-500/10 text-emerald-400 border-emerald-500/20">Aktif</span>
                            @else
                                <span class="px-2.5 py-1 flex w-fit rounded-full text-[10px] font-bold border bg-red-500/10 text-red-500 border-red-500/20">Tidak Aktif / Expired</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('redeem-codes.barcode', $code) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Barcode">
                                    <i data-lucide="barcode" class="w-4 h-4"></i>
                                </a>
                                <button type="button" @click="openEditModal({{ json_encode($code) }})" class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-amber-500/10 transition-colors" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <form action="{{ route('redeem-codes.destroy', $code) }}" method="POST" @submit="return confirm('Hapus kode ini?')" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-10 text-gray-500">
                            <div class="flex flex-col items-center gap-2">
                                <i data-lucide="ticket" class="w-8 h-8 opacity-50"></i>
                                <span>Belum ada data kode redeem</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $codes->links() }}
        </div>
    </div>

    {{-- Modal Form --}}
    <div x-show="modals.show" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.show = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between shrink-0">
                <h3 class="font-bold text-white flex items-center gap-2" x-text="modals.mode === 'create' ? '✨ Tambah Kode Redeem Baru' : '✏️ Edit Kode Redeem'"></h3>
                <button @click="modals.show = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <form :action="form.action" method="POST" class="flex flex-col overflow-hidden">
                @csrf
                <template x-if="modals.mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>
                
                <div class="p-5 flex-1 overflow-y-auto space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Kode String <span class="text-red-500">*</span></label>
                        <input type="text" name="code" x-model="form.code" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all uppercase font-mono font-bold tracking-widest placeholder:lowercase placeholder:font-sans placeholder:tracking-normal placeholder:font-normal" required placeholder="Contoh: LOYAL10">
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Tipe Diskon <span class="text-red-500">*</span></label>
                            <select name="type" x-model="form.type" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Nilai Diskon <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold" x-text="form.type === 'fixed' ? 'Rp' : '%'"></span>
                                <input type="number" name="value" x-model="form.value" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-8 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block flex items-center justify-between">Total Kuota <span class="text-[10px] text-gray-500 lowercase normal-case tracking-normal">(0 = ∞)</span></label>
                            <input type="number" name="max_uses" x-model="form.max_uses" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" min="0" required title="Total penggunaan di seluruh sistem">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block flex items-center justify-between">Limit Per User <span class="text-[10px] text-gray-500 lowercase normal-case tracking-normal">(0 = ∞)</span></label>
                            <input type="number" name="max_uses_per_customer" x-model="form.max_uses_per_customer" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" min="0" required title="Batas penggunaan per satu pelanggan">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Berlaku Dari</label>
                            <input type="date" name="valid_from" x-model="form.valid_from" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Berlaku Hingga</label>
                            <input type="date" name="valid_until" x-model="form.valid_until" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider block">Deskripsi</label>
                        <textarea name="description" x-model="form.description" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" rows="2" placeholder="Keterangan singkat kode promo"></textarea>
                    </div>

                    <div x-show="modals.mode === 'edit'" class="pt-2">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="checkbox" name="is_active" value="1" x-model="form.is_active" class="w-5 h-5 rounded border-gray-700 bg-gray-900 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-gray-800 transition-all cursor-pointer">
                            <span class="text-sm font-medium text-gray-300 group-hover:text-white transition-colors">Aktifkan Kode ini</span>
                        </label>
                    </div>
                </div>
                
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3 shrink-0">
                    <button type="button" @click="modals.show = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20" x-text="modals.mode === 'create' ? 'Simpan Kode' : 'Update Kode'"></button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('redeemCodesPage', () => ({
        modals: {
            show: false,
            mode: 'create'
        },
        form: {
            action: '',
            code: '',
            type: 'fixed',
            value: '',
            max_uses: 0,
            max_uses_per_customer: 1,
            valid_from: '',
            valid_until: '',
            description: '',
            is_active: true
        },

        openCreateModal() {
            this.modals.mode = 'create';
            this.form = {
                action: '{{ route('redeem-codes.store') }}',
                code: '',
                type: 'fixed',
                value: '',
                max_uses: 0,
                max_uses_per_customer: 1,
                valid_from: '',
                valid_until: '',
                description: '',
                is_active: true
            };
            this.modals.show = true;
        },

        openEditModal(code) {
            this.modals.mode = 'edit';
            this.form = {
                action: `/redeem-codes/${code.id}`,
                code: code.code,
                type: code.type,
                value: code.value,
                max_uses: code.max_uses,
                max_uses_per_customer: code.max_uses_per_customer,
                valid_from: code.valid_from ? code.valid_from.substring(0, 10) : '',
                valid_until: code.valid_until ? code.valid_until.substring(0, 10) : '',
                description: code.description || '',
                is_active: code.is_active == 1
            };
            this.modals.show = true;
        }
    }));
});
</script>
@endpush
@endsection

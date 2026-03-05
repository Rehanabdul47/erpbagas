@extends('layouts.app')

@section('title', 'Produksi')
@section('breadcrumb', 'Produksi')

@section('content')
<div class="space-y-6" x-data="productionPage()">
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

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h3 class="text-xl font-bold text-white mb-0">Daftar Produksi</h3>
        <a href="{{ route('production.create') }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-medium transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98] w-full sm:w-auto">
            <i data-lucide="plus" class="w-4 h-4"></i> Mulai Produksi
        </a>
    </div>

    <!-- Table Container -->
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden text-sm">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-3 font-medium">ID</th>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Produk Final</th>
                        <th class="px-5 py-3 font-medium">Jumlah</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Pembuat</th>
                        <th class="px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($productions as $prod)
                    <tr class="hover:bg-indigo-500/5 transition-colors group">
                        <td class="px-5 py-3 font-bold text-indigo-400 font-mono text-xs">#{{ $prod->id }}</td>
                        <td class="px-5 py-3 text-gray-300">{{ \Carbon\Carbon::parse($prod->date)->format('d M Y') }}</td>
                        <td class="px-5 py-3 font-bold text-white">{{ $prod->product->name }}</td>
                        <td class="px-5 py-3 text-gray-300 font-medium">{{ $prod->quantity }}</td>
                        <td class="px-5 py-3">
                            <div class="flex flex-col gap-2">
                                @if($prod->status === 'draft')
                                    <span class="px-2 py-1 rounded-full w-max text-[10px] font-bold border flex items-center gap-1.5 bg-gray-500/10 text-gray-400 border-gray-500/20">Draft</span>
                                @elseif($prod->status === 'processing')
                                    @php
                                        $stages = $prod->stages;
                                        $currentIndex = array_search($prod->stage, $stages);
                                        if ($currentIndex === false) $currentIndex = 0;
                                        $totalStages = count($stages);
                                    @endphp
                                    <div class="flex items-center justify-between text-[10px] font-bold text-amber-500 mb-1">
                                        <span>⏳ {{ $prod->stage_label }}</span>
                                        <span class="text-gray-500">{{ $currentIndex + 1 }}/{{ $totalStages }}</span>
                                    </div>
                                    <div class="w-full h-1.5 bg-gray-700 rounded-full overflow-hidden flex gap-0.5 mb-1">
                                        @foreach($stages as $index => $s)
                                            <div class="h-full flex-1 {{ $currentIndex >= $index ? 'bg-amber-500' : 'bg-gray-600/50' }}" title="{{ $s }}"></div>
                                        @endforeach
                                    </div>
                                    @if($prod->tailor_id || $prod->cost > 0)
                                    <div class="mt-1.5 text-[10px] font-bold text-indigo-400 border border-indigo-500/20 px-2 py-0.5 rounded flex items-center justify-center gap-1 w-full bg-indigo-500/10">
                                        @if($prod->tailor_id)
                                            <i data-lucide="scissors" class="w-3 h-3"></i> {{ $prod->tailor->name }}
                                        @else
                                            <i data-lucide="factory" class="w-3 h-3"></i> Internal
                                        @endif
                                        @if($prod->cost > 0)
                                            <span class="ml-1 text-gray-400 font-normal">| Rp {{ number_format($prod->cost, 0, ',', '.') }}/pcs (Tot: Rp {{ number_format($prod->cost * $prod->quantity, 0, ',', '.') }})</span>
                                        @endif
                                    </div>
                                    @endif
                                @elseif($prod->status === 'completed')
                                    <span class="px-2 py-1 rounded-full w-max text-[10px] font-bold border flex items-center gap-1.5 bg-emerald-500/10 text-emerald-400 border-emerald-500/20">✅ Selesai</span>
                                @elseif($prod->status === 'cancelled')
                                    <span class="px-2 py-1 rounded-full w-max text-[10px] font-bold border flex items-center gap-1.5 bg-red-500/10 text-red-400 border-red-500/20">❌ Batal</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold border bg-gray-500/10 text-gray-400 border-gray-500/20">{{ $prod->user->name ?? 'Sistem' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @if($prod->status === 'processing')
                                <div class="flex items-center gap-2 opacity-100 sm:opacity-50 sm:group-hover:opacity-100 transition-opacity">
                                    @if($prod->is_last_stage)
                                        <button type="button" 
                                            @click="openConfirmModal('finish', 'Selesaikan Produksi?', 'Produksi sudah di tahap akhir. Tandai selesai dan masukkan ke stok?', '{{ route('production.update-status', $prod) }}', 'PUT', 'completed')"
                                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-900/20">
                                            <i data-lucide="check-check" class="w-3.5 h-3.5"></i> Selesai
                                        </button>
                                    @else
                                        <button type="button" 
                                            @click="openConfirmModal('advance', 'Ke Tahap Selanjutnya?', 'Lanjutkan ke tahap \'{{ addslashes($prod->getNextStage() ?? '') }}\'?', '{{ route('production.advance-stage', $prod) }}', 'PUT')"
                                            class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors bg-indigo-600 hover:bg-indigo-500 text-white shadow-indigo-900/20">
                                            Tahap Selanjutnya <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                        </button>
                                    @endif
                                    <button type="button" @click="openTailorModal({{ $prod->id }}, {{ $prod->tailor_id ?? 'null' }})" class="p-1.5 rounded-lg bg-gray-700 text-gray-300 hover:text-white hover:bg-gray-600 transition-colors" title="Kelola Penjahit">
                                        <i data-lucide="scissors" class="w-4 h-4"></i>
                                    </button>
                                    <button type="button" 
                                        @click="openConfirmModal('cancel', 'Batalkan Produksi?', 'Batalkan produksi dan kembalikan bahan baku? Tindakan ini tidak dapat dibatalkan.', '{{ route('production.update-status', $prod) }}', 'PUT', 'cancelled')"
                                        class="p-1.5 rounded-lg bg-red-500/10 text-red-500 hover:text-white hover:bg-red-500 transition-colors" title="Batal Produksi">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            @else
                                <span class="text-gray-500 text-sm">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i data-lucide="package-open" class="w-12 h-12 text-gray-600 opacity-50"></i>
                                <span>Belum ada data produksi</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tailor Modal --}}
    <div x-show="tailorModal" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4">
        <div @click.away="tailorModal = false" class="bg-gray-800 border border-gray-700 rounded-xl max-w-sm w-full outline-none overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2"><i data-lucide="scissors" class="w-5 h-5 text-indigo-400"></i> Pilih Penjahit Vendor</h3>
                <button type="button" @click="tailorModal = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="`/production/${selectedProdId}/assign-tailor`" method="POST">
                @csrf @method('PUT')
                <div class="px-5 py-6">
                    <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-2">Pilih Vendor Penjahit</label>
                    <select name="tailor_id" x-model="selectedTailorId" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">-- Tanpa Vendor (Internal) --</option>
                        @foreach($tailors ?? [] as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="tailorModal = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Universal Confirmation Modal --}}
    <div x-show="confirmModal" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[200] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="confirmModal = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="alert-circle" x-show="confirmType === 'advance'" class="w-5 h-5 text-indigo-400"></i>
                    <i data-lucide="check-check" x-show="confirmType === 'finish'" class="w-5 h-5 text-emerald-400"></i>
                    <i data-lucide="alert-triangle" x-show="confirmType === 'cancel'" class="w-5 h-5 text-red-500"></i>
                    <span x-text="confirmTitle"> Konfirmasi</span>
                </h3>
                <button type="button" @click="confirmModal = false" class="text-gray-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-5 flex flex-col gap-4 text-center items-center justify-center">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mb-2 shadow-inner"
                     :class="confirmType === 'advance' ? 'bg-indigo-500/10 text-indigo-500' : (confirmType === 'finish' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-500')">
                    <i data-lucide="arrow-right" x-show="confirmType === 'advance'" class="w-8 h-8"></i>
                    <i data-lucide="check-check" x-show="confirmType === 'finish'" class="w-8 h-8"></i>
                    <i data-lucide="x" x-show="confirmType === 'cancel'" class="w-8 h-8"></i>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-white mb-1" x-text="confirmTitle"></h4>
                    <p class="text-sm text-gray-400" x-text="confirmDesc"></p>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                <button type="button" @click="confirmModal = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                <form :action="confirmAction" method="POST" class="m-0">
                    @csrf 
                    <template x-if="confirmMethod !== 'POST'">
                        <input type="hidden" name="_method" :value="confirmMethod">
                    </template>
                    <input type="hidden" name="status" :value="confirmStatusValue">
                    <button type="submit" 
                        class="px-4 py-2 text-white rounded-lg text-sm font-medium transition-colors shadow-sm"
                        :class="confirmType === 'advance' ? 'bg-indigo-600 hover:bg-indigo-500 border-indigo-500' : (confirmType === 'finish' ? 'bg-emerald-600 hover:bg-emerald-500 border-emerald-500' : 'bg-red-600 hover:bg-red-500 border-red-500')">
                        Konfirmasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productionPage', () => ({
        tailorModal: false,
        selectedProdId: null,
        selectedTailorId: '',

        confirmModal: false,
        confirmType: '',
        confirmTitle: '',
        confirmDesc: '',
        confirmAction: '',
        confirmMethod: 'POST',
        confirmStatusValue: '',

        openTailorModal(prodId, tailorId) {
            this.selectedProdId = prodId;
            this.selectedTailorId = tailorId || '';
            this.tailorModal = true;
        },

        openConfirmModal(type, title, desc, action, method = 'POST', status = '') {
            this.confirmType = type;
            this.confirmTitle = title;
            this.confirmDesc = desc;
            this.confirmAction = action;
            this.confirmMethod = method;
            this.confirmStatusValue = status;
            this.confirmModal = true;
            this.$nextTick(() => lucide.createIcons());
        }
    }));
});
</script>
@endpush
@endsection

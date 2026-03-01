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
                                    @if($prod->tailor_id)
                                    <div class="mt-1.5 text-[10px] font-bold text-indigo-400 border border-indigo-500/20 px-2 py-0.5 rounded flex items-center justify-center gap-1 w-full bg-indigo-500/10">
                                        <i data-lucide="scissors" class="w-3 h-3"></i> {{ $prod->tailor->name }}
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
                                        <form action="{{ route('production.update-status', $prod) }}" method="POST" onsubmit="return confirm('Produksi sudah di tahap akhir. Tandai selesai dan masukkan ke stok?')" class="m-0">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-900/20">
                                                <i data-lucide="check-check" class="w-3.5 h-3.5"></i> Selesai
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('production.advance-stage', $prod) }}" method="POST" class="m-0" 
                                              onsubmit="return confirm('Lanjutkan ke tahap \'{{ addslashes($prod->getNextStage() ?? '') }}\'?')">
                                            @csrf @method('PUT')
                                            <button type="submit" class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold transition-colors bg-indigo-600 hover:bg-indigo-500 text-white shadow-indigo-900/20">
                                                Tahap Selanjutnya <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button type="button" @click="openTailorModal({{ $prod->id }}, {{ $prod->tailor_id ?? 'null' }})" class="p-1.5 rounded-lg bg-gray-700 text-gray-300 hover:text-white hover:bg-gray-600 transition-colors" title="Kelola Penjahit">
                                        <i data-lucide="scissors" class="w-4 h-4"></i>
                                    </button>
                                    <form action="{{ route('production.update-status', $prod) }}" method="POST" onsubmit="return confirm('Batalkan produksi dan kembalikan bahan baku?')" class="m-0 ml-1">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="p-1.5 rounded-lg bg-red-500/10 text-red-500 hover:text-white hover:bg-red-500 transition-colors" title="Batal Produksi">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </form>
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
        <div @click.away="tailorModal = false" class="bg-gray-800 border border-gray-700 rounded-xl max-w-sm w-full outline-none">
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
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('productionPage', () => ({
        tailorModal: false,
        selectedProdId: null,
        selectedTailorId: '',

        openTailorModal(prodId, tailorId) {
            this.selectedProdId = prodId;
            this.selectedTailorId = tailorId || '';
            this.tailorModal = true;
        }
    }));
});
</script>
@endpush
@endsection

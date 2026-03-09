@extends('layouts.app')

@section('content')
<div class="px-6 py-8 md:px-8 max-w-7xl mx-auto min-h-screen" x-data="transferPage()">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <i data-lucide="arrow-right-left" class="w-6 h-6 text-indigo-400"></i>
                Transfer Stok Cabang
            </h1>
            <p class="text-sm text-gray-400 font-medium tracking-wide flex items-center gap-1.5 uppercase">
                <span class="w-2 h-2 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></span>
                Manajemen Distribusi & Logistik
            </p>
        </div>
        <a href="{{ route('production.transfers.create') }}" class="flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98] w-full sm:w-auto">
            <i data-lucide="plus" class="w-4 h-4"></i> Transfer Stok Baru
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded-xl flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5"></i>
        {{ session('success') }}
    </div>
    @endif
    
    @if(session('error'))
    <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 px-4 py-3 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-gray-800/50 border border-gray-700/50 rounded-2xl overflow-hidden shadow-xl backdrop-blur-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 font-medium">Tanggal Transfer</th>
                        <th class="px-6 py-4 font-medium text-center">Ref ID</th>
                        <th class="px-6 py-4 font-medium">Alur Distribusi</th>
                        <th class="px-6 py-4 font-medium">Spesifikasi Produk</th>
                        <th class="px-6 py-4 font-medium">Qty</th>
                        <th class="px-6 py-4 font-medium text-center">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($transfers as $trf)
                    <tr class="hover:bg-gray-700/20 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-300 whitespace-nowrap">
                            <i data-lucide="calendar" class="w-4 h-4 inline-block mr-1 text-gray-500"></i>
                            {{ $trf->created_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                <i data-lucide="hash" class="w-3 h-3"></i>
                                {{ $trf->reference }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-200">
                                    <i data-lucide="store" class="w-4 h-4 inline-block text-gray-400 mr-1"></i>
                                    {{ $trf->sourceBranch->name }}
                                </span>
                                <i data-lucide="arrow-right" class="w-4 h-4 text-gray-500"></i>
                                <span class="text-sm font-semibold text-indigo-300">
                                    <i data-lucide="store" class="w-4 h-4 inline-block opacity-70 mr-1"></i>
                                    {{ $trf->destinationBranch->name }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div>
                                    <p class="text-sm font-bold text-gray-200">{{ $trf->product->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $trf->product->sku }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($trf->received_quantity !== null)
                                <div class="space-y-1">
                                    <span class="text-sm font-bold text-gray-200 block">{{ number_format($trf->received_quantity) }} unit</span>
                                    @if($trf->received_quantity < $trf->quantity)
                                        <span class="text-[10px] text-red-500 font-bold bg-red-500/10 px-1.5 py-0.5 rounded border border-red-500/20">
                                            -{{ $trf->quantity - $trf->received_quantity }} Hilang/Rusak
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="text-sm font-bold text-gray-200">{{ number_format($trf->quantity) }} unit</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($trf->status === 'pending')
                                <span class="mx-auto px-2 py-1 rounded-full text-[10px] font-bold border flex items-center justify-center gap-1.5 bg-gray-500/10 text-gray-400 border-gray-500/20 w-max">
                                    <i data-lucide="clock" class="w-3 h-3"></i> Pending
                                </span>
                            @elseif($trf->status === 'sending')
                                <span class="mx-auto px-2 py-1 rounded-full text-[10px] font-bold border flex items-center justify-center gap-1.5 bg-amber-500/10 text-amber-500 border-amber-500/20 w-max animate-pulse">
                                    <i data-lucide="truck" class="w-3 h-3"></i> Dikirim
                                </span>
                            @elseif($trf->status === 'received')
                                <span class="mx-auto px-2 py-1 rounded-full text-[10px] font-bold border flex items-center justify-center gap-1.5 bg-emerald-500/10 text-emerald-400 border-emerald-500/20 w-max">
                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Diterima
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($trf->status === 'pending')
                                    <button @click="openConfirmModal('sending', 'Kirim Barang?', 'Tandai barang {{ $trf->reference }} sedang dalam proses pengiriman?', '{{ route('production.transfers.update-status', $trf) }}', 'sending')" 
                                            class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1 transition-colors bg-indigo-500/5 px-2.5 py-1.5 rounded-lg border border-indigo-500/20 hover:border-indigo-500/40">
                                        <i data-lucide="send" class="w-3.5 h-3.5"></i> Proses Kirim
                                    </button>
                                @elseif($trf->status === 'sending')
                                    <button @click="openConfirmModal('received', 'Terima Barang?', 'Konfirmasi bahwa barang {{ $trf->reference }} telah diterima. Tentukan jumlah aktual yang diterima jika ada kerusakan/kehilangan.', '{{ route('production.transfers.update-status', $trf) }}', 'received', {{ $trf->quantity }})"
                                            class="text-xs font-bold text-emerald-400 hover:text-emerald-300 flex items-center gap-1 transition-colors bg-emerald-500/5 px-2.5 py-1.5 rounded-lg border border-emerald-500/20 hover:border-emerald-500/40">
                                        <i data-lucide="package-check" class="w-3.5 h-3.5"></i> Konfirmasi Terima
                                    </button>
                                @else
                                    <span class="text-xs text-gray-500 flex items-center gap-1 px-2.5 py-1.5 italic">
                                        <i data-lucide="check-check" class="w-3.5 h-3.5 text-gray-600"></i> Done
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 border-none">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-20"></i>
                            <p class="font-medium">Belum ada riwayat transfer barang</p>
                            <p class="text-xs mt-1">Gunakan tombol 'Transfer Stok Baru' untuk memulai</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($transfers->hasPages())
        <div class="p-6 border-t border-gray-700/50">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    {{-- Confirmation Modal --}}
    <div x-show="confirmModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/80 backdrop-blur-sm"
         x-cloak>
        <div class="bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden" @click.away="confirmModal = false">
            <div class="p-6 text-center">
                <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center shadow-lg"
                     :class="confirmType === 'sending' ? 'bg-indigo-500/10 text-indigo-400' : 'bg-emerald-500/10 text-emerald-400'">
                    <i :data-lucide="confirmType === 'sending' ? 'truck' : 'package-check'" class="w-8 h-8"></i>
                </div>
                <h3 class="text-xl font-bold text-white mb-2" x-text="confirmTitle"></h3>
                <p class="text-sm text-gray-400 leading-relaxed mb-4" x-text="confirmDesc"></p>

                <template x-if="confirmType === 'received'">
                    <div class="space-y-4 text-left border-t border-gray-700/50 pt-4 mt-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest flex justify-between">
                                <span>Jumlah Diterima (Kondisi Baik)</span>
                                <span class="text-indigo-400" x-text="`Target: ${transferQty}`"></span>
                            </label>
                            <input type="number" name="received_quantity" x-model="receivedQty" :max="transferQty" min="0"
                                   class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                            <p x-show="receivedQty < transferQty" class="text-[10px] text-red-500 font-bold italic">
                                * Peringatan: Terdeteksi selisih <span x-text="transferQty - receivedQty"></span> unit yang hilang/rusak.
                            </p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Catatan Kedatangan (Opsional)</label>
                            <textarea name="arrival_notes" x-model="arrivalNotes" rows="2" placeholder="Contoh: 2 kardus basah, 1 unit pecah..."
                                      class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></textarea>
                        </div>
                    </div>
                </template>
            </div>
            <div class="px-6 py-4 bg-gray-900/50 flex justify-end gap-3 border-t border-gray-700/50">
                <button @click="confirmModal = false" class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-white transition-colors">Batal</button>
                <form :action="confirmAction" method="POST">
                    @csrf @method('PUT')
                    <input type="hidden" name="status" :value="confirmStatus">
                    <input type="hidden" name="received_quantity" :value="receivedQty">
                    <input type="hidden" name="arrival_notes" :value="arrivalNotes">
                    <button type="submit" 
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white shadow-lg transition-all active:scale-95"
                            :class="confirmType === 'sending' ? 'bg-indigo-600 hover:bg-indigo-500 shadow-indigo-600/20' : 'bg-emerald-600 hover:bg-emerald-500 shadow-emerald-600/20'">
                        Konfirmasi & Update Stok
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('transferPage', () => ({
            confirmModal: false,
            confirmType: '',
            confirmTitle: '',
            confirmDesc: '',
            confirmAction: '',
            confirmStatus: '',
            transferQty: 0,
            receivedQty: 0,
            arrivalNotes: '',

            openConfirmModal(type, title, desc, action, status, qty = 0) {
                this.confirmType = type;
                this.confirmTitle = title;
                this.confirmDesc = desc;
                this.confirmAction = action;
                this.confirmStatus = status;
                this.transferQty = qty;
                this.receivedQty = qty; // default to full quantity
                this.arrivalNotes = '';
                this.confirmModal = true;
                
                // Re-init lucide icons inside modal
                this.$nextTick(() => {
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                });
            }
        }));
    });
</script>
@endpush
@endsection

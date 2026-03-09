@extends('layouts.app')

@section('content')
<div class="px-6 py-8 md:px-8 max-w-4xl mx-auto min-h-screen">
    <div class="mb-10">
        <a href="{{ route('production.transfers.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-800/50 border border-gray-700/50 text-xs font-bold text-gray-400 hover:text-white hover:bg-gray-700/50 hover:border-indigo-500/50 transition-all mb-6 group uppercase tracking-widest">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 group-hover:-translate-x-1 transition-transform"></i> Kembali ke Riwayat
        </a>
        <div class="space-y-1">
            <h1 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-6 h-6 text-indigo-400"></i>
                Buat Transfer Baru
            </h1>
            <p class="text-sm text-gray-400 font-medium tracking-wide flex items-center gap-1.5 uppercase">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                Formulir Distribusi Antar Unit Bisnis
            </p>
        </div>
    </div>

    @if(session('error'))
    <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 px-4 py-3 rounded-xl flex items-center gap-3">
        <i data-lucide="alert-circle" class="w-5 h-5"></i>
        {{ session('error') }}
    </div>
    @endif
    
    @if ($errors->any())
        <div class="mb-6 bg-red-500/10 border border-red-500/50 text-red-500 px-4 py-3 rounded-xl">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('production.transfers.store') }}" method="POST" class="bg-gray-800/50 backdrop-blur-xl border border-gray-700/50 rounded-2xl p-6 md:p-8 shadow-xl">
        @csrf
        
        <div class="space-y-6">
            <!-- Source & Destination Branch -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="store" class="w-3.5 h-3.5 text-indigo-400"></i>
                        Unit Asal (Source)
                    </label>
                    <select name="source_branch_id" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        @foreach($sourceBranches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-400"></i>
                        Unit Tujuan (Destination)
                    </label>
                    <select name="destination_branch_id" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="">Pilih Cabang Tujuan</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Product Selection -->
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="package" class="w-3.5 h-3.5 text-purple-400"></i>
                    Spesifikasi Produk
                </label>
                <select name="product_id" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition-all select2" data-placeholder="Cari SKU atau nama produk...">
                    <option value="">Pilih Produk</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-stock="{{ $product->stock }}">
                            {{ $product->sku }} - {{ $product->name }} (Stok: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1" id="stock-info">Silakan pilih produk untuk melihat sisa stok.</p>
            </div>

            <!-- Quantity -->
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-orange-400"></i>
                    Kuantitas Transfer
                </label>
                <div class="relative">
                    <input type="number" name="quantity" required min="1" id="transfer-quantity"
                        class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition-all pl-12"
                        placeholder="Contoh: 10">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <i data-lucide="hash" class="w-5 h-5 text-gray-500"></i>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="space-y-2">
                <label class="text-[10px] font-bold text-gray-500 uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-blue-400"></i>
                    Justifikasi / Catatan
                </label>
                <textarea name="notes" rows="3" 
                    class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
                    placeholder="Contoh: Barang untuk display toko depan."></textarea>
            </div>
        </div>

        <div class="border-t border-gray-700/50 mt-8 pt-6 flex justify-end">
            <button type="submit" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all flex items-center gap-2 group">
                <i data-lucide="send" class="w-5 h-5 group-hover:-translate-y-1 group-hover:translate-x-1 transition-transform"></i>
                Kirim Barang
            </button>
        </div>
    </form>
</div>

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'classic',
            width: '100%',
        });
        
        // Update stock info and max value on product change
        $('select[name="product_id"]').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var stock = selectedOption.data('stock');
            var stockInfo = $('#stock-info');
            var qtyInput = $('#transfer-quantity');
            
            if (stock !== undefined) {
                stockInfo.html('<span class="text-green-400"><i data-lucide="check-circle" class="w-3 h-3 inline"></i> Stok tersedia: ' + stock + ' unit</span>');
                qtyInput.attr('max', stock);
                lucide.createIcons();
            } else {
                stockInfo.text('Silakan pilih produk untuk melihat sisa stok.');
                qtyInput.removeAttr('max');
            }
        });
    });
</script>
<style>
    .select2-container--classic .select2-selection--single {
        background-color: #111827 !important;
        border-color: #374151 !important;
        border-radius: 0.75rem !important;
        height: auto !important;
        padding: 0.5rem 1rem !important;
        color: white !important;
    }
    .select2-container--classic .select2-selection--single .select2-selection__rendered {
        color: white !important;
        line-height: inherit !important;
    }
    .select2-container--classic .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        top: 0 !important;
    }
    .select2-container--classic .select2-dropdown {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        color: white !important;
    }
    .select2-container--classic .select2-results__option--highlighted[aria-selected] {
        background-color: #4f46e5 !important;
    }
</style>
@endpush
@endsection

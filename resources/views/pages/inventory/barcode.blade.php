@extends('layouts.app')

@section('title', 'Barcode Produk - ' . $product->name)

@section('content')
<div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm max-w-2xl mx-auto p-10 text-center relative print:border-none print:shadow-none print:bg-white print:p-0">
    <div class="mb-8 print:hidden">
        <h2 class="text-2xl font-bold text-white mb-2">Barcode Produk</h2>
        <p class="text-gray-400">{{ $product->name }}</p>
    </div>

    <!-- Print Canvas Area -->
    <div class="inline-block bg-white p-10 border border-gray-200 rounded-2xl mb-8 print:border-none print:m-0 print:p-4">
        <div class="mb-3 font-bold text-xl text-black tracking-widest">
            {{ $product->sku }}
            <div class="text-[10px] font-normal tracking-normal text-gray-500 mt-1 uppercase">{{ $product->branch->name ?? 'Pusat' }}</div>
        </div>
        
        <div class="mb-3 flex justify-center">
            {!! Milon\Barcode\Facades\DNS1DFacade::getBarcodeHTML($product->sku, 'C128', 2, 80) !!}
        </div>
        
        <div class="text-sm text-gray-700 mt-4">
            <div class="font-medium mb-1">{{ $product->name }}</div>
            <div class="font-black text-lg">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="flex items-center justify-center gap-4 print:hidden">
        <button onclick="window.print()" class="flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-medium transition-colors shadow-lg shadow-indigo-600/20">
            <i data-lucide="printer" class="w-5 h-5"></i> Cetak Barcode
        </button>
        <a href="{{ route('inventory.index') }}" class="flex items-center gap-2 px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i> Kembali
        </a>
    </div>
</div>

@push('styles')
<style>
@media print {
    /* Hide everything else */
    body > *:not(main) { display: none !important; }
    main > *:not(#page-content) { display: none !important; }
    
    /* Layout adjustments for print */
    body, main, #page-content {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }
}
</style>
@endpush
@endsection

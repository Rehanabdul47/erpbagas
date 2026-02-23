@extends('layouts.app')

@section('title', 'Barcode Produk - ' . $product->name)

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto; padding: 40px; text-align: center;">
    <div style="margin-bottom: 30px;">
        <h2 style="margin: 0 0 8px 0;">Barcode Produk</h2>
        <p style="color: var(--text-tertiary); margin: 0;">{{ $product->name }}</p>
    </div>

    <div class="barcode-container" style="background: white; padding: 40px; border: 1px solid var(--border-color); border-radius: var(--radius-lg); display: inline-block; margin-bottom: 30px;">
        <div style="margin-bottom: 12px; font-weight: 700; font-size: 18px; color: black;">
            {{ $product->sku }}
        </div>
        
        <div style="margin-bottom: 12px;">
            {!! Milon\Barcode\Facades\DNS1DFacade::getBarcodeHTML($product->sku, 'C128', 2, 80) !!}
        </div>
        
        <div style="font-size: 14px; color: #555;">
            {{ $product->name }} <br>
            <span style="font-weight: 700;">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: center;" class="no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i data-lucide="printer"></i> Cetak Barcode
        </button>
        <a href="{{ route('inventory.index') }}" class="btn btn-ghost">
            <i data-lucide="arrow-left"></i> Kembali
        </a>
    </div>
</div>

<style>
@media print {
    .sidebar, .navbar, .no-print, .breadcrumb-container {
        display: none !important;
    }
    .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 auto !important;
    }
    body {
        background: white !important;
    }
}
</style>
@endsection

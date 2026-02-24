@extends('layouts.app')

@section('title', 'Barcode Promo - ' . $code->code)
@section('breadcrumb', 'Redeem Codes / Barcode')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto; padding: 40px; text-align: center;">
    <div style="margin-bottom: 30px;">
        <h2 style="margin: 0 0 8px 0;">Barcode Kode Promo</h2>
        <p style="color: var(--text-tertiary); margin: 0;">{{ $code->description ?? 'Potongan Harga' }}</p>
    </div>

    <div class="barcode-container" style="background: white; padding: 40px; border: 1px solid var(--border-color); border-radius: var(--radius-lg); display: inline-block; margin-bottom: 30px;">
        <div style="margin-bottom: 12px; font-weight: 700; font-size: 24px; color: black; letter-spacing: 2px;">
            {{ $code->code }}
        </div>
        
        <div style="margin-bottom: 12px;">
            {!! Milon\Barcode\Facades\DNS1DFacade::getBarcodeHTML($code->code, 'C128', 2, 80) !!}
        </div>
        
        <div style="font-size: 16px; color: #555;">
            Diskon: 
            <span style="font-weight: 700; color: var(--success-500);">
                {{ $code->type === 'percentage' ? number_format($code->value, 0) . '%' : 'Rp ' . number_format($code->value, 0, ',', '.') }}
            </span>
            <br>
            <span style="font-size: 12px; color: var(--text-tertiary);">
                Berlaku hingga: {{ $code->valid_until ? $code->valid_until->format('d M Y') : 'Selamanya' }}
            </span>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: center;" class="no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i data-lucide="printer"></i> Cetak Barcode
        </button>
        <a href="{{ route('redeem-codes.index') }}" class="btn btn-ghost">
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

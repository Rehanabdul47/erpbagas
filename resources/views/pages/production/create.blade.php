@extends('layouts.app')

@section('title', 'Mulai Produksi')
@section('breadcrumb')
<a href="{{ route('production.index') }}" style="color:var(--text-tertiary);text-decoration:none">Produksi</a>
<span style="margin:0 8px;color:var(--text-tertiary)">/</span>
<span>Mulai Produksi</span>
@endsection

@section('content')
<div class="card" style="max-width:800px">
    <h3 style="margin-bottom:20px">⚙️ Mulai Produksi Baru</h3>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:16px;color:#f87171;font-size:13px">
        @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-error" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:var(--danger-400);display:flex;align-items:center;gap:8px;">
        <i data-lucide="x-circle" style="width:18px;height:18px"></i> {{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('production.store') }}" id="production-form">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px">
            <div class="form-group" style="grid-column: span 2">
                <label>Produk Final <span style="color:var(--danger-400)">*</span></label>
                <select name="product_id" id="product_id" class="form-control" required onchange="calculateBom()">
                    <option value="">Pilih produk yang akan diproduksi...</option>
                    @foreach($finalProducts as $fp)
                    <option value="{{ $fp->id }}" data-bom="{{ json_encode($fp->billOfMaterialsAsFinal->map(function($b) { return ['name' => $b->rawMaterial->name, 'req_qty' => $b->quantity, 'stock' => $b->rawMaterial->stock]; })) }}">
                        {{ $fp->sku }} — {{ $fp->name }} (Stok Final: {{ $fp->stock }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah Produksi <span style="color:var(--danger-400)">*</span></label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="{{ old('quantity', 1) }}" min="1" required oninput="calculateBom()">
            </div>
            <div class="form-group">
                <label>Tanggal Mulai <span style="color:var(--danger-400)">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
            </div>
            
            <div class="form-group" style="grid-column: span 2; background:var(--bg-secondary); padding:16px; border-radius:8px; border:1px solid var(--border-color);">
                <label style="margin-bottom:12px;display:block;font-weight:700">📋 Kebutuhan Bahan Baku (BoM)</label>
                <div id="bom-preview" style="font-size:14px">
                    <div style="color:var(--text-tertiary);font-style:italic">Silakan pilih produk final terlebih dahulu.</div>
                </div>
            </div>

            <div class="form-group" style="grid-column: span 2">
                <label>Catatan Tambahan</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Catatan produksi...">{{ old('notes') }}</textarea>
            </div>
        </div>
        <div style="display:flex;gap:12px">
            <button type="submit" class="btn btn-primary" id="btn-submit"><i data-lucide="play-circle"></i> Mulai Proses Produksi</button>
            <a href="{{ route('production.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function calculateBom() {
    const select = document.getElementById('product_id');
    const qtyInput = document.getElementById('quantity').value;
    const previewArea = document.getElementById('bom-preview');
    const btnSubmit = document.getElementById('btn-submit');
    
    if(!select.value) {
        previewArea.innerHTML = '<div style="color:var(--text-tertiary);font-style:italic">Silakan pilih produk final terlebih dahulu.</div>';
        btnSubmit.disabled = false;
        return;
    }

    const option = select.options[select.selectedIndex];
    const boms = JSON.parse(option.getAttribute('data-bom'));
    const produceQty = parseInt(qtyInput) || 0;
    
    if(boms.length === 0) {
        previewArea.innerHTML = '<div style="color:var(--warning-400);padding:8px;background:rgba(234,179,8,0.1);border-radius:4px">⚠️ Produk ini belum memiliki Bill of Materials (Resep Bahan). Produksi akan tetap tercatat tanpa memotong bahan baku.</div>';
        btnSubmit.disabled = false;
        return;
    }

    let html = '<table style="width:100%;text-align:left;border-collapse:collapse">';
    html += '<thead><tr style="border-bottom:1px solid var(--border-color);color:var(--text-tertiary);font-size:12px"><th style="padding-bottom:8px">Bahan Baku</th><th style="padding-bottom:8px">Dibutuhkan</th><th style="padding-bottom:8px">Stok Tersedia</th><th style="padding-bottom:8px">Status</th></tr></thead><tbody>';
    
    let hasError = false;

    boms.forEach(b => {
        const totalNeeded = b.req_qty * produceQty;
        const isEnough = b.stock >= totalNeeded;
        if(!isEnough) hasError = true;
        
        html += `<tr style="border-bottom:1px dashed var(--border-color)"><td style="padding:8px 0;font-weight:600">${b.name}</td><td style="padding:8px 0">${totalNeeded}</td><td style="padding:8px 0">${b.stock}</td>`;
        html += `<td style="padding:8px 0">${isEnough ? '<span class="badge badge-success">Cukup</span>' : '<span class="badge badge-danger">Kurang</span>'}</td></tr>`;
    });
    
    html += '</tbody></table>';

    if(hasError) {
        html += '<div style="margin-top:12px;color:var(--danger-400);font-size:12px;font-weight:600">🔴 Stok bahan baku tidak mencukupi untuk memproduksi jumlah tersebut.</div>';
        // Optional: disable button
        // btnSubmit.disabled = true; 
    } else {
        btnSubmit.disabled = false;
    }

    previewArea.innerHTML = html;
}

// init on load (for old input)
document.addEventListener('DOMContentLoaded', calculateBom);
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'Edit Produk')
@section('breadcrumb')
<a href="{{ route('inventory.index') }}" style="color:var(--text-tertiary);text-decoration:none">Inventory</a>
<span style="margin:0 8px;color:var(--text-tertiary)">/</span>
<span>Edit {{ $product->name }}</span>
@endsection

@section('content')
<div class="card" style="max-width:700px">
    <h3 style="margin-bottom:20px">✏️ Edit Produk — {{ $product->sku }}</h3>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:16px;color:#f87171;font-size:13px">
        @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('inventory.update', $product) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            {{-- Category, SKU, Name fields... --}}
            <div class="form-group">
                <label>SKU <span style="color:var(--danger-400)">*</span></label>
                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}" required>
            </div>
            <div class="form-group">
                <label>Nama Produk <span style="color:var(--danger-400)">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="form-group">
                <label>Kategori <span style="color:var(--danger-400)">*</span></label>
                <select name="category_id" class="form-control" required>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="location" class="form-control" value="{{ old('location', $product->location) }}">
            </div>
            <div class="form-group">
                <label>Stok Saat Ini</label>
                <input type="number" class="form-control" value="{{ $product->stock }}" disabled style="opacity:0.6">
                <small style="color:var(--text-tertiary)">Gunakan Stok Masuk/Keluar untuk mengubah stok</small>
            </div>
            <div class="form-group">
                <label>Safety Stock <span style="color:var(--danger-400)">*</span></label>
                <input type="number" name="safety_stock" class="form-control" value="{{ old('safety_stock', $product->safety_stock) }}" min="0" required>
            </div>
            <div class="form-group">
                <label>Harga Jual <span style="color:var(--danger-400)">*</span></label>
                <input type="number" name="price" class="form-control" value="{{ old('price', $product->price) }}" min="0" step="1000" required>
            </div>
            <div class="form-group">
                <label>COGS <span style="color:var(--danger-400)">*</span></label>
                <input type="number" name="cogs" class="form-control" value="{{ old('cogs', $product->cogs) }}" min="0" step="1000" required>
            </div>

            <div class="form-group" style="grid-column:span 2;background:var(--bg-secondary);padding:16px;border-radius:var(--radius-md);border:1px dashed var(--border-color)">
                <label style="margin-bottom:12px;display:block;font-weight:700">🖼️ Gambar Produk</label>
                
                @if($product->image)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;padding:8px;background:var(--bg-surface);border-radius:4px;border:1px solid var(--border-color)">
                    <img src="{{ Str::startsWith($product->image, 'http') ? $product->image : asset($product->image) }}" style="width:48px;height:48px;object-fit:cover;border-radius:4px">
                    <div style="font-size:12px;color:var(--text-tertiary)">Gambar saat ini: <br> <span style="color:var(--text-secondary)">{{ basename($product->image) }}</span></div>
                </div>
                @endif

                <div style="display:flex;gap:12px;margin-bottom:16px">
                    <button type="button" class="btn btn-sm btn-outline active" id="btn-mode-url" onclick="setImageMode('url')">Link URL</button>
                    <button type="button" class="btn btn-sm btn-outline" id="btn-mode-upload" onclick="setImageMode('upload')">Upload File</button>
                </div>
                
                <div id="input-url">
                    <label style="font-size:12px;color:var(--text-tertiary)">Ganti URL gambar</label>
                    <input type="text" name="image" class="form-control" value="{{ old('image', $product->image) }}" placeholder="https://example.com/image.jpg">
                </div>
                
                <div id="input-upload" style="display:none">
                    <label style="font-size:12px;color:var(--text-tertiary)">Upload file baru (Max 2MB)</label>
                    <input type="file" name="image_file" class="form-control" style="padding:8px">
                </div>
            </div>

            <div class="form-group" style="grid-column:span 2">
                <label>Batch</label>
                <input type="text" name="batch" class="form-control" value="{{ old('batch', $product->batch) }}">
            </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:20px">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Update</button>
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
function setImageMode(mode) {
    const inputUrl = document.getElementById('input-url');
    const inputUpload = document.getElementById('input-upload');
    const btnUrl = document.getElementById('btn-mode-url');
    const btnUpload = document.getElementById('btn-mode-upload');
    
    if (mode === 'url') {
        inputUrl.style.display = 'block';
        inputUpload.style.display = 'none';
        btnUrl.classList.add('active');
        btnUpload.classList.remove('active');
    } else {
        inputUrl.style.display = 'none';
        inputUpload.style.display = 'block';
        btnUrl.classList.remove('active');
        btnUpload.classList.add('active');
    }
}
</script>
@endpush
@endsection

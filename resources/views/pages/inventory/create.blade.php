@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('breadcrumb')
<a href="{{ route('inventory.index') }}" style="color:var(--text-tertiary);text-decoration:none">Inventory</a>
<span style="margin:0 8px;color:var(--text-tertiary)">/</span>
<span>Tambah Produk</span>
@endsection

@section('content')
<div class="card" style="max-width:700px">
    <h3 style="margin-bottom:20px">📦 Tambah Produk Baru</h3>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:16px;color:#f87171;font-size:13px">
        @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('inventory.store') }}" enctype="multipart/form-data">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            {{-- SKU, Name, Category fields... --}}
            <div class="form-group">
                <label>SKU <span style="color:var(--danger-400)">*</span></label>
                <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="GUP-011" required>
            </div>
            <div class="form-group">
                <label>Nama Produk <span style="color:var(--danger-400)">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nama produk" required>
            </div>
            <div class="form-group">
                <label>Kategori <span style="color:var(--danger-400)">*</span></label>
                <select name="category_id" class="form-control" required>
                    <option value="">Pilih kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Lokasi</label>
                <input type="text" name="location" class="form-control" value="{{ old('location') }}" placeholder="Rak A1">
            </div>
            <div class="form-group" style="grid-column: span 2">
                <label>Tipe Produk <span style="color:var(--danger-400)">*</span></label>
                <select name="type" class="form-control" required>
                    <option value="final_product" {{ old('type') == 'final_product' ? 'selected' : '' }}>🌟 Final Product (Bisa dijual)</option>
                    <option value="raw_material" {{ old('type') == 'raw_material' ? 'selected' : '' }}>🧱 Raw Material (Bahan Baku)</option>
                </select>
            </div>

            <div id="bom-section" style="grid-column: span 2; display: none;">
                <div style="background:var(--bg-secondary);padding:16px;border-radius:var(--radius-md);border:1px dashed var(--border-color)">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                        <div>
                            <label style="font-weight:700;margin:0">📋 Bill of Materials (Resep Produksi)</label>
                            <div style="font-size:12px;color:var(--text-tertiary)">Bahan baku yang dibutuhkan untuk memproduksi 1 unit produk ini.</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline" onclick="addBomRow()"><i data-lucide="plus"></i> Tambah Bahan</button>
                    </div>
                    
                    <div id="bom-container" style="display:flex;flex-direction:column;gap:12px">
                        <!-- dynamic rows -->
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Stok Awal <span style="color:var(--danger-400)">*</span></label>
                <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" required>
            </div>
            <div class="form-group">
                <label>Safety Stock <span style="color:var(--danger-400)">*</span></label>
                <input type="number" name="safety_stock" class="form-control" value="{{ old('safety_stock', 0) }}" min="0" required>
            </div>
            @hasanyrole('Manager|Executive|SuperAdmin')
            <div class="form-group">
                <label>Harga Jual <span style="color:var(--danger-400)">*</span></label>
                <input type="number" name="price" class="form-control" value="{{ old('price', 0) }}" min="0" step="1000" required>
            </div>
            <div class="form-group">
                <label>COGS <span style="color:var(--danger-400)">*</span></label>
                <input type="number" name="cogs" class="form-control" value="{{ old('cogs', 0) }}" min="0" step="1000" required>
            </div>
            @else
            <input type="hidden" name="price" value="0">
            <input type="hidden" name="cogs" value="0">
            @endhasanyrole

            <div class="form-group" style="grid-column:span 2;background:var(--bg-secondary);padding:16px;border-radius:var(--radius-md);border:1px dashed var(--border-color)">
                <label style="margin-bottom:12px;display:block;font-weight:700">🖼️ Gambar Produk</label>
                <div style="display:flex;gap:12px;margin-bottom:16px">
                    <button type="button" class="btn btn-sm btn-outline active" id="btn-mode-url" onclick="setImageMode('url')">Link URL</button>
                    <button type="button" class="btn btn-sm btn-outline" id="btn-mode-upload" onclick="setImageMode('upload')">Upload File</button>
                </div>
                
                <div id="input-url">
                    <label style="font-size:12px;color:var(--text-tertiary)">Masukkan URL gambar (Unsplash, dsb)</label>
                    <input type="text" name="image" class="form-control" value="{{ old('image') }}" placeholder="https://example.com/image.jpg">
                </div>
                
                <div id="input-upload" style="display:none">
                    <label style="font-size:12px;color:var(--text-tertiary)">Pilih file gambar dari perangkat (Max 2MB)</label>
                    <input type="file" name="image_file" class="form-control" style="padding:8px">
                </div>
            </div>

            <div class="form-group" style="grid-column:span 2">
                <label>Batch</label>
                <input type="text" name="batch" class="form-control" value="{{ old('batch') }}" placeholder="B-2024-011">
            </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:20px">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Simpan</button>
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
@push('scripts')
<script>
const rawMaterials = @json($rawMaterials);
let bomIndex = 0;

function toggleBom() {
    const type = document.querySelector('select[name="type"]').value;
    const bomSection = document.getElementById('bom-section');
    const bomContainer = document.getElementById('bom-container');
    
    if (type === 'final_product') {
        bomSection.style.display = 'block';
        if (bomContainer.children.length === 0) {
            addBomRow();
        }
    } else {
        bomSection.style.display = 'none';
        bomContainer.innerHTML = '';
        bomIndex = 0;
    }
}

function addBomRow() {
    let options = '<option value="">Pilih raw material...</option>';
    rawMaterials.forEach(rm => {
        options += `<option value="${rm.id}">${rm.sku} — ${rm.name}</option>`;
    });

    const row = document.createElement('div');
    row.style.display = 'flex';
    row.style.gap = '12px';
    row.style.alignItems = 'flex-end';
    row.innerHTML = `
        <div class="form-group" style="flex:1;margin-bottom:0">
            <select name="boms[${bomIndex}][raw_material_id]" class="form-control" required>
                ${options}
            </select>
        </div>
        <div class="form-group" style="width:150px;margin-bottom:0">
            <input type="number" name="boms[${bomIndex}][quantity]" class="form-control" min="0.01" step="0.01" value="1" placeholder="Jumlah" required>
        </div>
        <button type="button" class="btn btn-sm" style="padding:0 12px;height:40px;background:rgba(239,68,68,0.1);color:var(--danger-400);border:1px solid rgba(239,68,68,0.2)" onclick="this.parentElement.remove()"><i data-lucide="trash-2" style="width:16px"></i></button>
    `;
    document.getElementById('bom-container').appendChild(row);
    if(typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    bomIndex++;
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('select[name="type"]').addEventListener('change', toggleBom);
    toggleBom();
});

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

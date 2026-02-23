@extends('layouts.app')

@section('title', 'Kategori Produk')
@section('breadcrumb', 'Kategori Produk')

@section('content')
@if(session('success'))
<div class="alert alert-success"><i data-lucide="check-circle" style="width:18px;height:18px"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error"><i data-lucide="x-circle" style="width:18px;height:18px"></i> {{ session('error') }}</div>
@endif

{{-- Stats --}}
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--primary-500)"><i data-lucide="tags"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $categories->count() }}</span><span class="stat-label">Total Kategori</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--success-500)"><i data-lucide="package"></i></div>
        <div class="stat-info"><span class="stat-value">{{ number_format($categories->sum('products_sum_stock')) }}</span><span class="stat-label">Total Unit (Stok)</span></div>
    </div>
</div>

<div class="card">
    <div class="filter-bar" style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;align-items:center">
        <input type="text" id="search-cat" class="form-control" placeholder="Cari kategori..." style="max-width:250px" onkeyup="filterCategories()">
        <div style="margin-left:auto">
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('modal-add-cat').classList.remove('hidden')"><i data-lucide="plus"></i> Tambah Kategori</button>
        </div>
    </div>

    <div class="table-container">
        <table id="categories-table">
            <thead>
                <tr><th>Nama Kategori</th><th>Jumlah SKU</th><th>Total Unit (Stok)</th><th>Dibuat</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr data-name="{{ strtolower($cat->name) }}">
                    <td style="font-weight:600;color:var(--text-primary)">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:36px;height:36px;border-radius:var(--radius-md);background:rgba(99,102,241,0.1);display:flex;align-items:center;justify-content:center;color:var(--primary-400);flex-shrink:0">
                                <i data-lucide="tag" style="width:18px;height:18px"></i>
                            </div>
                            {{ $cat->name }}
                        </div>
                    </td>
                    <td>
                        <span class="badge {{ $cat->products_count > 0 ? 'badge-info' : '' }}">{{ $cat->products_count }} SKU</span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span style="font-weight:600">{{ number_format($cat->products_sum_stock ?? 0) }}</span>
                            <span style="color:var(--text-tertiary);font-size:12px">unit</span>
                        </div>
                    </td>
                    <td>{{ $cat->created_at->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <button class="btn btn-ghost btn-xs" title="Edit" onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}')"><i data-lucide="edit-2" style="width:14px;height:14px"></i></button>
                            @if($cat->products_count === 0)
                            <form method="POST" action="{{ route('categories.destroy', $cat) }}" onsubmit="return confirm('Hapus kategori {{ $cat->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger-400)" title="Hapus"><i data-lucide="trash-2" style="width:14px;height:14px"></i></button>
                            </form>
                            @else
                            <button class="btn btn-ghost btn-xs" style="color:var(--text-tertiary);cursor:not-allowed" title="Tidak bisa dihapus (masih ada produk)" disabled><i data-lucide="trash-2" style="width:14px;height:14px"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-tertiary)">Belum ada kategori</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal: Tambah Kategori --}}
<div id="modal-add-cat" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal">
        <div class="modal-header"><h3>🏷️ Tambah Kategori</h3><button class="btn-icon" onclick="document.getElementById('modal-add-cat').classList.add('hidden')"><i data-lucide="x"></i></button></div>
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Kategori <span style="color:var(--danger-400)">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Masukkan nama kategori" required autofocus>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

{{-- Modal: Edit Kategori --}}
<div id="modal-edit-cat" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal">
        <div class="modal-header"><h3>✏️ Edit Kategori</h3><button class="btn-icon" onclick="document.getElementById('modal-edit-cat').classList.add('hidden')"><i data-lucide="x"></i></button></div>
        <form id="edit-cat-form" method="POST" action="">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Kategori <span style="color:var(--danger-400)">*</span></label>
                    <input type="text" name="name" id="edit-cat-name" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div>
</div>

@if($errors->any())
<div style="position:fixed;bottom:20px;right:20px;background:rgba(239,68,68,0.95);color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;z-index:999;box-shadow:0 4px 12px rgba(0,0,0,0.3)">
    @foreach($errors->all() as $error){{ $error }}@endforeach
</div>
@endif

@endsection

@push('scripts')
<script>
function filterCategories() {
    const search = document.getElementById('search-cat').value.toLowerCase();
    document.querySelectorAll('#categories-table tbody tr').forEach(row => {
        if (!row.dataset.name) return;
        row.style.display = (!search || row.dataset.name.includes(search)) ? '' : 'none';
    });
}

function openEditModal(id, name) {
    document.getElementById('edit-cat-name').value = name;
    document.getElementById('edit-cat-form').action = '/categories/' + id;
    document.getElementById('modal-edit-cat').classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
}
</script>
@endpush

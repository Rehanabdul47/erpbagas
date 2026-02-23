@extends('layouts.app')

@section('title', 'Inventory')
@section('breadcrumb', 'Inventory')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
<div class="alert alert-success" style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:var(--success-400);display:flex;align-items:center;gap:8px;">
    <i data-lucide="check-circle" style="width:18px;height:18px"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-error" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:var(--danger-400);display:flex;align-items:center;gap:8px;">
    <i data-lucide="x-circle" style="width:18px;height:18px"></i> {{ session('error') }}
</div>
@endif

{{-- Stats --}}
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--primary-500)"><i data-lucide="package"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $stats['totalSku'] }}</span><span class="stat-label">Total SKU</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--success-500)"><i data-lucide="layers"></i></div>
        <div class="stat-info"><span class="stat-value">{{ number_format($stats['totalStock']) }}</span><span class="stat-label">Total Stok</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--warning-500)"><i data-lucide="alert-triangle"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $stats['lowStock'] }}</span><span class="stat-label">Stok Rendah</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--danger-500)"><i data-lucide="x-circle"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $stats['outOfStock'] }}</span><span class="stat-label">Stok Habis</span></div>
    </div>
</div>

{{-- Tabs --}}
<div class="card" style="margin-bottom:0">
    <div class="tabs" style="display:flex;gap:0;border-bottom:1px solid var(--border-color);margin-bottom:20px">
        <button class="tab-btn active" onclick="switchTab('stock-list', this)">📦 Daftar Stok</button>
        <button class="tab-btn" onclick="switchTab('movement', this)">🔄 Riwayat Pergerakan</button>
        <button class="tab-btn" onclick="switchTab('opname', this)">📋 Stock Opname</button>
    </div>

    {{-- Tab: Daftar Stok --}}
    <div id="tab-stock-list" class="tab-content">
        <div class="filter-bar" style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;align-items:center">
            <input type="text" id="search-product" class="form-control" placeholder="Cari produk..." style="max-width:250px" onkeyup="filterProducts()">
            <select id="filter-category" class="form-control" style="max-width:180px" onchange="filterProducts()">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select id="filter-status" class="form-control" style="max-width:150px" onchange="filterProducts()">
                <option value="">Semua Status</option>
                <option value="ok">✅ Aman</option>
                <option value="low">⚠️ Rendah</option>
                <option value="out">🔴 Habis</option>
            </select>
            <div style="margin-left:auto;display:flex;gap:8px">
                <a href="{{ route('inventory.create') }}" class="btn btn-primary btn-sm"><i data-lucide="plus"></i> Tambah Produk</a>
                <button class="btn btn-success btn-sm" onclick="document.getElementById('modal-stock-in').classList.remove('hidden')"><i data-lucide="arrow-down-circle"></i> Stok Masuk</button>
                <button class="btn btn-warning btn-sm" onclick="document.getElementById('modal-stock-out').classList.remove('hidden')"><i data-lucide="arrow-up-circle"></i> Stok Keluar</button>
            </div>
        </div>

        <div class="table-container">
            <table id="inventory-table">
                <thead>
                    <tr>
                        <th style="width:50px"></th><th>SKU</th><th>Produk</th><th>Kategori</th><th>Stok</th><th>Safety</th>
                        <th>Harga</th><th>COGS</th><th>Lokasi</th><th>Status</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $p)
                    <tr data-sku="{{ strtolower($p->sku) }}" data-name="{{ strtolower($p->name) }}" data-category="{{ $p->category->name }}" data-status="{{ $p->status }}">
                        <td>
                            @if($p->image)
                                <img src="{{ Str::startsWith($p->image, 'http') ? $p->image : asset($p->image) }}" alt="{{ $p->name }}" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid var(--border-color)">
                            @else
                                <div style="width:32px;height:32px;background:var(--bg-tertiary);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--text-tertiary)">
                                    <i data-lucide="package" style="width:16px;height:16px"></i>
                                </div>
                            @endif
                        </td>
                        <td style="font-weight:600;color:var(--text-primary)">{{ $p->sku }}</td>
                        <td>{{ $p->name }}</td>
                        <td>{{ $p->category->name }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <div class="stock-bar" style="width:60px;height:6px;background:var(--bg-tertiary);border-radius:3px;overflow:hidden">
                                    @php $pct = $p->safety_stock > 0 ? min(100, ($p->stock / ($p->safety_stock * 3)) * 100) : 100; @endphp
                                    <div style="width:{{ $pct }}%;height:100%;background:{{ $p->status === 'ok' ? 'var(--success-400)' : ($p->status === 'low' ? 'var(--warning-400)' : 'var(--danger-400)') }};border-radius:3px"></div>
                                </div>
                                <span style="font-weight:600">{{ $p->stock }}</span>
                            </div>
                        </td>
                        <td>{{ $p->safety_stock }}</td>
                        <td>Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($p->cogs, 0, ',', '.') }}</td>
                        <td>{{ $p->location }}</td>
                        <td><span class="badge {{ $p->status_badge }}">{{ $p->status_label }}</span></td>
                        <td>
                            <div style="display:flex;gap:8px">
                                <a href="{{ route('inventory.barcode', $p) }}" class="btn btn-ghost btn-xs" title="Barcode"><i data-lucide="barcode" style="width:14px"></i></a>
                                <a href="{{ route('inventory.edit', $p) }}" class="btn btn-ghost btn-xs" title="Edit"><i data-lucide="edit" style="width:14px"></i></a>
                                <form action="{{ route('inventory.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger-400)" title="Hapus"><i data-lucide="trash-2" style="width:14px"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" style="text-align:center;padding:40px;color:var(--text-tertiary)">Belum ada data produk</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tab: Movement History --}}
    <div id="tab-movement" class="tab-content" style="display:none">
        <div class="table-container">
            <table>
                <thead><tr><th>Tanggal</th><th>SKU</th><th>Produk</th><th>Tipe</th><th>Qty</th><th>Referensi</th><th>Metode</th></tr></thead>
                <tbody>
                    @forelse($movements as $m)
                    <tr>
                        <td>{{ $m->created_at->format('Y-m-d H:i') }}</td>
                        <td style="font-weight:600">{{ $m->product->sku }}</td>
                        <td>{{ $m->product->name }}</td>
                        <td>
                            <span class="badge {{ $m->type === 'in' ? 'badge-success' : 'badge-danger' }}">
                                {{ $m->type === 'in' ? '📥 Masuk' : '📤 Keluar' }}
                            </span>
                        </td>
                        <td style="font-weight:600">{{ $m->quantity }}</td>
                        <td>{{ $m->reference }}</td>
                        <td><span class="badge badge-info">{{ $m->method }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-tertiary)">Belum ada riwayat pergerakan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tab: Stock Opname --}}
    <div id="tab-opname" class="tab-content" style="display:none">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
            <h4 style="margin:0">📋 Stock Opname — Reinkonsiliasi Stok</h4>
            <div style="font-size:12px;color:var(--text-tertiary)">* Mengubah stok langsung sesuai jumlah fisik terbaru.</div>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Produk</th><th>Stok Sistem</th><th>Stok Fisik</th><th>Selisih</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach($products as $p)
                    <tr>
                        <td>
                            <div style="font-weight:600">{{ $p->name }}</div>
                            <div style="font-size:12px;color:var(--text-tertiary)">SKU: {{ $p->sku }}</div>
                        </td>
                        <td id="sys-stock-{{ $p->id }}">{{ $p->stock }}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm opname-input" 
                                   style="width:100px" 
                                   value="{{ $p->stock }}" 
                                   oninput="updateDiff({{ $p->id }}, this.value, {{ $p->stock }})">
                        </td>
                        <td id="diff-{{ $p->id }}" style="font-weight:600">0</td>
                        <td>
                            <button class="btn btn-primary btn-xs" onclick='openOpnameModal({{ json_encode($p) }})'>Adjust</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-container" style="margin-top:24px">
            <h5 style="margin-bottom:12px;display:flex;align-items:center;gap:8px">📖 Riwayat Opname Terakhir <span class="badge badge-neutral">{{ $opnameHistory->count() }}</span></h5>
            <table>
                <thead>
                    <tr><th>Waktu</th><th>Produk</th><th>Sistem</th><th>Fisik</th><th>Selisih</th><th>Catatan</th><th>Oleh</th></tr>
                </thead>
                <tbody>
                    @forelse($opnameHistory as $oh)
                    <tr>
                        <td>{{ $oh->created_at->format('d M, H:i') }}</td>
                        <td>{{ $oh->product->name }}</td>
                        <td>{{ $oh->system_stock }}</td>
                        <td style="font-weight:600">{{ $oh->physical_stock }}</td>
                        <td style="color:{{ $oh->difference >= 0 ? 'var(--success-400)' : 'var(--danger-400)' }}">
                            {{ ($oh->difference > 0 ? '+' : '') . $oh->difference }}
                        </td>
                        <td><small>{{ $oh->notes ?? '-' }}</small></td>
                        <td><span class="badge badge-neutral" style="font-size:10px">{{ $oh->user->name }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" style="text-align:center;padding:20px;color:var(--text-tertiary)">Belum ada riwayat opname</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: Stock Opname Adjustment --}}
<div id="modal-opname" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal" style="max-width:400px">
        <div class="modal-header"><h3>📋 Konfirmasi Opname</h3><button class="btn-icon" onclick="document.getElementById('modal-opname').classList.add('hidden')"><i data-lucide="x"></i></button></div>
        <form method="POST" action="{{ route('inventory.stock-opname') }}">
            @csrf
            <input type="hidden" name="product_id" id="opname-product-id">
            <div class="modal-body">
                <div id="opname-info" style="margin-bottom:16px;padding:12px;background:var(--bg-surface);border-radius:var(--radius-md);font-size:14px">
                    Produk: <span id="opname-product-name" style="font-weight:600"></span><br>
                    Stok Sistem: <span id="opname-sys-stock"></span><br>
                    <div style="color:var(--primary-400);font-weight:700">Stok Fisik Baru: <span id="opname-phys-stock"></span></div>
                </div>
                <input type="hidden" name="physical_stock" id="opname-phys-input">
                <div class="form-group">
                    <label>Catatan (Alasan Selisih)</label>
                    <textarea name="notes" class="form-control" placeholder="Contoh: Barang rusak, salah catat, dll" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan Adjustment</button></div>
        </form>
    </div>
</div>

{{-- Modal: Stock Masuk --}}
<div id="modal-stock-in" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal">
        <div class="modal-header"><h3>📥 Stok Masuk</h3><button class="btn-icon" onclick="document.getElementById('modal-stock-in').classList.add('hidden')"><i data-lucide="x"></i></button></div>
        <form method="POST" action="{{ route('inventory.stock-in') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group"><label>Produk</label>
                    <select name="product_id" class="form-control" required>
                        <option value="">Pilih produk...</option>
                        @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }} (stok: {{ $p->stock }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Jumlah</label><input type="number" name="quantity" class="form-control" min="1" required></div>
                <div class="form-group"><label>Referensi</label><input type="text" name="reference" class="form-control" placeholder="PO ID / catatan"></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-success">Simpan</button></div>
        </form>
    </div>
</div>

{{-- Modal: Stock Keluar --}}
<div id="modal-stock-out" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal">
        <div class="modal-header"><h3>📤 Stok Keluar</h3><button class="btn-icon" onclick="document.getElementById('modal-stock-out').classList.add('hidden')"><i data-lucide="x"></i></button></div>
        <form method="POST" action="{{ route('inventory.stock-out') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group"><label>Produk</label>
                    <select name="product_id" class="form-control" required>
                        <option value="">Pilih produk...</option>
                        @foreach($products->where('stock', '>', 0) as $p)
                        <option value="{{ $p->id }}">{{ $p->sku }} — {{ $p->name }} (stok: {{ $p->stock }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>Jumlah</label><input type="number" name="quantity" class="form-control" min="1" required></div>
                <div class="form-group"><label>Referensi</label><input type="text" name="reference" class="form-control" placeholder="Order ID / catatan"></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-warning">Keluarkan</button></div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-switch tab based on URL parameter
window.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const activeTab = params.get('tab');
    if (activeTab) {
        const btn = document.querySelector(`button[onclick*="'${activeTab}'"]`);
        if (btn) btn.click();
    }
});

function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tab).style.display = 'block';
    btn.classList.add('active');
    if (window.lucide) lucide.createIcons();
}

function updateDiff(id, phys, sys) {
    const diff = phys - sys;
    const el = document.getElementById('diff-' + id);
    el.innerText = (diff > 0 ? '+' : '') + diff;
    el.style.color = diff === 0 ? 'var(--text-secondary)' : (diff > 0 ? 'var(--success-400)' : 'var(--danger-400)');
}

function openOpnameModal(product) {
    const input = document.querySelector(`tr:has(#sys-stock-${product.id}) .opname-input`);
    const physiqueValue = input.value;
    
    document.getElementById('opname-product-id').value = product.id;
    document.getElementById('opname-product-name').innerText = product.name;
    document.getElementById('opname-sys-stock').innerText = product.stock;
    document.getElementById('opname-phys-stock').innerText = physiqueValue;
    document.getElementById('opname-phys-input').value = physiqueValue;
    
    document.getElementById('modal-opname').classList.remove('hidden');
}

function filterProducts() {
    const search = document.getElementById('search-product').value.toLowerCase();
    const cat = document.getElementById('filter-category').value;
    const status = document.getElementById('filter-status').value;
    document.querySelectorAll('#inventory-table tbody tr').forEach(row => {
        if (!row.dataset.sku) return;
        const matchSearch = !search || row.dataset.sku.includes(search) || row.dataset.name.includes(search);
        const matchCat = !cat || row.dataset.category === cat;
        const matchStatus = !status || row.dataset.status === status;
        row.style.display = (matchSearch && matchCat && matchStatus) ? '' : 'none';
    });
}
</script>
@endpush

@extends('layouts.app')

@section('title', 'Buku Kas (Finance)')
@section('breadcrumb', 'Buku Kas')

@section('content')
@if(session('success'))
<div class="alert alert-success"><i data-lucide="check-circle" style="width:18px;height:18px"></i> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-error"><i data-lucide="x-circle" style="width:18px;height:18px"></i> {{ session('error') }}</div>
@endif

{{-- Stats --}}
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card" style="border-left: 4px solid var(--primary-500)">
        <div class="stat-icon" style="background:var(--primary-500)"><i data-lucide="wallet"></i></div>
        <div class="stat-info"><span class="stat-value">Rp {{ number_format($stats['totalBalance'], 0, ',', '.') }}</span><span class="stat-label">Saldo Kas Saat Ini</span></div>
    </div>
    <div class="card stat-card" style="border-left: 4px solid var(--success-500)">
        <div class="stat-icon" style="background:var(--success-500)"><i data-lucide="arrow-down-left"></i></div>
        <div class="stat-info"><span class="stat-value text-success">Rp {{ number_format($stats['monthlyIn'], 0, ',', '.') }}</span><span class="stat-label">Masuk ({{ $stats['currentMonth'] }})</span></div>
    </div>
    <div class="card stat-card" style="border-left: 4px solid var(--danger-500)">
        <div class="stat-icon" style="background:var(--danger-500)"><i data-lucide="arrow-up-right"></i></div>
        <div class="stat-info"><span class="stat-value text-danger">Rp {{ number_format($stats['monthlyOut'], 0, ',', '.') }}</span><span class="stat-label">Keluar ({{ $stats['currentMonth'] }})</span></div>
    </div>
</div>

<div class="card">
    <div class="filter-bar" style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;align-items:center">
        <form id="filter-form" style="display:flex;gap:12px;flex-wrap:wrap;flex:1">
            <input type="month" name="month" class="form-control" style="max-width:200px" value="{{ $month }}" onchange="document.getElementById('filter-form').submit()">
            
            <select name="type" class="form-control" style="max-width:180px" onchange="document.getElementById('filter-form').submit()">
                <option value="">Semua Transaksi</option>
                <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Kas Masuk</option>
                <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Kas Keluar</option>
            </select>
            
            <div style="flex:1;min-width:200px;display:flex;gap:8px">
                <input type="text" name="search" class="form-control" placeholder="Cari keterangan / kategori..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-ghost"><i data-lucide="search" style="width:18px"></i></button>
            </div>
        </form>

        <div style="display:flex;gap:8px;margin-left:auto">
            <button class="btn btn-primary btn-sm" onclick="openModal('modal-in')" style="background:var(--success-500);border-color:var(--success-600)"><i data-lucide="plus"></i> Kas Masuk</button>
            <button class="btn btn-primary btn-sm" onclick="openModal('modal-out')" style="background:var(--danger-500);border-color:var(--danger-600)"><i data-lucide="minus"></i> Kas Keluar</button>
        </div>
    </div>

    <div class="table-container">
        <table id="finance-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th>Nominal</th>
                    <th>Pencatat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $trx)
                <tr>
                    <td style="white-space:nowrap">{{ $trx->date->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px">
                            @if($trx->type === 'in')
                            <div style="width:24px;height:24px;border-radius:4px;background:rgba(34,197,94,0.1);color:var(--success-500);display:flex;align-items:center;justify-content:center"><i data-lucide="arrow-down-left" style="width:14px;height:14px"></i></div>
                            @else
                            <div style="width:24px;height:24px;border-radius:4px;background:rgba(239,68,68,0.1);color:var(--danger-500);display:flex;align-items:center;justify-content:center"><i data-lucide="arrow-up-right" style="width:14px;height:14px"></i></div>
                            @endif
                            <span style="font-weight:500">{{ $trx->category }}</span>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:14px;margin-bottom:2px">{{ $trx->description }}</div>
                        @if($trx->reference)
                        <div style="font-size:12px;color:var(--text-tertiary)">Ref: {{ $trx->reference }}</div>
                        @endif
                    </td>
                    <td style="font-weight:600;white-space:nowrap;color:{{ $trx->type === 'in' ? 'var(--success-500)' : 'var(--danger-500)' }}">
                        {{ $trx->type === 'in' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </td>
                    <td>{{ $trx->user->name ?? 'System' }}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <button class="btn btn-ghost btn-xs" title="Edit" onclick='editTrx(@json($trx))'><i data-lucide="edit-2" style="width:14px;height:14px"></i></button>
                            <form method="POST" action="{{ route('finance.destroy', $trx) }}" onsubmit="return confirm('Hapus pencatatan kas ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger-400)" title="Hapus"><i data-lucide="trash-2" style="width:14px;height:14px"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-tertiary)">Tidak ada transaksi kas di periode ini</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Kas Masuk --}}
<div id="modal-in" class="modal-overlay hidden" onclick="if(event.target===this)closeModal('modal-in')">
    <div class="modal">
        <div class="modal-header"><h3><i data-lucide="arrow-down-left" style="color:var(--success-500);display:inline-block;vertical-align:middle;margin-right:8px"></i> Catat Kas Masuk</h3><button class="btn-icon" onclick="closeModal('modal-in')"><i data-lucide="x"></i></button></div>
        <form method="POST" action="{{ route('finance.store') }}">
            @csrf
            <input type="hidden" name="type" value="in">
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                    <div class="form-group" style="margin:0"><label>Tanggal</label><input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                    <div class="form-group" style="margin:0">
                        <label>Kategori</label>
                        <input type="text" name="category" list="cat-in" class="form-control" placeholder="Pilih/Ketik kategori" required>
                        <datalist id="cat-in">
                            <option value="Penjualan Tunai"><option value="Penerimaan Piutang"><option value="Suntikan Modal"><option value="Lain-lain">
                        </datalist>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control" min="1" step="0.01" placeholder="0" required>
                </div>
                <div class="form-group">
                    <label>Keterangan Lengkap</label>
                    <textarea name="description" class="form-control" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <label>Referensi (Opsional)</label>
                    <input type="text" name="reference" class="form-control" placeholder="No. Invoice / Kwitansi">
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary" style="background:var(--success-500)">Simpan Pemasukan</button></div>
        </form>
    </div>
</div>

{{-- Modal Kas Keluar --}}
<div id="modal-out" class="modal-overlay hidden" onclick="if(event.target===this)closeModal('modal-out')">
    <div class="modal">
        <div class="modal-header"><h3><i data-lucide="arrow-up-right" style="color:var(--danger-500);display:inline-block;vertical-align:middle;margin-right:8px"></i> Catat Kas Keluar</h3><button class="btn-icon" onclick="closeModal('modal-out')"><i data-lucide="x"></i></button></div>
        <form method="POST" action="{{ route('finance.store') }}">
            @csrf
            <input type="hidden" name="type" value="out">
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                    <div class="form-group" style="margin:0"><label>Tanggal</label><input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required></div>
                    <div class="form-group" style="margin:0">
                        <label>Kategori</label>
                        <input type="text" name="category" list="cat-out" class="form-control" placeholder="Pilih/Ketik kategori" required>
                        <datalist id="cat-out">
                            <option value="Operasional"><option value="Gaji Karyawan"><option value="Kasbon Karyawan"><option value="Pembelian Aset"><option value="Biaya Server"><option value="Lain-lain">
                        </datalist>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control" min="1" step="0.01" placeholder="0" required>
                </div>
                <div class="form-group">
                    <label>Keterangan Lengkap</label>
                    <textarea name="description" class="form-control" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <label>Referensi (Opsional)</label>
                    <input type="text" name="reference" class="form-control" placeholder="No. PO / Bukti Transfer">
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary" style="background:var(--danger-500)">Simpan Pengeluaran</button></div>
        </form>
    </div>
</div>

{{-- Modal Edit Transaksi --}}
<div id="modal-edit" class="modal-overlay hidden" onclick="if(event.target===this)closeModal('modal-edit')">
    <div class="modal">
        <div class="modal-header"><h3 id="edit-title">✏️ Edit Transaksi</h3><button class="btn-icon" onclick="closeModal('modal-edit')"><i data-lucide="x"></i></button></div>
        <form id="form-edit" method="POST" action="">
            @csrf @method('PUT')
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                    <div class="form-group" style="margin:0"><label>Tanggal</label><input type="date" name="date" id="edit-date" class="form-control" required></div>
                    <div class="form-group" style="margin:0">
                        <label>Kategori</label>
                        <input type="text" name="category" id="edit-category" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Nominal (Rp)</label>
                    <input type="number" name="amount" id="edit-amount" class="form-control" min="1" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Keterangan Lengkap</label>
                    <textarea name="description" id="edit-desc" class="form-control" rows="2" required></textarea>
                </div>
                <div class="form-group">
                    <label>Referensi (Opsional)</label>
                    <input type="text" name="reference" id="edit-ref" class="form-control">
                </div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Update Data</button></div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function editTrx(trx) {
    const isOut = trx.type === 'out';
    document.getElementById('edit-title').innerHTML = isOut ? '✏️ Edit Kas Keluar' : '✏️ Edit Kas Masuk';
    document.getElementById('edit-date').value = trx.date.split('T')[0];
    document.getElementById('edit-category').value = trx.category;
    document.getElementById('edit-amount').value = trx.amount;
    document.getElementById('edit-desc').value = trx.description;
    document.getElementById('edit-ref').value = trx.reference || '';
    
    document.getElementById('form-edit').action = '/finance/' + trx.id;
    openModal('modal-edit');
}
</script>
<style>
.text-success { color: var(--success-500) !important; }
.text-danger { color: var(--danger-500) !important; }
</style>
@endpush

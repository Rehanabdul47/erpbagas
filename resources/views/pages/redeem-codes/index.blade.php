@extends('layouts.app')

@section('title', 'Redeem Codes')
@section('breadcrumb', 'Redeem Codes')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
<div class="alert alert-success" style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:var(--success-400);display:flex;align-items:center;gap:8px;">
    <i data-lucide="check-circle" style="width:18px;height:18px"></i> {{ session('success') }}
</div>
@endif

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
        <h3 style="margin:0">✨ Daftar Kode Redeem</h3>
        <button class="btn btn-primary btn-sm" onclick="document.getElementById('modal-create').classList.remove('hidden')">
            <i data-lucide="plus"></i> Tambah Kode
        </button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Tipe & Nilai</th>
                    <th>Total Kuota</th>
                    <th>Limit Per User</th>
                    <th>Masa Berlaku</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($codes as $code)
                <tr>
                    <td>
                        <div style="font-weight:700;color:var(--text-primary)">{{ $code->code }}</div>
                        <div style="font-size:12px;color:var(--text-tertiary)">{{ $code->description ?? '-' }}</div>
                    </td>
                    <td>
                        <span class="badge {{ $code->type === 'percentage' ? 'badge-info' : 'badge-success' }}">
                            {{ $code->type === 'percentage' ? number_format($code->value, 0) . '%' : 'Rp ' . number_format($code->value, 0, ',', '.') }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:600">{{ $code->used_count }} / {{ $code->max_uses > 0 ? $code->max_uses : '∞' }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600">{{ $code->max_uses_per_customer > 0 ? $code->max_uses_per_customer . 'x' : '∞' }}</div>
                    </td>
                    <td>
                        <div style="font-size:12px;color:var(--text-secondary)">
                            Dari: {{ $code->valid_from ? $code->valid_from->format('d M Y') : 'Selamanya' }}<br>
                            Hingga: {{ $code->valid_until ? $code->valid_until->format('d M Y') : 'Selamanya' }}
                        </div>
                    </td>
                    <td>
                        @if($code->isValid())
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-danger">Tidak Aktif/Expired</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:8px">
                            <a href="{{ route('redeem-codes.barcode', $code) }}" class="btn btn-ghost btn-xs" title="Barcode">
                                <i data-lucide="barcode" style="width:14px"></i>
                            </a>
                            <button class="btn btn-ghost btn-xs" onclick="openEditModal({{ json_encode($code) }})" title="Edit">
                                <i data-lucide="edit" style="width:14px"></i>
                            </button>
                            <form action="{{ route('redeem-codes.destroy', $code) }}" method="POST" onsubmit="return confirm('Hapus kode ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger-400)" title="Hapus">
                                    <i data-lucide="trash-2" style="width:14px"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-tertiary)">Belum ada data kode redeem</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:20px">
        {{ $codes->links() }}
    </div>
</div>

{{-- Modal: Create --}}
<div id="modal-create" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3>✨ Tambah Kode Redeem Baru</h3>
            <button class="btn-icon" onclick="document.getElementById('modal-create').classList.add('hidden')"><i data-lucide="x"></i></button>
        </div>
        <form action="{{ route('redeem-codes.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label>Kode String <span style="color:var(--danger-400)">*</span></label>
                    <input type="text" name="code" class="form-control" required placeholder="Contoh: LOYAL10" style="text-transform:uppercase">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label>Tipe Diskon <span style="color:var(--danger-400)">*</span></label>
                        <select name="type" class="form-control" required>
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nilai Diskon <span style="color:var(--danger-400)">*</span></label>
                        <input type="number" name="value" class="form-control" required min="0" step="0.01">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label>Total Kuota <span style="font-size:11px;color:var(--text-tertiary)">(0 = ∞)</span></label>
                        <input type="number" name="max_uses" class="form-control" value="0" min="0" required title="Total penggunaan di seluruh sistem">
                    </div>
                    <div class="form-group">
                        <label>Limit Per User <span style="font-size:11px;color:var(--text-tertiary)">(0 = ∞)</span></label>
                        <input type="number" name="max_uses_per_customer" class="form-control" value="1" min="0" required title="Batas penggunaan per satu pelanggan">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label>Berlaku Dari</label>
                        <input type="date" name="valid_from" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Berlaku Hingga</label>
                        <input type="date" name="valid_until" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Keterangan singkat kode promo"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-create').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kode</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Edit --}}
<div id="modal-edit" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3>✏️ Edit Kode Redeem</h3>
            <button class="btn-icon" onclick="document.getElementById('modal-edit').classList.add('hidden')"><i data-lucide="x"></i></button>
        </div>
        <form id="form-edit" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label>Kode String <span style="color:var(--danger-400)">*</span></label>
                    <input type="text" name="code" id="edit-code" class="form-control" required style="text-transform:uppercase">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label>Tipe Diskon <span style="color:var(--danger-400)">*</span></label>
                        <select name="type" id="edit-type" class="form-control" required>
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nilai Diskon <span style="color:var(--danger-400)">*</span></label>
                        <input type="number" name="value" id="edit-value" class="form-control" required min="0" step="0.01">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label>Total Kuota</label>
                        <input type="number" name="max_uses" id="edit-max-uses" class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Limit Per User</label>
                        <input type="number" name="max_uses_per_customer" id="edit-max-uses-per-customer" class="form-control" min="0" required>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group">
                        <label>Berlaku Dari</label>
                        <input type="date" name="valid_from" id="edit-valid-from" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Berlaku Hingga</label>
                        <input type="date" name="valid_until" id="edit-valid-until" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" id="edit-description" class="form-control" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                        <input type="checkbox" name="is_active" id="edit-active" value="1">
                        <span>Aktifkan Kode ini</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('modal-edit').classList.add('hidden')">Batal</button>
                <button type="submit" class="btn btn-primary">Update Kode</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(code) {
    document.getElementById('form-edit').action = `/redeem-codes/${code.id}`;
    document.getElementById('edit-code').value = code.code;
    document.getElementById('edit-type').value = code.type;
    document.getElementById('edit-value').value = code.value;
    document.getElementById('edit-max-uses').value = code.max_uses;
    document.getElementById('edit-max-uses-per-customer').value = code.max_uses_per_customer;
    document.getElementById('edit-valid-from').value = code.valid_from ? code.valid_from.substring(0, 10) : '';
    document.getElementById('edit-valid-until').value = code.valid_until ? code.valid_until.substring(0, 10) : '';
    document.getElementById('edit-description').value = code.description;
    document.getElementById('edit-active').checked = code.is_active;
    
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endpush


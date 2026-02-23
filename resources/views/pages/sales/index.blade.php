@extends('layouts.app')

@section('title', 'Sales & CRM')
@section('breadcrumb', 'Sales & CRM')

@section('content')
{{-- Flash Messages --}}
@if(session('success'))
<div class="alert alert-success" style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:var(--success-400);display:flex;align-items:center;gap:8px;">
    <i data-lucide="check-circle" style="width:18px;height:18px"></i> {{ session('success') }}
</div>
@endif

{{-- Stats --}}
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:24px">
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--primary-500)"><i data-lucide="trending-up"></i></div>
        <div class="stat-info">
            <span class="stat-value">Rp {{ number_format($stats['totalRevenue'], 0, ',', '.') }}</span>
            <span class="stat-label">Pendapatan (Bulan Ini)</span>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--success-500)"><i data-lucide="users"></i></div>
        <div class="stat-info">
            <span class="stat-value">{{ $stats['totalCustomers'] }}</span>
            <span class="stat-label">Total Customer</span>
        </div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--warning-500)"><i data-lucide="shopping-bag"></i></div>
        <div class="stat-info">
            <span class="stat-value">{{ number_format($stats['totalSales']) }}</span>
            <span class="stat-label">Total Transaksi</span>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:0">
    <div class="tabs" style="display:flex;gap:0;border-bottom:1px solid var(--border-color);margin-bottom:20px">
        <button class="tab-btn active" onclick="switchTab('customers', this)">👥 Customer (CRM)</button>
        <button class="tab-btn" onclick="switchTab('history', this)">📜 Riwayat Penjualan</button>
    </div>

    {{-- Tab: Customer List --}}
    <div id="tab-customers" class="tab-content">
        <div class="filter-bar" style="display:flex;gap:12px;margin-bottom:16px;align-items:center">
            <input type="text" id="search-customer" class="form-control" placeholder="Cari nama/phone..." style="max-width:300px" onkeyup="filterCustomers()">
            <button class="btn btn-primary btn-sm" style="margin-left:auto" onclick="openAddCustomerModal()">
                <i data-lucide="plus"></i> Tambah Customer
            </button>
        </div>

        <div class="table-container">
            <table id="customer-table">
                <thead>
                    <tr><th>Nama Customer</th><th>Phone</th><th>Status</th><th>Total Order</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($customers as $c)
                    <tr data-name="{{ strtolower($c->name) }}" data-phone="{{ $c->phone }}">
                        <td style="font-weight:600;color:var(--text-primary)">{{ $c->name }}</td>
                        <td>{{ $c->phone ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $c->status === 'active' ? 'badge-success' : ($c->status === 'leads' ? 'badge-info' : 'badge-neutral') }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                        <td style="font-weight:700">{{ $c->sales_count }}</td>
                        <td>
                            <div style="display:flex;gap:4px">
                                <button class="btn btn-ghost btn-xs" onclick='openEditCustomerModal({{ json_encode($c) }})'><i data-lucide="edit-2" style="width:14px"></i></button>
                                <form method="POST" action="{{ route('sales.customers.destroy', $c) }}" onsubmit="return confirm('Hapus customer ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger-400)"><i data-lucide="trash-2" style="width:14px"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-tertiary)">Belum ada data customer</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Tab: Sales History --}}
    <div id="tab-history" class="tab-content" style="display:none">
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Invoice</th><th>Customer</th><th>Tanggal</th><th>Total Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse($sales as $s)
                    <tr>
                        <td style="font-weight:600">#{{ $s->invoice_no }}</td>
                        <td>{{ $s->customer->name }}</td>
                        <td>{{ $s->sale_date }}</td>
                        <td style="font-weight:700">Rp {{ number_format($s->total_amount, 0, ',', '.') }}</td>
                        <td><span class="badge badge-success">{{ ucfirst($s->status) }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--text-tertiary)">Belum ada riwayat penjualan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: Add/Edit Customer --}}
<div id="modal-customer" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3 id="modal-title">👥 Tambah Customer</h3>
            <button class="btn-icon" onclick="document.getElementById('modal-customer').classList.add('hidden')"><i data-lucide="x"></i></button>
        </div>
        <form id="customer-form" method="POST" action="{{ route('sales.customers.store') }}">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Customer <span style="color:var(--danger-400)">*</span></label>
                    <input type="text" name="name" id="c-name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="c-email" class="form-control">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="c-phone" class="form-control">
                </div>
                <div class="form-group">
                    <label>Status <span style="color:var(--danger-400)">*</span></label>
                    <select name="status" id="c-status" class="form-control" required>
                        <option value="active">Active</option>
                        <option value="leads">Leads</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" id="c-address" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + tab).style.display = 'block';
    btn.classList.add('active');
    if (window.lucide) lucide.createIcons();
}

function openAddCustomerModal() {
    document.getElementById('modal-title').innerText = '👥 Tambah Customer';
    document.getElementById('customer-form').action = "{{ route('sales.customers.store') }}";
    document.getElementById('form-method').value = "POST";
    document.getElementById('customer-form').reset();
    document.getElementById('modal-customer').classList.remove('hidden');
}

function openEditCustomerModal(c) {
    document.getElementById('modal-title').innerText = '👥 Edit Customer';
    document.getElementById('customer-form').action = `/sales/customers/${c.id}`;
    document.getElementById('form-method').value = "PUT";
    
    document.getElementById('c-name').value = c.name;
    document.getElementById('c-email').value = c.email || '';
    document.getElementById('c-phone').value = c.phone || '';
    document.getElementById('c-status').value = c.status;
    document.getElementById('c-address').value = c.address || '';
    
    document.getElementById('modal-customer').classList.remove('hidden');
}

function filterCustomers() {
    const search = document.getElementById('search-customer').value.toLowerCase();
    document.querySelectorAll('#customer-table tbody tr').forEach(row => {
        if (!row.dataset.name) return;
        const match = row.dataset.name.includes(search) || row.dataset.phone.includes(search);
        row.style.display = match ? '' : 'none';
    });
}

// Auto-switch tab on load
window.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');
    if (tab) {
        const btn = document.querySelector(`button[onclick*="'${tab}'"]`);
        if (btn) btn.click();
    }
});
</script>
@endpush

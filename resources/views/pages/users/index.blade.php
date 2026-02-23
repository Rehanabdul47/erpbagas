@extends('layouts.app')

@section('title', 'Manage Users')
@section('breadcrumb', 'Manage Users')

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
        <div class="stat-icon" style="background:var(--primary-500)"><i data-lucide="users"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $stats['total'] }}</span><span class="stat-label">Total User</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--success-500)"><i data-lucide="user-check"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $stats['active'] }}</span><span class="stat-label">User Aktif</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--info-500,#3b82f6)"><i data-lucide="shield"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $stats['roles'] }}</span><span class="stat-label">Total Role</span></div>
    </div>
</div>

{{-- User Table --}}
<div class="card">
    <div class="filter-bar" style="display:flex;gap:12px;margin-bottom:16px;flex-wrap:wrap;align-items:center">
        <input type="text" id="search-user" class="form-control" placeholder="Cari user..." style="max-width:250px" onkeyup="filterUsers()">
        <select id="filter-role" class="form-control" style="max-width:180px" onchange="filterUsers()">
            <option value="">Semua Role</option>
            @foreach($roles as $role)
            <option value="{{ $role->name }}">{{ $role->name }}</option>
            @endforeach
        </select>
        <div style="margin-left:auto">
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm"><i data-lucide="user-plus"></i> Tambah User</a>
        </div>
    </div>

    <div class="table-container">
        <table id="users-table">
            <thead>
                <tr>
                    <th>Nama</th><th>Email</th><th>Role</th><th>Department</th><th>Dibuat</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr data-name="{{ strtolower($u->name) }}" data-email="{{ strtolower($u->email) }}" data-role="{{ $u->roles->first()?->name ?? '' }}">
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:36px;height:36px;border-radius:50%;background:var(--primary-500);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:14px;flex-shrink:0">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <span style="font-weight:600;color:var(--text-primary)">{{ $u->name }}</span>
                        </div>
                    </td>
                    <td>{{ $u->email }}</td>
                    <td>
                        @php
                            $roleName = $u->roles->first()?->name ?? '-';
                            $roleBadge = match($roleName) {
                                'Executive' => 'badge-primary',
                                'Manager' => 'badge-info',
                                'Staff Gudang' => 'badge-warning',
                                'Staff Finance' => 'badge-success',
                                'Staff Sales' => 'badge-danger',
                                'Staff HR' => 'badge-info',
                                default => '',
                            };
                        @endphp
                        <span class="badge {{ $roleBadge }}">{{ $roleName }}</span>
                    </td>
                    <td style="text-transform:capitalize">{{ $u->department }}</td>
                    <td>{{ $u->created_at->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;gap:4px">
                            <a href="{{ route('users.edit', $u) }}" class="btn btn-ghost btn-xs" title="Edit"><i data-lucide="edit-2" style="width:14px;height:14px"></i></a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('Hapus user {{ $u->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-xs" style="color:var(--danger-400)" title="Hapus"><i data-lucide="trash-2" style="width:14px;height:14px"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-tertiary)">Belum ada data user</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterUsers() {
    const search = document.getElementById('search-user').value.toLowerCase();
    const role = document.getElementById('filter-role').value;
    document.querySelectorAll('#users-table tbody tr').forEach(row => {
        if (!row.dataset.name) return;
        const matchSearch = !search || row.dataset.name.includes(search) || row.dataset.email.includes(search);
        const matchRole = !role || row.dataset.role === role;
        row.style.display = (matchSearch && matchRole) ? '' : 'none';
    });
}
</script>
@endpush

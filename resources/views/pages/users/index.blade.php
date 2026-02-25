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
                <tr class="user-row" style="cursor:pointer" data-user='@json($u->load("roles"))' data-name="{{ strtolower($u->name) }}" data-email="{{ strtolower($u->email) }}" data-role="{{ $u->roles->first()?->name ?? '' }}">
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
                            <a href="{{ route('users.edit', $u) }}" class="btn btn-ghost btn-xs" title="Edit" onclick="event.stopPropagation()"><i data-lucide="edit-2" style="width:14px;height:14px"></i></a>
                            @if($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="event.stopPropagation(); return confirm('Hapus user {{ $u->name }}?')" style="display:inline" onclick="event.stopPropagation()">
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

{{-- Detail User Modal --}}
<div id="modal-user-view" class="modal-overlay hidden" onclick="if(event.target===this)closeModal('modal-user-view')">
    <div class="modal" style="max-width:500px">
        <div class="modal-header">
            <h3><i data-lucide="user" style="display:inline-block;vertical-align:middle;margin-right:8px;color:var(--primary-500)"></i> Profil Pengguna</h3>
            <button class="btn-icon" onclick="closeModal('modal-user-view')"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" id="user-detail-content">
            {{-- Content will be injected by JS --}}
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-user-view')">Tutup</button>
            <a id="btn-edit-link" href="#" class="btn btn-primary">Edit Profil</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.user-row').forEach(row => {
        row.addEventListener('click', function(e) {
            // Don't trigger if clicking on actions or buttons
            if (e.target.closest('.btn') || e.target.closest('form') || e.target.closest('a')) {
                return;
            }
            try {
                const userData = JSON.parse(this.getAttribute('data-user'));
                viewUser(userData);
            } catch (err) {
                console.error('Error parsing user data', err);
            }
        });
    });
});

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

function viewUser(user) {
    const roles = user.roles.map(r => r.name).join(', ') || '-';
    const initials = user.name.substring(0, 1).toUpperCase();
    const date = new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    
    document.getElementById('user-detail-content').innerHTML = `
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:80px;height:80px;border-radius:50%;background:var(--primary-500);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:28px;margin:0 auto 12px;box-shadow:0 10px 15px -3px rgba(var(--primary-rgb), 0.3)">
                ${initials}
            </div>
            <h4 style="font-size:20px;margin:0;font-weight:700;color:var(--text-primary)">${user.name}</h4>
            <div style="color:var(--text-tertiary);font-size:14px;margin-top:4px">${user.email}</div>
        </div>
        
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="detail-item">
                <div style="font-size:11px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:1px;font-weight:700">Role / Akses</div>
                <div style="font-weight:600;margin-top:4px;color:var(--text-primary)">${roles}</div>
            </div>
            <div class="detail-item">
                <div style="font-size:11px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:1px;font-weight:700">Departemen</div>
                <div style="font-weight:600;margin-top:4px;text-transform:capitalize;color:var(--text-primary)">${user.department || '-'}</div>
            </div>
            <div class="detail-item">
                <div style="font-size:11px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:1px;font-weight:700">Bergabung</div>
                <div style="font-weight:600;margin-top:4px;color:var(--text-primary)">${date}</div>
            </div>
            <div class="detail-item">
                <div style="font-size:11px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:1px;font-weight:700">User ID</div>
                <div style="font-weight:600;margin-top:4px;color:var(--text-primary)">#${user.id}</div>
            </div>
        </div>
    `;
    
    document.getElementById('btn-edit-link').href = `/users/${user.id}/edit`;
    document.getElementById('modal-user-view').classList.remove('hidden');
    
    if(window.lucide) lucide.createIcons();
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>
<style>
#users-table tbody tr:hover {
    background: rgba(var(--primary-rgb), 0.05) !important;
}
.detail-item {
    background: var(--bg-secondary);
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}
.modal-overlay.hidden {
    display: none;
}
.modal {
    background: var(--bg-card, white);
    padding: 24px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
}
</style>
@endpush

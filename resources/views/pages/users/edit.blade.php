@extends('layouts.app')

@section('title', 'Edit User')
@section('breadcrumb')
<a href="{{ route('users.index') }}" style="color:var(--text-tertiary);text-decoration:none">Manage Users</a>
<span style="margin:0 8px;color:var(--text-tertiary)">/</span>
<span>Edit {{ $user->name }}</span>
@endsection

@section('content')
<div class="card" style="max-width:700px">
    <h3 style="margin-bottom:20px">✏️ Edit User — {{ $user->name }}</h3>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:8px;padding:12px;margin-bottom:16px;color:#f87171;font-size:13px">
        @foreach($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="form-group">
                <label>Nama <span style="color:var(--danger-400)">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="form-group">
                <label>Email <span style="color:var(--danger-400)">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="form-group">
                <label>Password Baru</label>
                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
            </div>
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
            </div>
            <div class="form-group">
                <label>Role <span style="color:var(--danger-400)">*</span></label>
                <select name="role" class="form-control" required>
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Department <span style="color:var(--danger-400)">*</span></label>
                <select name="department" class="form-control" required>
                    @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ old('department', $user->department) === $dept ? 'selected' : '' }} style="text-transform:capitalize">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display:flex;gap:12px;margin-top:20px">
            <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Update</button>
            <a href="{{ route('users.index') }}" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
@endsection

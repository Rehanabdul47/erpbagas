@extends('layouts.app')

@section('title', 'Edit User')
@section('breadcrumb')
<div class="flex items-center gap-2 text-gray-400 text-sm">
    <a href="{{ route('users.index') }}" class="hover:text-white transition-colors">Manage Users</a>
    <span>/</span>
    <span class="text-white">Edit {{ $user->name }}</span>
</div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col p-6 relative">
        
        {{-- Abstract Graphic Header --}}
        <div class="absolute top-0 right-0 w-64 h-64 bg-amber-500/5 rounded-full blur-3xl -mx-20 -my-20 pointer-events-none"></div>

        <h3 class="font-bold text-white text-xl flex items-center gap-3 mb-6 relative z-10">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                <i data-lucide="user-cog" class="w-5 h-5"></i>
            </div>
            Edit User &mdash; {{ $user->name }}
        </h3>

        @if($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4 mb-6 relative z-10">
            <div class="flex items-center gap-2 text-red-400 font-bold mb-2">
                <i data-lucide="alert-circle" class="w-4 h-4"></i> Terdapat Kesalahan:
            </div>
            <ul class="list-disc list-inside space-y-1 text-red-300 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}" class="relative z-10">
            @csrf 
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex justify-between">
                        <span>Nama Lengkap</span> <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="user" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all focus:bg-gray-900 group-hover:border-gray-600" required>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex justify-between">
                        <span>Email Address</span> <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="mail" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all focus:bg-gray-900" required>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex justify-between">
                        <span>Password Baru</span>
                    </label>
                    <div class="relative" x-data="{ show: false }">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                        <input :type="show ? 'text' : 'password'" name="password" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg pl-10 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all focus:bg-gray-900" placeholder="Kosongkan jika tidak diubah">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-white transition-colors">
                            <i data-lucide="eye" x-show="!show" class="w-4 h-4"></i>
                            <i data-lucide="eye-off" x-show="show" x-cloak class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex justify-between">
                        <span>Konfirmasi Password Baru</span>
                    </label>
                    <div class="relative" x-data="{ show: false }">
                        <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg pl-10 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all focus:bg-gray-900" placeholder="Ulangi password baru">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-white transition-colors">
                            <i data-lucide="eye" x-show="!show" class="w-4 h-4"></i>
                            <i data-lucide="eye-off" x-show="show" x-cloak class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex justify-between">
                        <span>Role</span> <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="shield" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                        <select name="role" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg pl-10 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all focus:bg-gray-900 appearance-none cursor-pointer" required>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex justify-between">
                        <span>Departemen</span> <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="briefcase" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                        <select name="department" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg pl-10 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all focus:bg-gray-900 appearance-none cursor-pointer" required>
                            @foreach($departments as $dept)
                            <option value="{{ $dept }}" {{ old('department', $user->department) === $dept ? 'selected' : '' }} class="capitalize">{{ $dept }}</option>
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-bold text-gray-400 uppercase tracking-wider flex justify-between">
                        <span>Penempatan (Cabang/Lokasi)</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="map-pin" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                        <select name="branch_id" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg pl-10 pr-10 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all focus:bg-gray-900 appearance-none cursor-pointer">
                            <option value="" {{ old('branch_id', $user->branch_id) ? '' : 'selected' }}>Pusat (Tidak ada cabang)</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $user->branch_id) == $branch->id ? 'selected' : '' }} class="capitalize">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-6 border-t border-gray-700/50">
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-bold transition-all shadow-lg shadow-indigo-600/20 active:scale-95 flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Perubahan
                </button>
                <a href="{{ route('users.index') }}" class="px-6 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-bold transition-all border border-gray-600 hover:border-gray-500 active:scale-95">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Manage Users')
@section('breadcrumb', 'Manage Users')

@section('content')
<div class="space-y-6" x-data="usersPage()">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-500 flex items-center gap-3">
        <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Stats Container --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mb-6">
        <div class="bg-gray-800 border-[0.5px] border-b-4 border-gray-700/50 border-b-indigo-500 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="users" class="w-5 h-5"></i></div>
            <div class="text-3xl font-black text-white mb-1 tracking-tight">{{ $stats['total'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Total User</div>
        </div>
        <div class="bg-gray-800 border-[0.5px] border-b-4 border-gray-700/50 border-b-emerald-500 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="user-check" class="w-5 h-5"></i></div>
            <div class="text-3xl font-black text-white mb-1 tracking-tight">{{ $stats['active'] }}</div>
            <div class="text-gray-400 text-sm font-medium">User Aktif</div>
        </div>
        <div class="bg-gray-800 border-[0.5px] border-b-4 border-gray-700/50 border-b-amber-500 rounded-xl p-5 shadow-sm sm:col-span-2 md:col-span-1">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4"><i data-lucide="shield" class="w-5 h-5"></i></div>
            <div class="text-3xl font-black text-white mb-1 tracking-tight">{{ $stats['roles'] }}</div>
            <div class="text-gray-400 text-sm font-medium">Total Role</div>
        </div>
    </div>

    {{-- User Table Card --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col p-4 sm:p-5">
        
        {{-- Toolbar / Filter Bar --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                {{-- Search --}}
                <div class="relative w-full sm:w-64 shrink-0">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="text" x-model="searchQuery" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500" placeholder="Cari nama atau email...">
                </div>
                {{-- Role Filter --}}
                <div class="relative w-full sm:w-48 shrink-0">
                    <select x-model="selectedRole" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-3 pr-10 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none cursor-pointer">
                        <option value="">Semua Role</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 pointer-events-none"></i>
                </div>
            </div>
            
            <a href="{{ route('users.create') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20 w-full sm:w-auto whitespace-nowrap">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Tambah User
            </a>
        </div>

        {{-- Table Container --}}
        <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg text-sm">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-3 font-medium">Pengguna</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 font-medium">Departemen</th>
                        <th class="px-5 py-3 font-medium">Penempatan</th>
                        <th class="px-5 py-3 font-medium">Bergabung</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($users as $u)
                    <tr x-show="matchUser('{{ strtolower($u->name) }}', '{{ strtolower($u->email) }}', '{{ $u->roles->first()?->name ?? '' }}')" 
                        @click="openModal({{ json_encode($u->load('roles')) }})"
                        class="hover:bg-indigo-500/5 transition-colors group cursor-pointer active:bg-indigo-500/10">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-indigo-600 flex flex-col items-center justify-center text-white font-bold text-sm shrink-0 shadow-inner">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-white truncate max-w-[200px]">{{ $u->name }}</div>
                                    <div class="text-xs text-gray-500 truncate mt-0.5 max-w-[200px]">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $roleName = $u->roles->first()?->name ?? '-';
                                $roleBadge = match($roleName) {
                                    'Executive' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                    'Manager' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                    'Supervisor' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                    'Staff' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                    'Operator Produksi' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                    'Sales/Marketing' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                    'Admin' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                                    'Security' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                    'Driver' => 'bg-stone-500/10 text-stone-400 border-stone-500/20',
                                    default => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
                                };
                            @endphp
                            <span class="px-2.5 py-1 flex w-fit rounded-full text-xs font-bold border {{ $roleBadge }}">
                                {{ $roleName }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-gray-300 capitalize text-sm font-medium">{{ $u->department ?: '-' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if($u->branch)
                                <div class="flex items-center gap-1.5 text-gray-300 text-sm font-medium">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-indigo-400"></i>
                                    {{ $u->branch->name }}
                                </div>
                            @else
                                <span class="text-gray-500 text-sm font-medium italic">Pusat</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-gray-400 text-xs font-mono">
                            {{ $u->created_at->format('d M Y') }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-50 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('users.edit', $u) }}" @click.stop class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </a>
                                @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $u) }}" 
                                    @submit.prevent="$dispatch('global-confirm', { 
                                        title: 'HAPUS PENGGUNA', 
                                        message: 'Yakin ingin menghapus {{ $u->name }}? Operasi ini akan menghilangakan akses pengguna.',
                                        actionUrl: '{{ route('users.destroy', $u) }}',
                                        actionMethod: 'DELETE',
                                        confirmText: 'HAPUS'
                                    })" 
                                    @click.stop class="m-0">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-12 text-gray-500">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 rounded-full bg-gray-900/50 flex items-center justify-center text-gray-600">
                                    <i data-lucide="users" class="w-8 h-8"></i>
                                </div>
                                <p class="text-sm font-medium">Belum ada data user</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Empty filter state -->
        <div x-show="!hasMatchingUsers()" x-cloak class="text-center py-10 text-gray-500 bg-gray-900/30 rounded-lg mt-4 border border-gray-700/50 border-dashed">
            <p class="text-sm font-medium">Tidak ada user yang cocok dengan filter pencarian.</p>
        </div>
    </div>

    {{-- Detail User Modal --}}
    <div x-show="modals.view" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.view = false" class="bg-gray-800 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden flex flex-col pointer-events-auto transform transition-all relative">
            
            {{-- Abstract Graphic Header --}}
            <div class="h-24 bg-gradient-to-br from-indigo-600 to-indigo-900 w-full absolute top-0 left-0">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4IiBoZWlnaHQ9IjgiPgo8cmVjdCB3aWR0aD0iOCIgaGVpZ2h0PSI4IiBmaWxsPSIjZmZmIiBmaWxsLW9wYWNpdHk9IjAuMDUiLz4KPC9zdmc+')] opacity-20"></div>
            </div>

            {{-- Close Button --}}
            <button @click="modals.view = false" class="absolute top-4 right-4 p-1.5 text-white/70 hover:text-white bg-black/20 hover:bg-black/40 rounded-full transition-colors z-10"><i data-lucide="x" class="w-4 h-4"></i></button>

            <div class="px-5 pt-12 pb-6 relative z-10 flex flex-col items-center text-center">
                {{-- Avatar --}}
                <div class="w-20 h-20 rounded-full bg-indigo-500 border-4 border-gray-800 flex items-center justify-center text-white font-black text-3xl shadow-lg mb-3" x-text="activeUserInitials"></div>
                <h4 class="text-xl font-bold text-white mb-0" x-text="activeUser?.name"></h4>
                <div class="text-sm text-indigo-300 font-medium mb-1" x-text="activeUser?.email"></div>
                <div class="text-xs text-gray-400 flex items-center justify-center gap-1 mb-4">
                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                    <span x-text="activeUser?.branch?.name || 'Pusat'"></span>
                </div>
                
                <span class="px-3 py-1 rounded-full text-xs font-bold border bg-gray-900/80 border-gray-700 text-gray-300 shadow-inner" x-text="activeUserRoles() || 'No Role'"></span>
            </div>
            
            <div class="p-5 bg-gray-900/50 border-t border-gray-700/50">
                <div class="grid grid-cols-2 gap-3 mb-5">
                    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-3 shadow-inner">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Departemen</div>
                        <div class="text-sm font-bold text-white capitalize truncate" x-text="activeUser?.department || '-'"></div>
                    </div>
                    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-3 shadow-inner">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Bergabung</div>
                        <div class="text-sm font-bold text-white truncate" x-text="formatDate(activeUser?.created_at)"></div>
                    </div>
                </div>

                <div class="flex gap-2 w-full">
                    <button @click="modals.view = false" class="flex-1 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors border border-gray-600">Tutup</button>
                    <a :href="'/users/' + activeUser?.id + '/edit'" class="flex-1 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20 text-center flex items-center justify-center"><i data-lucide="edit-2" class="w-4 h-4 mr-2"></i> Edit Profil</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('usersPage', () => ({
        searchQuery: '',
        selectedRole: '',
        modals: {
            view: false
        },
        activeUser: null,
        
        matchUser(name, email, role) {
            const query = this.searchQuery.toLowerCase();
            const matchSearch = query === '' || name.includes(query) || email.includes(query);
            const matchRole = this.selectedRole === '' || role === this.selectedRole;
            return matchSearch && matchRole;
        },

        hasMatchingUsers() {
            // Rough JS estimation if all are hidden; real logic via visual empty state is cleaner
            // Alpine handles visibility directly on the rows anyway, this is just for the empty state div.
            // Using DOM check fallback due to lack of full dataset reactivity in this implementation
            const visibleRows = document.querySelectorAll('tbody tr[style*="display: none"]');
            const totalRows = document.querySelectorAll('tbody tr').length;
            // if not all rows are hidden
            return visibleRows.length !== totalRows; 
        },

        openModal(user) {
            this.activeUser = user;
            this.modals.view = true;
            
            // Re-initialize lucide icons for the newly updated modal content
            setTimeout(() => {
                if(window.lucide) {
                    window.lucide.createIcons();
                }
            }, 50);
        },

        get activeUserInitials() {
            if (!this.activeUser || !this.activeUser.name) return '?';
            return this.activeUser.name.substring(0, 1).toUpperCase();
        },

        activeUserRoles() {
            if (!this.activeUser || !this.activeUser.roles) return '-';
            return this.activeUser.roles.map(r => r.name).join(', ');
        },

        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        }
    }));
});
</script>
@endpush
@endsection

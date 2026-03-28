@extends('layouts.app')

@section('title', 'Audit Trail')
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Audit Trail</span>
@endsection

@section('content')
<div x-data="{ showDetail: false, detail: null }">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0">
            <i data-lucide="shield-check" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white m-0">Audit Trail</h3>
            <p class="text-xs text-gray-400 mt-0.5">Log aktivitas seluruh perubahan data keuangan</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm mb-6">
        <form class="flex flex-wrap items-center gap-3">
            <select name="action" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">Semua Aksi</option>
                @foreach(['created','updated','deleted','posted','voided','approved','rejected'] as $a)
                <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ ucfirst($a) }}</option>
                @endforeach
            </select>
            <select name="user_id" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">Semua User</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Dari">
            <input type="date" name="date_to" value="{{ request('date_to') }}" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Sampai">
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi..."
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-3 pr-10 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none placeholder-gray-500">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white"><i data-lucide="search" class="w-4 h-4"></i></button>
            </div>
        </form>
    </div>

    {{-- Trail List --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">Waktu</th>
                        <th class="px-5 py-4 font-medium">User</th>
                        <th class="px-5 py-4 font-medium">Aksi</th>
                        <th class="px-5 py-4 font-medium">Modul</th>
                        <th class="px-5 py-4 font-medium">Deskripsi</th>
                        <th class="px-5 py-4 font-medium text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($trails as $trail)
                    <tr class="hover:bg-gray-700/20 transition-colors">
                        <td class="px-5 py-4 text-gray-300 text-xs">
                            {{ $trail->created_at->format('d M Y') }}<br>
                            <span class="text-gray-500">{{ $trail->created_at->format('H:i:s') }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center text-[10px] font-bold text-gray-300">{{ substr($trail->user->name ?? 'S', 0, 1) }}</div>
                                <span class="text-gray-300 text-xs">{{ $trail->user->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            @php
                                $ac = ['created'=>'bg-emerald-500/10 text-emerald-400','updated'=>'bg-blue-500/10 text-blue-400','deleted'=>'bg-red-500/10 text-red-400','posted'=>'bg-indigo-500/10 text-indigo-400','voided'=>'bg-red-500/10 text-red-400','approved'=>'bg-emerald-500/10 text-emerald-400'];
                            @endphp
                            <span class="text-[10px] px-2 py-1 rounded-full font-bold {{ $ac[$trail->action] ?? 'bg-gray-500/10 text-gray-400' }}">{{ strtoupper($trail->action) }}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-400 text-xs font-mono">{{ class_basename($trail->auditable_type) }} #{{ $trail->auditable_id }}</td>
                        <td class="px-5 py-4 text-gray-300 text-xs max-w-[300px] truncate">{{ $trail->description ?? '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            @if($trail->old_values || $trail->new_values)
                            <button @click="detail = {{ json_encode($trail) }}; showDetail = true" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-12 text-gray-500">Tidak ada log audit ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trails->hasPages())
        <div class="p-4 border-t border-gray-700/50">{{ $trails->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Detail Modal --}}
    <div x-show="showDetail" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4">
        <div @click.away="showDetail = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2"><i data-lucide="diff" class="w-4 h-4 text-indigo-400"></i> Detail Perubahan</h3>
                <button @click="showDetail = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-5 max-h-[60vh] overflow-y-auto space-y-4">
                <template x-if="detail && detail.old_values">
                    <div>
                        <div class="text-[10px] font-bold text-red-400 uppercase tracking-widest mb-2">Sebelum (Old Values)</div>
                        <pre class="bg-gray-900 rounded-lg p-3 text-xs text-red-300 overflow-x-auto" x-text="JSON.stringify(detail.old_values, null, 2)"></pre>
                    </div>
                </template>
                <template x-if="detail && detail.new_values">
                    <div>
                        <div class="text-[10px] font-bold text-emerald-400 uppercase tracking-widest mb-2">Sesudah (New Values)</div>
                        <pre class="bg-gray-900 rounded-lg p-3 text-xs text-emerald-300 overflow-x-auto" x-text="JSON.stringify(detail.new_values, null, 2)"></pre>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

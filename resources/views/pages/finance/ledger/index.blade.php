@extends('layouts.app')

@section('title', 'General Ledger')
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">General Ledger</span>
@endsection

@section('content')
<div>
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 flex items-center gap-3">
        <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">
                <i data-lucide="book-open" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white m-0">General Ledger</h3>
                <p class="text-xs text-gray-400 mt-0.5">Buku besar — seluruh jurnal transaksi keuangan</p>
            </div>
        </div>
        <a href="{{ route('finance.ledger.create') }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20">
            <i data-lucide="plus" class="w-4 h-4"></i> Entri Jurnal Baru
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm mb-6">
        <form class="flex flex-wrap items-center gap-3">
            <input type="month" name="month" value="{{ $month }}" onchange="this.form.submit()"
                   class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            <select name="status" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <option value="">Semua Status</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="posted" {{ request('status') == 'posted' ? 'selected' : '' }}>Posted</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Void</option>
            </select>
            <select name="account_id" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none max-w-[220px]">
                <option value="">Semua Akun</option>
                @foreach($accounts as $acc)
                <option value="{{ $acc->id }}" {{ request('account_id') == $acc->id ? 'selected' : '' }}>{{ $acc->code }} — {{ $acc->name }}</option>
                @endforeach
            </select>
            <div class="relative flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari referensi / deskripsi..."
                       class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-3 pr-10 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none placeholder-gray-500">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- Entries Table --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">Tanggal</th>
                        <th class="px-5 py-4 font-medium">Referensi</th>
                        <th class="px-5 py-4 font-medium">Deskripsi</th>
                        <th class="px-5 py-4 font-medium text-right">Debit</th>
                        <th class="px-5 py-4 font-medium text-right">Kredit</th>
                        <th class="px-5 py-4 font-medium text-center">Status</th>
                        <th class="px-5 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($entries as $entry)
                    <tr class="hover:bg-gray-700/20 transition-colors group">
                        <td class="px-5 py-4 text-gray-300 font-medium">{{ $entry->date->format('d M Y') }}</td>
                        <td class="px-5 py-4">
                            <span class="font-mono text-xs px-2 py-0.5 bg-gray-900 text-indigo-400 rounded">{{ $entry->reference ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-300 max-w-[300px] truncate">{{ $entry->description }}</td>
                        <td class="px-5 py-4 text-right font-bold text-blue-400">Rp {{ number_format($entry->items->sum('debit'), 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right font-bold text-orange-400">Rp {{ number_format($entry->items->sum('credit'), 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $statusColors = ['draft' => 'bg-gray-500/10 text-gray-400', 'posted' => 'bg-blue-500/10 text-blue-400', 'approved' => 'bg-emerald-500/10 text-emerald-400', 'void' => 'bg-red-500/10 text-red-400'];
                            @endphp
                            <span class="text-[10px] px-2 py-1 rounded-full font-bold {{ $statusColors[$entry->status] ?? '' }}">
                                {{ strtoupper($entry->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('finance.ledger.show', $entry) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors inline-block" title="Detail">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-12 text-gray-500">Tidak ada jurnal ditemukan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
        <div class="p-4 border-t border-gray-700/50">{{ $entries->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection

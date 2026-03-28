@extends('layouts.app')

@section('title', 'Buku Kas (Finance)')
@section('breadcrumb', 'Buku Kas')

@section('content')
<div x-data="{
    showModalIn: false,
    showModalOut: false,
    showModalEdit: false,
    showModalSettings: false,
    rePercentage: {{ $rePercentage }},
    editTrx: { id: '', date: '', category: '', amount: '', description: '', reference: '', type: '' },
    method: 'Tunai',
    openEditModal(trx) {
        if (!trx) return;
        this.editTrx = {
            id: trx.id,
            date: trx.date ? trx.date.split(/[T ]/)[0] : '',
            category: trx.category || '',
            amount: trx.amount || 0,
            description: trx.description || '',
            reference: trx.reference || '',
            type: trx.type || 'out'
        };
        this.showModalEdit = true;
    },
    appendMethod(target) {
        var prefix = '[' + this.method + '] ';
        var elId = target === 'in' ? 'desc-in' : 'desc-out';
        var el = document.getElementById(elId);
        if (el && el.value && !el.value.startsWith('[')) {
            el.value = prefix + el.value;
        }
    }
}">

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

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 md:gap-6 mb-6">
        <div class="bg-gray-800 border-l-4 border-indigo-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="wallet" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-white mb-1">Rp {{ number_format($stats['totalBalance'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Saldo Kas Saat Ini</div>
        </div>
        <div class="bg-gray-800 border-l-4 border-emerald-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-4"><i data-lucide="arrow-down-left" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-emerald-400 mb-1">Rp {{ number_format($stats['monthlyIn'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Masuk ({{ $stats['currentMonth'] }})</div>
        </div>
        <div class="bg-gray-800 border-l-4 border-red-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center mb-4"><i data-lucide="arrow-up-right" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-red-400 mb-1">Rp {{ number_format($stats['monthlyOut'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Keluar ({{ $stats['currentMonth'] }})</div>
        </div>
        <div class="bg-gray-800 border-l-4 border-amber-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4"><i data-lucide="banknote" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-amber-400 mb-1">Rp {{ number_format($stats['totalPiutang'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Total Piutang Usaha</div>
        </div>
        <div class="bg-gray-800 border-l-4 border-purple-500 border-y border-r border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center mb-4"><i data-lucide="credit-card" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-purple-400 mb-1">Rp {{ number_format($stats['totalHutang'] ?? 0, 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Total Hutang Usaha</div>
        </div>
    </div>

    {{-- Filters and Actions --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm mb-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <form id="filter-form" class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <h2 class="text-lg font-bold text-white mr-2">Buku Kas</h2>
                
                <input type="month" name="month" value="{{ $month }}" onchange="document.getElementById('filter-form').submit()"
                       class="w-full sm:w-auto bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                
                <select name="type" onchange="document.getElementById('filter-form').submit()"
                        class="w-full sm:w-auto bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    <option value="">Semua Tipe</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>Masuk</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>Keluar</option>
                </select>
                
                <div class="relative w-full sm:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari transaksi..."
                           class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-3 pr-10 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <div class="flex flex-wrap gap-2 w-full lg:w-auto">
                <a href="{{ route('finance.print', ['month' => $month, 'type' => request('type'), 'search' => request('search')]) }}" target="_blank" class="flex items-center justify-center gap-2 px-3 py-2 bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500 hover:text-white rounded-lg text-sm font-medium transition-colors border border-indigo-500/20" title="Cetak Buku Kas">
                    <i data-lucide="printer" class="w-4 h-4"></i> Cetak
                </a>
                <a href="{{ route('finance.reports', ['month' => $month]) }}" class="flex items-center justify-center gap-2 px-3 py-2 bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500 hover:text-white rounded-lg text-sm font-medium transition-colors border border-indigo-500/20">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Laporan
                </a>
                <button @click="showModalSettings = true" class="flex items-center justify-center gap-2 px-3 py-2 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-lg text-sm font-medium transition-colors border border-gray-600" title="Pengaturan Persentase Laba">
                    <i data-lucide="settings" class="w-4 h-4"></i>
                </button>
                <button @click="showModalIn = true" class="flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-emerald-600/20">
                    <i data-lucide="plus" class="w-4 h-4"></i> Masuk
                </button>
                <button @click="showModalOut = true" class="flex items-center justify-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-red-600/20">
                    <i data-lucide="minus" class="w-4 h-4"></i> Keluar
                </button>
            </div>
        </div>
    </div>

    {{-- Transactions Table --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden w-full">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">Tanggal</th>
                        <th class="px-5 py-4 font-medium">Kategori</th>
                        <th class="px-5 py-4 font-medium">Keterangan</th>
                        <th class="px-5 py-4 font-medium text-right">Nominal</th>
                        <th class="px-5 py-4 font-medium">Pencatat</th>
                        <th class="px-5 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-gray-700/20 transition-colors group">
                        <td class="px-5 py-4 text-gray-300 font-medium whitespace-nowrap">{{ $trx->date->format('d M Y') }}</td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($trx->type === 'in')
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                                </div>
                                @else
                                <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center shrink-0">
                                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i>
                                </div>
                                @endif
                                <span class="font-bold text-white">{{ $trx->category }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="text-sm text-gray-300 mb-0.5 whitespace-normal break-words min-w-[200px]">{{ $trx->description }}</div>
                            @if($trx->reference)
                            <div class="text-xs text-gray-500 font-mono inline-flex items-center gap-1 bg-gray-900 px-2 py-0.5 rounded">
                                <i data-lucide="hash" class="w-3 h-3"></i> {{ $trx->reference }}
                            </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right font-bold {{ $trx->type === 'in' ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $trx->type === 'in' ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center text-xs font-bold text-gray-300">
                                    {{ substr($trx->user->name ?? 'S', 0, 1) }}
                                </div>
                                <span class="text-gray-400">{{ $trx->user->name ?? 'System' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="openEditModal({{ json_encode($trx) }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Edit">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </button>
                                <form method="POST" action="{{ route('finance.destroy', $trx) }}" 
                                    @submit.prevent="$dispatch('global-confirm', { 
                                        title: 'HAPUS TRANSAKSI', 
                                        message: 'Yakin ingin menghapus catatan kas ini? Saldo buku kas akan ikut diperbarui secara otomatis.',
                                        actionUrl: '{{ route('finance.destroy', $trx) }}',
                                        actionMethod: 'DELETE',
                                        confirmText: 'HAPUS'
                                    })" 
                                    class="m-0">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-12 text-gray-500">Tidak ada transaksi kas di periode ini</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Kas Masuk --}}
    <div x-show="showModalIn" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showModalIn = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2 px-2 py-1 bg-emerald-500/10 text-emerald-400 rounded-lg">
                    <i data-lucide="arrow-down-left" class="w-4 h-4"></i> Catat Kas Masuk
                </h3>
                <button type="button" @click="showModalIn = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
             <form method="POST" action="{{ route('finance.store') }}" @submit="appendMethod('in')">
                @csrf
                <input type="hidden" name="type" value="in">
                <div class="p-5 flex flex-col gap-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Tanggal Transaksi</label>
                            <input type="date" name="date" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Metode Penerimaan</label>
                            <select x-model="method" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all">
                                <option value="Tunai">Tunai / Cash</option>
                                <option value="Transfer">Transfer Bank</option>
                                <option value="QRIS">QRIS / E-Wallet</option>
                                <option value="Giro">Cek / Giro</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Akun Penerimaan</label>
                        <select name="category" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all" required>
                            <option value="">-- Pilih Akun --</option>
                            @foreach($accounts->where('is_active', true)->groupBy(function($a) { return substr($a->code, 0, 1); }) as $prefix => $grpAccounts)
                                @php
                                    $groupName = match($prefix) { '1' => 'Aset', '2' => 'Liabilitas', '3' => 'Ekuitas', '4' => 'Pendapatan', '5' => 'Beban Pokok', '6' => 'Beban Operasional', '7' => 'Lainnya', default => 'Lainnya' };
                                @endphp
                                <optgroup label="{{ $groupName }}">
                                    @foreach($grpAccounts as $account)
                                        <option value="{{ $account->name }} ({{ $account->code }})">
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Nominal (Rp)</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">Rp</span>
                            <input type="number" name="amount" class="w-full bg-gray-900 border border-gray-700 text-emerald-400 font-black text-xl rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-emerald-500 outline-none transition-all group-hover:border-gray-600" min="1" step="0.01" placeholder="0" required>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach([50000, 100000, 500000, 1000000] as $amt)
                            <button type="button" @click="$el.closest('.space-y-1.5').querySelector('input').value = '{{ $amt }}'" 
                                    class="text-[10px] px-2 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded transition-colors">
                                +{{ number_format($amt/1000, 0) }}k
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Keterangan</label>
                            <textarea id="desc-in" name="description" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all placeholder-gray-600" rows="2" placeholder="Contoh: Pembayaran invoice #001..." required></textarea>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Referensi / No. Dok</label>
                            <input type="text" name="reference" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none transition-all placeholder-gray-600" placeholder="No. Invoice / Kwitansi">
                            <p class="text-[9px] text-gray-500 italic pl-1">Opsional: isi kode unik transaksi</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModalIn = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-emerald-900/20 active:scale-95 flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Pemasukan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Kas Keluar --}}
    <div x-show="showModalOut" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showModalOut = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2 px-2 py-1 bg-red-500/10 text-red-400 rounded-lg">
                    <i data-lucide="arrow-up-right" class="w-4 h-4"></i> Catat Kas Keluar
                </h3>
                <button type="button" @click="showModalOut = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
             <form method="POST" action="{{ route('finance.store') }}" @submit="appendMethod('out')">
                @csrf
                <input type="hidden" name="type" value="out">
                <div class="p-5 flex flex-col gap-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Tanggal Transaksi</label>
                            <input type="date" name="date" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none transition-all" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Metode Pembayaran</label>
                            <select x-model="method" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none transition-all">
                                <option value="Tunai">Tunai / Cash</option>
                                <option value="Transfer">Transfer Bank</option>
                                <option value="QRIS">QRIS / E-Wallet</option>
                                <option value="Giro">Cek / Giro</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Akun Pengeluaran</label>
                        <select name="category" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none transition-all" required>
                            <option value="">-- Pilih Akun --</option>
                            @foreach($accounts->where('is_active', true)->groupBy(function($a) { return substr($a->code, 0, 1); }) as $prefix => $grpAccounts)
                                @php
                                    $groupName = match($prefix) { '1' => 'Aset', '2' => 'Liabilitas', '3' => 'Ekuitas', '4' => 'Pendapatan', '5' => 'Beban Pokok', '6' => 'Beban Operasional', '7' => 'Lainnya', default => 'Lainnya' };
                                @endphp
                                <optgroup label="{{ $groupName }}">
                                    @foreach($grpAccounts as $account)
                                        <option value="{{ $account->name }} ({{ $account->code }})">
                                            {{ $account->code }} - {{ $account->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Nominal (Rp)</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold">Rp</span>
                            <input type="number" name="amount" class="w-full bg-gray-900 border border-gray-700 text-red-400 font-black text-xl rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-red-500 outline-none transition-all group-hover:border-gray-600" min="1" step="0.01" placeholder="0" required>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach([50000, 100000, 500000, 1000000] as $amt)
                            <button type="button" @click="$el.closest('.space-y-1.5').querySelector('input').value = '{{ $amt }}'" 
                                    class="text-[10px] px-2 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded transition-colors">
                                +{{ number_format($amt/1000, 0) }}k
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Keterangan</label>
                            <textarea id="desc-out" name="description" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none transition-all placeholder-gray-600" rows="2" placeholder="Contoh: Bayar listrik bulan ini..." required></textarea>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Referensi / No. Bukti</label>
                            <input type="text" name="reference" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-red-500 outline-none transition-all placeholder-gray-600" placeholder="No. Nota / Bukti Transfer">
                            <p class="text-[9px] text-gray-500 italic pl-1">Opsional: isi no. bukti bayar</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModalOut = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-red-900/20 active:scale-95 flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Simpan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit Transaksi --}}
    <div x-show="showModalEdit" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showModalEdit = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2 px-2 py-1 rounded-lg" :class="editTrx.type === 'out' ? 'bg-amber-500/10 text-amber-500' : 'bg-indigo-500/10 text-indigo-400'">
                    <i data-lucide="edit-2" class="w-4 h-4"></i> <span x-text="editTrx.type === 'out' ? 'Edit Kas Keluar' : 'Edit Kas Masuk'"></span>
                </h3>
                <button type="button" @click="showModalEdit = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" :action="'/finance/' + editTrx.id">
                @csrf @method('PUT')
                <div class="p-5 flex flex-col gap-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Tanggal</label>
                            <input type="date" name="date" x-model="editTrx.date" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Akun</label>
                            <select name="category" x-model="editTrx.category" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                                <option value="">-- Pilih Akun --</option>
                                @foreach($accounts->where('is_active', true)->groupBy(function($a) { return substr($a->code, 0, 1); }) as $prefix => $grpAccounts)
                                    @php
                                        $groupName = match($prefix) { '1' => 'Aset', '2' => 'Liabilitas', '3' => 'Ekuitas', '4' => 'Pendapatan', '5' => 'Beban Pokok', '6' => 'Beban Operasional', '7' => 'Lainnya', default => 'Lainnya' };
                                    @endphp
                                    <optgroup label="{{ $groupName }}">
                                        @foreach($grpAccounts as $account)
                                            <option value="{{ $account->name }} ({{ $account->code }})">
                                                {{ $account->code }} - {{ $account->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Nominal (Rp)</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                            <input type="number" name="amount" x-model="editTrx.amount" class="w-full bg-gray-900 border border-gray-700 text-white font-black text-xl rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-indigo-500 outline-none transition-all" min="1" step="0.01" required>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2">
                            @foreach([50000, 100000, 500000, 1000000] as $amt)
                            <button type="button" @click="editTrx.amount = '{{ $amt }}'" 
                                    class="text-[10px] px-2 py-1 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded transition-colors">
                                Set {{ number_format($amt/1000, 0) }}k
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Keterangan Lengkap</label>
                        <textarea name="description" x-model="editTrx.description" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" rows="3" required></textarea>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Referensi / No. Dok</label>
                        <input type="text" name="reference" x-model="editTrx.reference" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModalEdit = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-900/20 active:scale-95 flex items-center gap-2" :class="editTrx.type === 'out' ? 'bg-amber-600 hover:bg-amber-500' : ''">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i> Update Data
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Modal Pengaturan Laba Ditahan --}}
    <div x-show="showModalSettings" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="showModalSettings = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2 px-2 py-1 bg-indigo-500/10 text-indigo-400 rounded-lg">
                    <i data-lucide="settings" class="w-4 h-4"></i> Pengaturan Keuangan
                </h3>
                <button type="button" @click="showModalSettings = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('settings.update') }}">
                @csrf
                <input type="hidden" name="group" value="finance">
                <div class="p-6 flex flex-col gap-6">
                    <div class="space-y-3">
                        <label class="text-sm font-bold text-gray-300">Persentase Laba Ditahan</label>
                        <p class="text-xs text-gray-500 italic">Tentukan berapa persen dari Laba Bersih yang akan dialokasikan sebagai Laba Ditahan di Neraca.</p>
                        <div class="relative group">
                            <input type="number" name="finance_retained_earnings_percentage" 
                                   x-model="rePercentage" 
                                   class="w-full bg-gray-900 border border-gray-700 text-indigo-400 font-bold text-2xl rounded-xl pl-6 pr-12 py-4 focus:ring-2 focus:ring-indigo-500 outline-none transition-all group-hover:border-gray-600" 
                                   min="0" max="100" step="1" required>
                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-gray-400 font-black text-xl">%</span>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-500 px-2">
                            <span>Laba Ditahan: <span class="text-indigo-400 font-bold" x-text="rePercentage + '%'"></span></span>
                            <span>Laba Tahun Berjalan: <span class="text-emerald-400 font-bold" x-text="(100 - rePercentage) + '%'"></span></span>
                        </div>
                    </div>

                    <div class="bg-indigo-500/5 border border-indigo-500/20 rounded-xl p-4 flex gap-4">
                        <i data-lucide="info" class="w-5 h-5 text-indigo-400 shrink-0"></i>
                        <p class="text-[11px] text-gray-400 leading-relaxed">
                            Pengaturan ini akan langsung mempengaruhi tampilan pada <strong>Laporan Neraca</strong>. Sisa persentase (100% - Laba Ditahan) akan otomatis dianggap sebagai Laba Tahun Berjalan.
                        </p>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModalSettings = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-900/20 active:scale-95 flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i> Terapkan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

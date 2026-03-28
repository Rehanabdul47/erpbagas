@extends('layouts.app')

@section('title', 'Entri Jurnal Baru')
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<a href="{{ route('finance.ledger.index') }}" class="text-gray-400 hover:text-white transition-colors">General Ledger</a>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Entri Jurnal Baru</span>
@endsection

@section('content')
<div x-data="{
    items: [
        { account_id: '', debit: 0, credit: 0, description: '' },
        { account_id: '', debit: 0, credit: 0, description: '' },
    ],
    get totalDebit() { return this.items.reduce((s, i) => s + parseFloat(i.debit || 0), 0); },
    get totalCredit() { return this.items.reduce((s, i) => s + parseFloat(i.credit || 0), 0); },
    get isBalanced() { return Math.abs(this.totalDebit - this.totalCredit) < 0.01 && this.totalDebit > 0; },
    get difference() { return Math.abs(this.totalDebit - this.totalCredit); },
    addRow() { this.items.push({ account_id: '', debit: 0, credit: 0, description: '' }); },
    removeRow(index) { if(this.items.length > 2) this.items.splice(index, 1); },
    formatRupiah(val) { return new Intl.NumberFormat('id-ID').format(val); }
}">
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 flex items-center gap-3">
        <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0">
            <i data-lucide="file-plus" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white m-0">Entri Jurnal Baru</h3>
            <p class="text-xs text-gray-400 mt-0.5">Buat pencatatan jurnal manual — pastikan debit dan kredit seimbang</p>
        </div>
    </div>

    <form method="POST" action="{{ route('finance.ledger.store') }}">
        @csrf
        {{-- Header --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Tanggal</label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Referensi</label>
                    <input type="text" name="reference" value="{{ old('reference') }}" placeholder="JV-001" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Cost Center</label>
                    <input type="text" name="cost_center" value="{{ old('cost_center') }}" placeholder="Opsional" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Periode Fiskal</label>
                    <select name="fiscal_period_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">— Otomatis —</option>
                        @foreach($periods as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->start_date->format('d/m') }} - {{ $p->end_date->format('d/m/Y') }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 space-y-1.5">
                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Deskripsi Jurnal</label>
                <textarea name="description" rows="2" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none placeholder-gray-600" placeholder="Deskripsi transaksi..." required>{{ old('description') }}</textarea>
            </div>
        </div>

        {{-- Journal Items --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="table" class="w-4 h-4 text-indigo-400"></i> Detail Item Jurnal
                </h4>
                <button type="button" @click="addRow()" class="flex items-center gap-1 px-3 py-1.5 bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500 hover:text-white rounded-lg text-xs font-medium transition-colors">
                    <i data-lucide="plus" class="w-3 h-3"></i> Tambah Baris
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 font-medium w-[40%]">Akun</th>
                            <th class="px-4 py-3 font-medium text-right w-[22%]">Debit (Rp)</th>
                            <th class="px-4 py-3 font-medium text-right w-[22%]">Kredit (Rp)</th>
                            <th class="px-4 py-3 font-medium text-center w-[16%]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="hover:bg-gray-700/20">
                                <td class="px-4 py-3">
                                    <select :name="'items['+index+'][account_id]'" x-model="item.account_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                                        <option value="">— Pilih Akun —</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'items['+index+'][debit]'" x-model="item.debit" @input="if(item.debit > 0) item.credit = 0" min="0" step="0.01" class="w-full bg-gray-900 border border-gray-700 text-blue-400 font-bold rounded-lg px-3 py-2 text-sm text-right focus:ring-2 focus:ring-blue-500 outline-none">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'items['+index+'][credit]'" x-model="item.credit" @input="if(item.credit > 0) item.debit = 0" min="0" step="0.01" class="w-full bg-gray-900 border border-gray-700 text-orange-400 font-bold rounded-lg px-3 py-2 text-sm text-right focus:ring-2 focus:ring-orange-500 outline-none">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeRow(index)" x-show="items.length > 2" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="border-t-2 border-gray-600">
                        <tr class="bg-gray-900/50">
                            <td class="px-4 py-4 text-right font-bold text-white">TOTAL</td>
                            <td class="px-4 py-4 text-right font-black text-lg text-blue-400" x-text="'Rp ' + formatRupiah(totalDebit)"></td>
                            <td class="px-4 py-4 text-right font-black text-lg text-orange-400" x-text="'Rp ' + formatRupiah(totalCredit)"></td>
                            <td class="px-4 py-4 text-center">
                                <div x-show="isBalanced" class="text-emerald-400 text-xs font-bold flex items-center justify-center gap-1">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i> Balanced
                                </div>
                                <div x-show="!isBalanced && totalDebit > 0" class="text-red-400 text-xs font-bold flex items-center justify-center gap-1">
                                    <i data-lucide="alert-circle" class="w-4 h-4"></i> Selisih <span x-text="formatRupiah(difference)"></span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('finance.ledger.index') }}" class="px-4 py-2 text-gray-400 hover:text-white transition-colors text-sm">← Kembali</a>
            <div class="flex gap-3">
                <button type="submit" name="status" value="draft" class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-gray-300 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Draft
                </button>
                <button type="submit" name="status" value="posted" :disabled="!isBalanced" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:bg-gray-700 disabled:text-gray-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-900/20 active:scale-95 flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Post Jurnal
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

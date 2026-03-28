@extends('layouts.app')

@section('title', 'Buat Anggaran')
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<a href="{{ route('finance.budgets.index') }}" class="text-gray-400 hover:text-white">Anggaran</a>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Buat Anggaran</span>
@endsection

@section('content')
<div x-data="{
    items: [{ account_id: '', planned_amount: 0 }],
    get totalPlanned() { return this.items.reduce((s, i) => s + parseFloat(i.planned_amount || 0), 0); },
    addRow() { this.items.push({ account_id: '', planned_amount: 0 }); },
    removeRow(i) { if(this.items.length > 1) this.items.splice(i, 1); },
    formatRupiah(val) { return new Intl.NumberFormat('id-ID').format(val); }
}">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
            <i data-lucide="file-plus" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white m-0">Buat Anggaran Baru</h3>
            <p class="text-xs text-gray-400 mt-0.5">Tentukan alokasi anggaran per akun untuk satu periode</p>
        </div>
    </div>

    <form method="POST" action="{{ route('finance.budgets.store') }}">
        @csrf
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Nama Anggaran</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Anggaran Q1 2026" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Periode Fiskal</label>
                    <select name="fiscal_period_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <option value="">— Pilih Periode —</option>
                        @foreach($periods as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Departemen</label>
                    <input type="text" name="department" placeholder="Opsional" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Cost Center</label>
                    <input type="text" name="cost_center" placeholder="Opsional" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>
        </div>

        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2"><i data-lucide="list" class="w-4 h-4 text-indigo-400"></i> Item Anggaran</h4>
                <button type="button" @click="addRow()" class="flex items-center gap-1 px-3 py-1.5 bg-indigo-500/10 text-indigo-400 hover:bg-indigo-500 hover:text-white rounded-lg text-xs font-medium transition-colors">
                    <i data-lucide="plus" class="w-3 h-3"></i> Tambah
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 font-medium w-[60%]">Akun</th>
                            <th class="px-4 py-3 font-medium text-right w-[30%]">Jumlah Anggaran (Rp)</th>
                            <th class="px-4 py-3 font-medium text-center w-[10%]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        <template x-for="(item, index) in items" :key="index">
                            <tr>
                                <td class="px-4 py-3">
                                    <select :name="'items['+index+'][account_id]'" x-model="item.account_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                                        <option value="">— Pilih Akun —</option>
                                        @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" :name="'items['+index+'][planned_amount]'" x-model="item.planned_amount" min="0" step="1" class="w-full bg-gray-900 border border-gray-700 text-emerald-400 font-bold rounded-lg px-3 py-2 text-sm text-right focus:ring-2 focus:ring-emerald-500 outline-none">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" @click="removeRow(index)" x-show="items.length > 1" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="border-t-2 border-gray-600 bg-gray-900/50">
                        <tr>
                            <td class="px-4 py-4 text-right font-bold text-white">TOTAL ANGGARAN</td>
                            <td class="px-4 py-4 text-right font-black text-lg text-emerald-400" x-text="'Rp ' + formatRupiah(totalPlanned)"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('finance.budgets.index') }}" class="text-gray-400 hover:text-white text-sm">← Kembali</a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95 flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i> Simpan Anggaran
            </button>
        </div>
    </form>
</div>
@endsection

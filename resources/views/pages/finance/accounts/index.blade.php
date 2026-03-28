@extends('layouts.app')

@section('title', 'Chart of Accounts')
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Chart of Accounts</span>
@endsection

@section('content')
<div x-data="{
    showModal: false,
    editMode: false,
    form: { code: '', name: '', type: 'asset', parent_id: '', description: '', is_header: false, is_control: false, normal_balance: 'debit', department_code: '', cost_center_code: '' },
    editId: null,
    openCreate(parentId = '') {
        this.editMode = false;
        this.editId = null;
        this.form = { code: '', name: '', type: 'asset', parent_id: parentId, description: '', is_header: false, is_control: false, normal_balance: 'debit', department_code: '', cost_center_code: '' };
        this.showModal = true;
    },
    openEdit(acc) {
        this.editMode = true;
        this.editId = acc.id;
        this.form = {
            code: acc.code, name: acc.name, type: acc.type,
            parent_id: acc.parent_id || '',
            description: acc.description || '',
            is_header: acc.is_header ? true : false,
            is_control: acc.is_control ? true : false,
            normal_balance: acc.normal_balance || 'debit',
            department_code: acc.department_code || '',
            cost_center_code: acc.cost_center_code || '',
        };
        this.showModal = true;
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

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
                <i data-lucide="list-tree" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white m-0">Chart of Accounts</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kelola struktur akun keuangan perusahaan</p>
            </div>
        </div>
        <button @click="openCreate()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Akun
        </button>
    </div>

    {{-- COA Tree --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">Kode Akun</th>
                        <th class="px-5 py-4 font-medium">Nama Akun</th>
                        <th class="px-5 py-4 font-medium">Tipe</th>
                        <th class="px-5 py-4 font-medium text-center">Normal</th>
                        <th class="px-5 py-4 font-medium text-center">Status</th>
                        <th class="px-5 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @foreach($accounts as $account)
                        @include('pages.finance.accounts._row', ['account' => $account, 'depth' => 0])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Create/Edit --}}
    <div x-show="showModal" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4">
        <div @click.away="showModal = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2 px-2 py-1 bg-indigo-500/10 text-indigo-400 rounded-lg">
                    <i data-lucide="list-tree" class="w-4 h-4"></i>
                    <span x-text="editMode ? 'Edit Akun' : 'Tambah Akun Baru'"></span>
                </h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="editMode ? '/finance/accounts/' + editId : '{{ route('finance.accounts.store') }}'" method="POST">
                @csrf
                <template x-if="editMode"><input type="hidden" name="_method" value="PUT"></template>
                <div class="p-5 flex flex-col gap-4 max-h-[60vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Kode Akun</label>
                            <input type="text" name="code" x-model="form.code" :disabled="editMode" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none disabled:opacity-50" placeholder="1-101" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Tipe Akun</label>
                            <select name="type" x-model="form.type" @change="form.normal_balance = ['asset','expense'].includes(form.type) ? 'debit' : 'credit'" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" :disabled="editMode">
                                <option value="asset">Aset</option>
                                <option value="liability">Liabilitas</option>
                                <option value="equity">Ekuitas</option>
                                <option value="revenue">Pendapatan</option>
                                <option value="expense">Beban</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Nama Akun</label>
                        <input type="text" name="name" x-model="form.name" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Kas & Setara Kas" required>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Akun Induk (Opsional)</label>
                        <select name="parent_id" x-model="form.parent_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            <option value="">— Tidak Ada (Root) —</option>
                            @foreach($flatAccounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Keterangan</label>
                        <textarea name="description" x-model="form.description" rows="2" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Opsional"></textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Saldo Normal</label>
                            <select name="normal_balance" x-model="form.normal_balance" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="debit">Debit</option>
                                <option value="credit">Kredit</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Dept Code</label>
                            <input type="text" name="department_code" x-model="form.department_code" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="FIN">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Cost Center</label>
                            <input type="text" name="cost_center_code" x-model="form.cost_center_code" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="CC01">
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_header" x-model="form.is_header" value="1" class="w-4 h-4 rounded bg-gray-900 border-gray-600 text-indigo-500 focus:ring-indigo-500">
                            <span class="text-sm text-gray-300">Header (tidak bisa diposting)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_control" x-model="form.is_control" value="1" class="w-4 h-4 rounded bg-gray-900 border-gray-600 text-indigo-500 focus:ring-indigo-500">
                            <span class="text-sm text-gray-300">Control Account</span>
                        </label>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg active:scale-95 flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> <span x-text="editMode ? 'Simpan Perubahan' : 'Buat Akun'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

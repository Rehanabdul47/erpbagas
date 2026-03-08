@extends('layouts.app')

@section('title', 'Sales & CRM')
@section('breadcrumb', 'Sales & CRM')

@section('content')
<div x-data="salesData()">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    {{-- Header & Filter --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i data-lucide="trending-up" class="w-6 h-6 text-indigo-400"></i> Sales & CRM
        </h2>
        <form id="branch-filter-form" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <input type="hidden" name="tab" x-model="activeTab" />
            <select name="branch_id" onchange="document.getElementById('branch-filter-form').submit()"
                    class="bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all shadow-sm">
                <option value="all">Semua Cabang / Toko</option>
                @foreach($branches as $branch)
                <option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
                    {{ $branch->name }} {{ $branch->type === 'main' ? '(Kantor Pusat)' : '' }}
                </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-6">
        <div class="bg-gray-800 border-b-4 border-indigo-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="trending-up" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1 truncate" title="Rp {{ number_format($stats['totalRevenue'], 0, ',', '.') }}">Rp {{ number_format($stats['totalRevenue'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Pendapatan (Bulan Ini)</div>
        </div>
        <div class="bg-gray-800 border-b-4 border-emerald-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="users" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $stats['totalCustomers'] }}</div>
            <div class="text-gray-400 text-sm">Total Customer</div>
        </div>
        <div class="bg-gray-800 border-b-4 border-amber-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4"><i data-lucide="shopping-bag" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['totalSales'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Total Transaksi</div>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="flex overflow-x-auto border-b border-gray-700/50 hide-scrollbar bg-gray-900/20">
            <button @click="activeTab = 'customers'" :class="activeTab === 'customers' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4"></i> Customer (CRM)
            </button>
            <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4"></i> Riwayat Penjualan
            </button>
        </div>

        {{-- Tab: Customer List --}}
        <div x-show="activeTab === 'customers'" class="p-5">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
                <div class="relative w-full sm:max-w-xs">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="text" x-model="searchCustomer" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500" placeholder="Cari nama/phone...">
                </div>
                <button @click="openModalAddCustomer" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20 w-full sm:w-auto">
                    <i data-lucide="plus" class="w-4 h-4"></i> Tambah Customer
                </button>
            </div>

            <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr><th class="px-4 py-3 font-medium">Nama Customer</th><th class="px-4 py-3 font-medium">Phone</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Total Order</th><th class="px-4 py-3 font-medium text-right">Aksi</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($customers as $c)
                        <tr x-show="matchCustomer('{{ strtolower($c->name) }}', '{{ $c->phone }}')" @click="viewCustomerDetail({{ json_encode($c) }})" class="hover:bg-indigo-500/10 transition-colors cursor-pointer group">
                            <td class="px-4 py-3 font-bold text-white">{{ $c->name }}</td>
                            <td class="px-4 py-3 text-gray-300 font-mono text-xs">{{ $c->phone ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border {{ $c->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($c->status === 'leads' ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-gray-500/10 text-gray-400 border-gray-500/20') }}">
                                    {{ ucfirst($c->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-white">{{ $c->sales_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button @click.stop="openEditCustomerModal({{ json_encode($c) }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Edit"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                                    <form method="POST" action="{{ route('sales.customers.destroy', $c) }}" 
                                        @submit.prevent="$dispatch('global-confirm', { 
                                            title: 'HAPUS CUSTOMER', 
                                            message: 'Apakah Anda yakin ingin menghapus customer {{ addslashes($c->name) }}? Semua riwayat transaksi akan sulit dilacak.',
                                            actionUrl: '{{ route('sales.customers.destroy', $c) }}',
                                            actionMethod: 'DELETE',
                                            confirmText: 'HAPUS'
                                        })" 
                                        @click.stop class="m-0">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-10 text-gray-500">Belum ada data customer</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab: Sales History --}}
        <div x-show="activeTab === 'history'" x-cloak style="display: none;" class="p-5">
            <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr><th class="px-4 py-3 font-medium">Invoice</th><th class="px-4 py-3 font-medium">Customer</th><th class="px-4 py-3 font-medium">Cabang / Toko</th><th class="px-4 py-3 font-medium">Tanggal</th><th class="px-4 py-3 font-medium">Total Amount</th><th class="px-4 py-3 font-medium">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($sales as $s)
                        <tr @click="viewSaleDetail({{ json_encode($s->load('customer', 'branch')) }})" class="hover:bg-indigo-500/10 transition-colors cursor-pointer group">
                            <td class="px-4 py-3 font-bold text-indigo-400 font-mono text-xs">#{{ $s->invoice_no }}</td>
                            <td class="px-4 py-3 font-bold text-white">{{ $s->customer->name }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $s->branch ? $s->branch->name : 'Kantor Pusat' }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ \Carbon\Carbon::parse($s->sale_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-bold text-white">Rp {{ number_format($s->total_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border {{ $s->status === 'completed' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-gray-500/10 text-gray-400 border-gray-500/20' }}">
                                    {{ ucfirst($s->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center py-10 text-gray-500">Belum ada riwayat penjualan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal: Add/Edit Customer --}}
    <div x-show="modals.customer" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.customer = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2" x-text="custForm.title"></h3>
                <button @click="modals.customer = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="custForm.action" method="POST">
                @csrf
                <input type="hidden" name="_method" value="PUT" x-show="custForm.method === 'PUT'" :disabled="custForm.method !== 'PUT'">
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama Customer <span class="text-red-500">*</span></label><input type="text" name="name" x-model="custForm.name" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</label><input type="email" name="email" x-model="custForm.email" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Phone</label><input type="text" name="phone" x-model="custForm.phone" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Status <span class="text-red-500">*</span></label><select name="status" x-model="custForm.status" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"><option value="active">Active</option><option value="leads">Leads</option><option value="inactive">Inactive</option></select></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Alamat</label><textarea name="address" x-model="custForm.address" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" rows="3"></textarea></div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="modals.customer = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Detail View --}}
    <div x-show="modals.detail" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.detail = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2" x-html="detail.title"></h3>
                <button @click="modals.detail = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-5 overflow-y-auto max-h-[70vh]" x-html="detail.content">
            </div>
            <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end">
                <button @click="modals.detail = false" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || 'customers';

    Alpine.data('salesData', () => ({
        activeTab: initialTab,
        searchCustomer: '',
        modals: {
            customer: false,
            detail: false
        },
        custForm: { action: '', method: '', title: '', id: '', name: '', email: '', phone: '', status: 'active', address: '' },
        detail: { title: '', content: '' },

        matchCustomer(name, phone) {
            if (this.searchCustomer === '') return true;
            const search = this.searchCustomer.toLowerCase();
            return name.includes(search) || phone.includes(search);
        },

        openModalAddCustomer() {
            this.custForm = { action: '{{ route("sales.customers.store") }}', method: 'POST', title: '👥 Tambah Customer', id: '', name: '', email: '', phone: '', status: 'active', address: '' };
            this.modals.customer = true;
        },

        openEditCustomerModal(c) {
            this.custForm = { action: `/sales/customers/${c.id}`, method: 'PUT', title: '👥 Edit Customer', id: c.id, name: c.name, email: c.email || '', phone: c.phone || '', status: c.status, address: c.address || '' };
            this.modals.customer = true;
        },

        viewCustomerDetail(c) {
            this.detail.title = '👥 Detail Customer';
            const initials = c.name.substring(0, 1).toUpperCase();
            
            this.detail.content = `
                <div class="text-center mb-6">
                    <div class="w-20 h-20 rounded-full bg-indigo-500 text-white flex items-center justify-center text-3xl font-bold mx-auto mb-3 shadow-lg shadow-indigo-500/30">${initials}</div>
                    <h4 class="text-xl font-bold text-white mb-1">${c.name}</h4>
                    <div class="text-gray-400 text-sm font-medium">${c.email || 'Tanpa Email'}</div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Telepon / WA</div>
                        <div class="text-gray-300 text-sm font-mono">${c.phone || '-'}</div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</div>
                        <div class="mt-1"><span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border ${c.status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : (c.status === 'leads' ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-gray-500/10 text-gray-400 border-gray-500/20')}">${c.status.toUpperCase()}</span></div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-4 rounded-xl sm:col-span-2">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Alamat</div>
                        <div class="text-gray-300 text-sm italic">${c.address || '-'}</div>
                    </div>
                </div>
            `;
            this.modals.detail = true;
        },

        viewSaleDetail(s) {
            this.detail.title = '📜 Detail Transaksi';
            const amount = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(s.total_amount);
            const date = new Date(s.sale_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            const branch = s.branch ? s.branch.name : 'Kantor Pusat';

            this.detail.content = `
                <div class="flex justify-between items-start mb-5 pb-5 border-b border-dashed border-gray-700/80">
                    <div>
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Nomor Invoice</div>
                        <div class="text-xl font-bold text-indigo-400 font-mono">#${s.invoice_no}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal</div>
                        <div class="text-sm font-bold text-white">${date}</div>
                    </div>
                </div>
                
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Customer</div>
                        <div class="text-gray-300 text-sm font-bold">${s.customer.name}</div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Cabang / Toko</div>
                        <div class="text-gray-300 text-sm font-bold">${branch}</div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</div>
                        <div class="mt-1"><span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border ${s.status === 'completed' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-gray-500/10 text-gray-400 border-gray-500/20'}">${s.status.toUpperCase()}</span></div>
                    </div>
                </div>

                <div class="bg-indigo-500/10 border border-indigo-500/30 p-5 rounded-xl text-center shadow-inner">
                    <div class="text-xs text-indigo-400/80 font-bold uppercase tracking-wider mb-2">Total Pembayaran</div>
                    <div class="text-3xl font-black text-indigo-400 font-mono tracking-tight">${amount}</div>
                </div>
                
                <div class="mt-5 text-xs text-gray-500 italic flex items-start gap-2 bg-gray-900/30 p-3 rounded-lg border border-gray-800">
                    <i data-lucide="info" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    Catatan: ${s.notes || 'Tidak ada catatan untuk transaksi ini.'}
                </div>
            `;
            this.modals.detail = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        }
    }));
});
</script>
@endpush

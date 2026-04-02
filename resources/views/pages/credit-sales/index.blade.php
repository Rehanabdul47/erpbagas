@extends('layouts.app')

@section('title', 'Penjualan Tempo')
@section('breadcrumb', 'Penjualan Tempo')

@section('content')
<div x-data="creditSalesApp()">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3 animate-[fadeIn_0.3s_ease]">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 mb-6 text-red-400 flex items-center gap-3 animate-[fadeIn_0.3s_ease]">
        <i data-lucide="alert-circle" class="w-5 h-5 shrink-0"></i>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    {{-- Header & Filter --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i data-lucide="clock" class="w-6 h-6 text-indigo-400"></i> Penjualan Tempo
        </h2>
        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <form id="branch-filter-form" class="flex items-center gap-3">
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
            <button @click="openCreateModal()" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20">
                <i data-lucide="plus" class="w-4 h-4"></i> Buat Penjualan Tempo
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
        <div class="bg-gray-800 border-b-4 border-amber-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4"><i data-lucide="alert-triangle" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-white mb-1 truncate" title="Rp {{ number_format($stats['totalOutstanding'], 0, ',', '.') }}">Rp {{ number_format($stats['totalOutstanding'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Total Piutang Outstanding</div>
        </div>
        <div class="bg-gray-800 border-b-4 border-red-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-400 flex items-center justify-center mb-4"><i data-lucide="alarm-clock" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-white mb-1 truncate" title="Rp {{ number_format($stats['totalOverdue'], 0, ',', '.') }}">Rp {{ number_format($stats['totalOverdue'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Piutang Jatuh Tempo</div>
        </div>
        <div class="bg-gray-800 border-b-4 border-indigo-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="trending-up" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-white mb-1 truncate" title="Rp {{ number_format($stats['totalThisMonth'], 0, ',', '.') }}">Rp {{ number_format($stats['totalThisMonth'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Penjualan Tempo Bulan Ini</div>
        </div>
        <div class="bg-gray-800 border-b-4 border-emerald-500 border-x border-t border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="file-text" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-white mb-1">{{ number_format($stats['totalCreditSales']) }}</div>
            <div class="text-gray-400 text-sm">Total Transaksi Tempo</div>
        </div>
    </div>

    {{-- Tabs Container --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
        <div class="flex overflow-x-auto border-b border-gray-700/50 hide-scrollbar bg-gray-900/20">
            <button @click="activeTab = 'sales'" :class="activeTab === 'sales' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4"></i> Penjualan Tempo
            </button>
            <button @click="activeTab = 'payments'" :class="activeTab === 'payments' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
                <i data-lucide="banknote" class="w-4 h-4"></i> Riwayat Pembayaran
            </button>
            <button @click="activeTab = 'summary'" :class="activeTab === 'summary' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors flex items-center gap-2">
                <i data-lucide="users" class="w-4 h-4"></i> Ringkasan Customer
            </button>
        </div>

        {{-- Tab: Penjualan Tempo --}}
        <div x-show="activeTab === 'sales'" class="p-5">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
                <div class="relative w-full sm:max-w-xs">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                    <input type="text" x-model="searchSales" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500" placeholder="Cari invoice / customer...">
                </div>
                <div class="flex items-center gap-2">
                    <select x-model="filterStatus" class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        <option value="all">Semua Status</option>
                        <option value="unpaid">Belum Dibayar</option>
                        <option value="partial">Sebagian</option>
                        <option value="overdue">Jatuh Tempo</option>
                        <option value="paid">Lunas</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Invoice</th>
                            <th class="px-4 py-3 font-medium">Customer</th>
                            <th class="px-4 py-3 font-medium">Tgl Jual</th>
                            <th class="px-4 py-3 font-medium">Jatuh Tempo</th>
                            <th class="px-4 py-3 font-medium">Total</th>
                            <th class="px-4 py-3 font-medium">Terbayar</th>
                            <th class="px-4 py-3 font-medium">Sisa</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($creditSales as $cs)
                        @php
                            $paidPercent = $cs->total_amount > 0 ? round(($cs->paid_amount / $cs->total_amount) * 100) : 0;
                            $paymentCount = $cs->payments->count();
                        @endphp
                        <tr x-show="matchSale('{{ strtolower($cs->invoice_no) }}', '{{ strtolower($cs->customer->name) }}', '{{ $cs->status }}', {{ $cs->is_overdue ? 'true' : 'false' }})"
                            @click="viewDetail({{ $cs->id }})" class="hover:bg-indigo-500/10 transition-colors cursor-pointer group">
                            <td class="px-4 py-3 font-bold text-indigo-400 font-mono text-xs">#{{ $cs->invoice_no }}</td>
                            <td class="px-4 py-3 font-bold text-white">{{ $cs->customer->name }}</td>
                            <td class="px-4 py-3 text-gray-300">{{ $cs->sale_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="{{ $cs->is_overdue && $cs->status !== 'paid' ? 'text-red-400 font-bold' : 'text-gray-300' }}">
                                    {{ $cs->due_date->format('d M Y') }}
                                    @if($cs->is_overdue && $cs->status !== 'paid')
                                    <i data-lucide="alert-triangle" class="w-3 h-3 inline-block ml-1"></i>
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-white">Rp {{ number_format($cs->total_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <span class="text-emerald-400 font-medium">Rp {{ number_format($cs->paid_amount, 0, ',', '.') }}</span>
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500 {{ $paidPercent >= 100 ? 'bg-emerald-500' : ($paidPercent > 0 ? 'bg-blue-500' : 'bg-gray-600') }}" style="width: {{ $paidPercent }}%"></div>
                                        </div>
                                        <span class="text-[10px] font-bold {{ $paidPercent >= 100 ? 'text-emerald-400' : 'text-gray-500' }}">{{ $paidPercent }}%</span>
                                    </div>
                                    @if($paymentCount > 0)
                                    <span class="text-[10px] text-gray-500">{{ $paymentCount }}x cicilan</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 font-bold text-amber-400">Rp {{ number_format($cs->remaining_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $color = $cs->status_color;
                                    $label = $cs->status_label;
                                @endphp
                                <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border bg-{{ $color }}-500/10 text-{{ $color }}-400 border-{{ $color }}-500/20">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @if($cs->status !== 'paid')
                                    <button @click.stop="openPaymentModal({{ json_encode($cs->load('customer')) }})" class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors" title="Bayar Cicilan">
                                        <i data-lucide="banknote" class="w-4 h-4"></i>
                                    </button>
                                    @endif
                                    <button @click.stop="viewDetail({{ $cs->id }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Detail">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="9" class="text-center py-10 text-gray-500">Belum ada data penjualan tempo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab: Riwayat Pembayaran --}}
        <div x-show="activeTab === 'payments'" x-cloak style="display: none;" class="p-5">
            <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Tanggal</th>
                            <th class="px-4 py-3 font-medium">Invoice</th>
                            <th class="px-4 py-3 font-medium">Customer</th>
                            <th class="px-4 py-3 font-medium">Jumlah Bayar</th>
                            <th class="px-4 py-3 font-medium">Metode</th>
                            <th class="px-4 py-3 font-medium">Referensi</th>
                            <th class="px-4 py-3 font-medium">Oleh</th>
                            <th class="px-4 py-3 font-medium text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($payments as $p)
                        <tr class="hover:bg-indigo-500/10 transition-colors">
                            <td class="px-4 py-3 text-gray-300">{{ $p->payment_date->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-bold text-indigo-400 font-mono text-xs">#{{ $p->creditSale->invoice_no }}</td>
                            <td class="px-4 py-3 font-bold text-white">{{ $p->creditSale->customer->name }}</td>
                            <td class="px-4 py-3 font-bold text-emerald-400">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border {{ $p->payment_method === 'cash' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20' }}">
                                    {{ $p->payment_method === 'cash' ? 'Tunai' : 'Transfer' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ $p->reference ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $p->user->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button onclick="window.open('{{ route('credit-sales.payments.receipt', [$p->credit_sale_id, $p->id]) }}?print=true', '_blank', 'width=800,height=600')" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Cetak Nota Cicilan">
                                    <i data-lucide="printer" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center py-10 text-gray-500">Belum ada riwayat pembayaran</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tab: Ringkasan Customer --}}
        <div x-show="activeTab === 'summary'" x-cloak style="display: none;" class="p-5">
            <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 font-medium">Customer</th>
                            <th class="px-4 py-3 font-medium">Jumlah Transaksi</th>
                            <th class="px-4 py-3 font-medium">Total Kredit</th>
                            <th class="px-4 py-3 font-medium">Piutang Outstanding</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @forelse($customerSummary as $csum)
                        <tr class="hover:bg-indigo-500/10 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs font-bold border border-indigo-500/20">
                                        {{ strtoupper(substr($csum->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white">{{ $csum->name }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $csum->phone ?? $csum->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-bold text-white">{{ $csum->credit_sales_count }}</td>
                            <td class="px-4 py-3 text-gray-300">Rp {{ number_format($csum->total_credit ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 font-bold {{ ($csum->total_outstanding ?? 0) > 0 ? 'text-amber-400' : 'text-emerald-400' }}">
                                Rp {{ number_format($csum->total_outstanding ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if(($csum->total_outstanding ?? 0) > 0)
                                <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border bg-amber-500/10 text-amber-400 border-amber-500/20">Ada Piutang</span>
                                @else
                                <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border bg-emerald-500/10 text-emerald-400 border-emerald-500/20">Lunas</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-10 text-gray-500">Belum ada data ringkasan customer</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal: Buat Penjualan Tempo --}}
    <div x-show="modals.create" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.create = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between shrink-0">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5 text-indigo-400"></i> Buat Penjualan Tempo Baru
                </h3>
                <button @click="modals.create = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('credit-sales.store') }}" method="POST" @submit="validateForm($event)">
                @csrf
                <div class="p-5 flex flex-col gap-4 overflow-y-auto">
                    {{-- Customer Selection --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Customer <span class="text-red-500">*</span></label>
                        <select name="customer_id" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">Pilih Customer</option>
                            @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} {{ $c->phone ? '('.$c->phone.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Payment Terms --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lama Tempo (Hari) <span class="text-red-500">*</span></label>
                            <input type="number" name="payment_term_days" x-model="createForm.termDays" min="1" max="365" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Perkiraan Jatuh Tempo</label>
                            <div class="w-full bg-gray-900/50 border border-gray-700 text-amber-400 rounded-lg px-4 py-2 text-sm font-bold flex items-center gap-2">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                <span x-text="getDueDate()"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Product Items --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Produk <span class="text-red-500">*</span></label>
                        <template x-for="(item, index) in createForm.items" :key="index">
                            <div class="flex items-start gap-2 mb-2 bg-gray-900/50 border border-gray-700/50 p-3 rounded-lg">
                                <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    <select :name="'items['+index+'][product_id]'" x-model="item.product_id" @change="updateItemPrice(index)" required class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all col-span-1">
                                        <option value="">Pilih Produk</option>
                                        @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->price }}" data-stock="{{ $p->stock }}">{{ $p->name }} (Stok: {{ $p->stock }})</option>
                                        @endforeach
                                    </select>
                                    <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" @input="updateItemSubtotal(index)" min="1" placeholder="Qty" required class="bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                    <div class="bg-gray-900/50 border border-gray-700 rounded-lg px-3 py-2 text-sm text-white font-mono flex items-center">
                                        Rp <span x-text="formatNumber(item.subtotal)" class="ml-1"></span>
                                    </div>
                                </div>
                                <button type="button" @click="removeItem(index)" x-show="createForm.items.length > 1" class="p-2 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors mt-0.5 shrink-0">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="addItem()" class="flex items-center gap-2 text-indigo-400 hover:text-indigo-300 text-sm font-medium transition-colors mt-1">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Produk
                        </button>
                    </div>

                    {{-- Grand Total --}}
                    <div class="bg-indigo-500/10 border border-indigo-500/30 p-4 rounded-xl text-center">
                        <div class="text-xs text-indigo-400/80 font-bold uppercase tracking-wider mb-2">Total Penjualan</div>
                        <div class="text-3xl font-black text-indigo-400 font-mono tracking-tight">Rp <span x-text="formatNumber(grandTotal())"></span></div>
                    </div>

                    {{-- Notes --}}
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Catatan</label>
                        <textarea name="notes" rows="2" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500" placeholder="Catatan opsional..."></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3 shrink-0">
                    <button type="button" @click="modals.create = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20">
                        <i data-lucide="check" class="w-4 h-4 inline-block mr-1"></i> Simpan Penjualan Tempo
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Detail Penjualan Tempo --}}
    <div x-show="modals.detail" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.detail = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between shrink-0">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-indigo-400"></i> Detail Penjualan Tempo
                </h3>
                <button @click="modals.detail = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-5 overflow-y-auto" x-show="detailData">
                {{-- Header Info --}}
                <div class="flex justify-between items-start mb-5 pb-5 border-b border-dashed border-gray-700/80">
                    <div>
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Nomor Invoice</div>
                        <div class="text-xl font-bold text-indigo-400 font-mono" x-text="'#' + (detailData?.invoice_no || '')"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Status</div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold" 
                              :class="{
                                  'bg-amber-500/10 text-amber-400 border border-amber-500/20': detailData?.status === 'unpaid',
                                  'bg-blue-500/10 text-blue-400 border border-blue-500/20': detailData?.status === 'partial',
                                  'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': detailData?.status === 'paid',
                                  'bg-red-500/10 text-red-400 border border-red-500/20': detailData?.status === 'overdue'
                              }" x-text="getStatusLabel(detailData?.status)"></span>
                    </div>
                </div>

                {{-- Info Grid --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Customer</div>
                        <div class="text-gray-300 text-sm font-bold" x-text="detailData?.customer?.name || '-'"></div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tanggal Jual</div>
                        <div class="text-gray-300 text-sm" x-text="formatDate(detailData?.sale_date)"></div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Jatuh Tempo</div>
                        <div class="text-sm font-bold" :class="isOverdue(detailData) ? 'text-red-400' : 'text-gray-300'" x-text="formatDate(detailData?.due_date)"></div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tempo</div>
                        <div class="text-gray-300 text-sm font-bold" x-text="(detailData?.payment_term_days || 0) + ' Hari'"></div>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="mb-5">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Detail Produk</div>
                    <div class="border border-gray-700/50 rounded-lg overflow-hidden">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider">
                                <tr><th class="px-4 py-2 text-left">Produk</th><th class="px-4 py-2 text-center">Qty</th><th class="px-4 py-2 text-right">Harga</th><th class="px-4 py-2 text-right">Subtotal</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700/50">
                                <template x-for="item in (detailData?.items || [])" :key="item.id">
                                    <tr>
                                        <td class="px-4 py-2 text-white font-medium" x-text="item.product?.name || '-'"></td>
                                        <td class="px-4 py-2 text-center text-gray-300" x-text="item.quantity"></td>
                                        <td class="px-4 py-2 text-right text-gray-300 font-mono text-xs" x-text="'Rp ' + formatNumber(item.unit_price)"></td>
                                        <td class="px-4 py-2 text-right text-white font-bold font-mono text-xs" x-text="'Rp ' + formatNumber(item.subtotal)"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Payment Progress Bar --}}
                <div class="mb-5">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Progress Pembayaran</span>
                        <span class="text-xs font-bold" :class="getPaymentPercent(detailData) >= 100 ? 'text-emerald-400' : 'text-blue-400'" x-text="getPaymentPercent(detailData) + '%'"></span>
                    </div>
                    <div class="h-3 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700 ease-out" 
                             :class="getPaymentPercent(detailData) >= 100 ? 'bg-gradient-to-r from-emerald-500 to-emerald-400' : 'bg-gradient-to-r from-blue-600 to-blue-400'"
                             :style="'width: ' + getPaymentPercent(detailData) + '%'"></div>
                    </div>
                    <div class="flex justify-between items-center mt-1">
                        <span class="text-[10px] text-gray-500" x-text="(detailData?.payments?.length || 0) + ' kali pembayaran'"></span>
                        <span class="text-[10px] text-gray-500" x-text="'Target: Rp ' + formatNumber(detailData?.total_amount || 0)"></span>
                    </div>
                </div>

                {{-- Payment Summary --}}
                <div class="grid grid-cols-3 gap-3 mb-5">
                    <div class="bg-indigo-500/10 border border-indigo-500/30 p-4 rounded-xl text-center">
                        <div class="text-[10px] text-indigo-400/80 font-bold uppercase tracking-wider mb-1">Total</div>
                        <div class="text-lg font-black text-indigo-400 font-mono" x-text="'Rp ' + formatNumber(detailData?.total_amount || 0)"></div>
                    </div>
                    <div class="bg-emerald-500/10 border border-emerald-500/30 p-4 rounded-xl text-center">
                        <div class="text-[10px] text-emerald-400/80 font-bold uppercase tracking-wider mb-1">Terbayar</div>
                        <div class="text-lg font-black text-emerald-400 font-mono" x-text="'Rp ' + formatNumber(detailData?.paid_amount || 0)"></div>
                    </div>
                    <div class="bg-amber-500/10 border border-amber-500/30 p-4 rounded-xl text-center">
                        <div class="text-[10px] text-amber-400/80 font-bold uppercase tracking-wider mb-1">Sisa</div>
                        <div class="text-lg font-black text-amber-400 font-mono" x-text="'Rp ' + formatNumber((detailData?.total_amount || 0) - (detailData?.paid_amount || 0))"></div>
                    </div>
                </div>

                {{-- Payment History --}}
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-2">
                            Riwayat Cicilan
                            <span x-show="detailData?.payments?.length > 0" class="px-1.5 py-0.5 bg-indigo-500 text-white text-[9px] font-bold rounded" x-text="(detailData?.payments?.length || 0) + 'x'"></span>
                        </div>
                    </div>
                    <template x-if="detailData?.payments?.length > 0">
                        <div class="border border-gray-700/50 rounded-lg overflow-hidden">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider">
                                    <tr><th class="px-4 py-2 text-left">#</th><th class="px-4 py-2 text-left">Tanggal</th><th class="px-4 py-2 text-right">Jumlah</th><th class="px-4 py-2 text-left">Metode</th><th class="px-4 py-2 text-left">Ref</th><th class="px-4 py-2 text-left">Catatan</th><th class="px-4 py-2 text-right">Aksi</th></tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700/50">
                                    <template x-for="(pay, idx) in (detailData?.payments || [])" :key="pay.id">
                                        <tr>
                                            <td class="px-4 py-2 text-gray-500 text-xs font-bold" x-text="'Cicilan #' + (idx + 1)"></td>
                                            <td class="px-4 py-2 text-gray-300 text-xs" x-text="formatDate(pay.payment_date)"></td>
                                            <td class="px-4 py-2 text-right text-emerald-400 font-bold font-mono text-xs" x-text="'Rp ' + formatNumber(pay.amount)"></td>
                                            <td class="px-4 py-2">
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold" :class="pay.payment_method === 'cash' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-blue-500/10 text-blue-400'" x-text="pay.payment_method === 'cash' ? 'Tunai' : 'Transfer'"></span>
                                            </td>
                                            <td class="px-4 py-2 text-gray-400 text-xs font-mono" x-text="pay.reference || '-'"></td>
                                            <td class="px-4 py-2 text-gray-500 text-xs italic" x-text="pay.notes || '-'"></td>
                                            <td class="px-4 py-2 text-right">
                                                <button @click="window.open(`/credit-sales/${detailData.id}/payments/${pay.id}/receipt?print=true`, '_blank', 'width=800,height=600')" class="p-1 rounded-md text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Cetak Nota">
                                                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                    <template x-if="!detailData?.payments?.length">
                        <div class="border border-gray-700/50 rounded-lg p-6 text-center">
                            <i data-lucide="inbox" class="w-8 h-8 text-gray-600 mx-auto mb-2"></i>
                            <p class="text-gray-500 text-sm">Belum ada pembayaran cicilan</p>
                        </div>
                    </template>
                </div>

                {{-- Notes --}}
                <div x-show="detailData?.notes" class="mt-5 text-xs text-gray-500 italic flex items-start gap-2 bg-gray-900/30 p-3 rounded-lg border border-gray-800">
                    <i data-lucide="info" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    <span x-text="detailData?.notes"></span>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-between shrink-0">
                <button x-show="detailData?.status !== 'paid'" @click="modals.detail = false; openPaymentModal(detailData)" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-emerald-600/20 flex items-center gap-2">
                    <i data-lucide="banknote" class="w-4 h-4"></i> Input Pembayaran
                </button>
                <button @click="modals.detail = false" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Modal: Input Pembayaran Cicilan --}}
    <div x-show="modals.payment" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.payment = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between shrink-0">
                <h3 class="font-bold text-white flex items-center gap-2">
                    <i data-lucide="banknote" class="w-5 h-5 text-emerald-400"></i> Pembayaran Cicilan
                    <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 text-[10px] font-bold rounded-full border border-blue-500/20" x-text="'Cicilan #' + (paymentForm.installmentNumber)"></span>
                </h3>
                <button @click="modals.payment = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="paymentForm.action" method="POST" class="flex flex-col overflow-hidden min-h-0">
                @csrf
                <div class="p-5 flex flex-col gap-4 overflow-y-auto min-h-0">
                    {{-- Invoice Info Card --}}
                    <div class="bg-gray-900/50 border border-gray-700/50 p-4 rounded-xl">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Invoice</span>
                            <span class="text-indigo-400 font-mono font-bold text-sm" x-text="'#' + paymentForm.invoiceNo"></span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</span>
                            <span class="text-white font-bold text-sm" x-text="paymentForm.customerName"></span>
                        </div>
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Tagihan</span>
                            <span class="text-gray-300 font-bold text-sm font-mono" x-text="'Rp ' + formatNumber(paymentForm.totalAmount)"></span>
                        </div>
                        {{-- Progress Bar --}}
                        <div class="mb-2">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[10px] font-bold text-gray-500">PROGRESS PEMBAYARAN</span>
                                <span class="text-[10px] font-bold" :class="paymentForm.paidPercent >= 100 ? 'text-emerald-400' : 'text-blue-400'" x-text="paymentForm.paidPercent + '%'"></span>
                            </div>
                            <div class="h-2.5 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-500" 
                                     :class="paymentForm.paidPercent >= 100 ? 'bg-gradient-to-r from-emerald-500 to-emerald-400' : 'bg-gradient-to-r from-blue-600 to-blue-400'"
                                     :style="'width: ' + paymentForm.paidPercent + '%'"></div>
                            </div>
                            <div class="flex justify-between mt-1">
                                <span class="text-[10px] text-emerald-400" x-text="'Terbayar: Rp ' + formatNumber(paymentForm.paidAmount)"></span>
                                <span class="text-[10px] text-amber-400 font-bold" x-text="'Sisa: Rp ' + formatNumber(paymentForm.remaining)"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Amount Input with Quick Actions --}}
                    <div class="space-y-2">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Jumlah Bayar <span class="text-red-500">*</span></label>
                        <div class="flex gap-2 flex-wrap">
                            <button type="button" @click="paymentForm.payAmount = paymentForm.remaining" class="px-3 py-1.5 bg-emerald-600/20 border border-emerald-500/30 text-emerald-400 rounded-lg text-xs font-bold hover:bg-emerald-600/30 transition-colors">
                                💰 Bayar Penuh
                            </button>
                            <button type="button" @click="paymentForm.payAmount = Math.ceil(paymentForm.remaining / 2)" class="px-3 py-1.5 bg-blue-600/20 border border-blue-500/30 text-blue-400 rounded-lg text-xs font-bold hover:bg-blue-600/30 transition-colors">
                                50%
                            </button>
                            <button type="button" @click="paymentForm.payAmount = Math.ceil(paymentForm.remaining / 3)" class="px-3 py-1.5 bg-indigo-600/20 border border-indigo-500/30 text-indigo-400 rounded-lg text-xs font-bold hover:bg-indigo-600/30 transition-colors">
                                1/3
                            </button>
                            <button type="button" @click="paymentForm.payAmount = Math.ceil(paymentForm.remaining / 4)" class="px-3 py-1.5 bg-purple-600/20 border border-purple-500/30 text-purple-400 rounded-lg text-xs font-bold hover:bg-purple-600/30 transition-colors">
                                1/4
                            </button>
                        </div>
                        <input type="number" name="amount" x-model="paymentForm.payAmount" :max="paymentForm.remaining" min="1" step="1" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-3 text-lg font-bold font-mono focus:ring-2 focus:ring-emerald-500 outline-none transition-all" placeholder="Masukkan nominal bebas">
                        <p class="text-[10px] text-gray-500 flex items-center gap-1">
                            <i data-lucide="info" class="w-3 h-3"></i>
                            Nominal fleksibel — bayar berapapun mulai dari Rp 1 s/d Rp <span x-text="formatNumber(paymentForm.remaining)"></span>
                        </p>
                    </div>

                    {{-- Preview: After Payment --}}
                    <div x-show="paymentForm.payAmount > 0" class="bg-gray-900/80 border border-gray-700/50 p-4 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-3">Preview Setelah Pembayaran Ini</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <div class="text-[10px] text-gray-500 mb-0.5">Sisa Piutang</div>
                                <div class="text-sm font-bold font-mono" :class="getNewRemaining() <= 0 ? 'text-emerald-400' : 'text-amber-400'" x-text="'Rp ' + formatNumber(Math.max(0, getNewRemaining()))"></div>
                            </div>
                            <div>
                                <div class="text-[10px] text-gray-500 mb-0.5">Status Baru</div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="{
                                    'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': getNewRemaining() <= 0,
                                    'bg-blue-500/10 text-blue-400 border border-blue-500/20': getNewRemaining() > 0
                                }" x-text="getNewRemaining() <= 0 ? '✅ LUNAS' : '🔄 Sebagian Terbayar'"></span>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[10px] text-gray-500">Progress setelah bayar</span>
                                <span class="text-[10px] font-bold text-blue-400" x-text="getNewPercent() + '%'"></span>
                            </div>
                            <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-300" 
                                     :class="getNewPercent() >= 100 ? 'bg-gradient-to-r from-emerald-500 to-emerald-400' : 'bg-gradient-to-r from-blue-600 to-blue-400'"
                                     :style="'width: ' + Math.min(100, getNewPercent()) + '%'"></div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Metode <span class="text-red-500">*</span></label>
                            <select name="payment_method" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                <option value="cash">Tunai</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal Bayar <span class="text-red-500">*</span></label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">No. Referensi / Bukti Transfer</label>
                        <input type="text" name="reference" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Opsional">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Catatan Cicilan</label>
                        <textarea name="notes" rows="2" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-500" placeholder="Contoh: Cicilan ke-2 dari 4x pembayaran..."></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3 shrink-0">
                    <button type="button" @click="modals.payment = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-emerald-600/20">
                        <i data-lucide="check" class="w-4 h-4 inline-block mr-1"></i> Bayar Cicilan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || 'sales';

    // Product prices lookup
    const productPrices = {
        @foreach($products as $p)
        {{ $p->id }}: {{ $p->price }},
        @endforeach
    };

    Alpine.data('creditSalesApp', () => ({
        activeTab: initialTab,
        searchSales: '',
        filterStatus: 'all',
        modals: {
            create: false,
            detail: false,
            payment: false,
        },
        createForm: {
            termDays: 30,
            items: [{ product_id: '', quantity: 1, price: 0, subtotal: 0 }],
        },
        detailData: null,
        detailLoading: false,
        paymentForm: {
            action: '',
            invoiceNo: '',
            customerName: '',
            totalAmount: 0,
            paidAmount: 0,
            remaining: 0,
            paidPercent: 0,
            payAmount: '',
            installmentNumber: 1,
        },

        // Search & Filter
        matchSale(invoice, customer, status, isOverdue) {
            let searchMatch = true;
            if (this.searchSales !== '') {
                const search = this.searchSales.toLowerCase();
                searchMatch = invoice.includes(search) || customer.includes(search);
            }
            let statusMatch = true;
            if (this.filterStatus !== 'all') {
                if (this.filterStatus === 'overdue') {
                    statusMatch = isOverdue || status === 'overdue';
                } else {
                    statusMatch = status === this.filterStatus;
                }
            }
            return searchMatch && statusMatch;
        },

        // Create Modal
        openCreateModal() {
            this.createForm = {
                termDays: 30,
                items: [{ product_id: '', quantity: 1, price: 0, subtotal: 0 }],
            };
            this.modals.create = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        addItem() {
            this.createForm.items.push({ product_id: '', quantity: 1, price: 0, subtotal: 0 });
        },

        removeItem(index) {
            this.createForm.items.splice(index, 1);
        },

        updateItemPrice(index) {
            const item = this.createForm.items[index];
            item.price = productPrices[item.product_id] || 0;
            this.updateItemSubtotal(index);
        },

        updateItemSubtotal(index) {
            const item = this.createForm.items[index];
            item.subtotal = (item.price || 0) * (item.quantity || 0);
        },

        grandTotal() {
            return this.createForm.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
        },

        getDueDate() {
            const days = parseInt(this.createForm.termDays) || 30;
            const due = new Date();
            due.setDate(due.getDate() + days);
            return due.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        validateForm(e) {
            const hasItems = this.createForm.items.some(i => i.product_id !== '' && i.quantity > 0);
            if (!hasItems) {
                e.preventDefault();
                alert('Pilih minimal 1 produk!');
            }
        },

        // Detail Modal
        async viewDetail(id) {
            this.detailLoading = true;
            this.modals.detail = true;
            try {
                const res = await fetch(`/credit-sales/${id}`);
                this.detailData = await res.json();
            } catch (err) {
                console.error('Error fetching detail:', err);
            }
            this.detailLoading = false;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        // Payment Modal
        openPaymentModal(cs) {
            const total = parseFloat(cs.total_amount) || 0;
            const paid = parseFloat(cs.paid_amount) || 0;
            const remaining = total - paid;
            const paidPercent = total > 0 ? Math.round((paid / total) * 100) : 0;
            const installmentNumber = (cs.payments?.length || 0) + 1;
            this.paymentForm = {
                action: `/credit-sales/${cs.id}/payments`,
                invoiceNo: cs.invoice_no,
                customerName: cs.customer?.name || cs.customer_name || '-',
                totalAmount: total,
                paidAmount: paid,
                remaining: remaining,
                paidPercent: paidPercent,
                payAmount: '',
                installmentNumber: installmentNumber,
            };
            this.modals.payment = true;
            this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
        },

        getNewRemaining() {
            const pay = parseFloat(this.paymentForm.payAmount) || 0;
            return this.paymentForm.remaining - pay;
        },

        getNewPercent() {
            const pay = parseFloat(this.paymentForm.payAmount) || 0;
            const total = this.paymentForm.totalAmount || 1;
            const newPaid = this.paymentForm.paidAmount + pay;
            return Math.min(100, Math.round((newPaid / total) * 100));
        },

        getPaymentPercent(data) {
            if (!data || !data.total_amount) return 0;
            return Math.min(100, Math.round((parseFloat(data.paid_amount || 0) / parseFloat(data.total_amount)) * 100));
        },

        // Helpers
        formatNumber(n) {
            return new Intl.NumberFormat('id-ID').format(n || 0);
        },

        formatDate(d) {
            if (!d) return '-';
            return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },

        getStatusLabel(status) {
            const labels = { unpaid: 'Belum Dibayar', partial: 'Sebagian', paid: 'Lunas', overdue: 'Jatuh Tempo' };
            return labels[status] || status;
        },

        isOverdue(data) {
            if (!data || data.status === 'paid') return false;
            return new Date() > new Date(data.due_date);
        },
    }));
});
</script>
@endpush

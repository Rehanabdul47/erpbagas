@extends('layouts.app')

@section('title', 'Anggaran')
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Anggaran</span>
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

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0">
                <i data-lucide="piggy-bank" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white m-0">Anggaran & Budget</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kelola anggaran per periode dan lihat realisasi</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('finance.budgets.report') }}" class="flex items-center gap-2 px-4 py-2 bg-amber-500/10 text-amber-400 hover:bg-amber-600 hover:text-white rounded-lg text-sm font-medium transition-colors border border-amber-500/20">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i> Budget vs Actual
            </a>
            <a href="{{ route('finance.budgets.create') }}" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Buat Anggaran
            </a>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">Nama Anggaran</th>
                        <th class="px-5 py-4 font-medium">Periode</th>
                        <th class="px-5 py-4 font-medium">Departemen</th>
                        <th class="px-5 py-4 font-medium text-right">Total Anggaran</th>
                        <th class="px-5 py-4 font-medium text-center">Status</th>
                        <th class="px-5 py-4 font-medium">Dibuat Oleh</th>
                        <th class="px-5 py-4 font-medium text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($budgets as $budget)
                    <tr class="hover:bg-gray-700/20 transition-colors">
                        <td class="px-5 py-4 font-bold text-white">{{ $budget->name }}</td>
                        <td class="px-5 py-4">
                            <div class="flex flex-col">
                                <span class="text-gray-300">{{ $budget->fiscalPeriod->name ?? '-' }}</span>
                                @if($budget->fiscalPeriod)
                                    @php
                                        $pc = ['open'=>'text-emerald-400 bg-emerald-500/10','closed'=>'text-amber-400 bg-amber-500/10','locked'=>'text-red-400 bg-red-500/10'];
                                    @endphp
                                    <span class="text-[9px] font-black uppercase tracking-tighter w-fit px-1 rounded mt-0.5 {{ $pc[$budget->fiscalPeriod->status] ?? 'text-gray-500 bg-gray-500/10' }}">
                                        {{ $budget->fiscalPeriod->status }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-300">{{ $budget->department ?? '-' }}</td>
                        <td class="px-5 py-4 text-right font-bold text-emerald-400">Rp {{ number_format($budget->items->sum('planned_amount'), 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-center">
                            @php $bs = ['draft'=>'bg-gray-500/10 text-gray-400','approved'=>'bg-emerald-500/10 text-emerald-400','active'=>'bg-blue-500/10 text-blue-400','closed'=>'bg-red-500/10 text-red-400']; @endphp
                            <span class="text-[10px] px-2 py-1 rounded-full font-bold {{ $bs[$budget->status] ?? '' }}">{{ strtoupper($budget->status) }}</span>
                        </td>
                        <td class="px-5 py-4 text-gray-400">{{ $budget->creator->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if(auth()->user()->isExecutive())
                                <a href="{{ route('finance.budgets.edit', $budget) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-all" title="Edit Anggaran">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                                <button type="button" 
                                    @click="$dispatch('global-confirm', { 
                                        title: 'Hapus Anggaran', 
                                        message: 'Apakah Anda yakin ingin menghapus anggaran {{ $budget->name }}? Tindakan ini tidak dapat dibatalkan.', 
                                        confirmText: 'Ya, Hapus', 
                                        actionUrl: '{{ route('finance.budgets.destroy', $budget) }}', 
                                        actionMethod: 'DELETE' 
                                    })"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-all" title="Hapus Anggaran">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center py-12 text-gray-500">Belum ada anggaran dibuat</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Detail Jurnal #' . $entry->reference)
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<a href="{{ route('finance.ledger.index') }}" class="text-gray-400 hover:text-white transition-colors">General Ledger</a>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">{{ $entry->reference }}</span>
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
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white m-0">Jurnal #{{ $entry->reference }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $entry->date->format('d F Y') }} · {{ $entry->description }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @php
                $statusColors = ['draft' => 'bg-gray-500/10 text-gray-400 border-gray-500/30', 'posted' => 'bg-blue-500/10 text-blue-400 border-blue-500/30', 'approved' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30', 'void' => 'bg-red-500/10 text-red-400 border-red-500/30'];
            @endphp
            <span class="px-3 py-1.5 rounded-lg text-sm font-bold border {{ $statusColors[$entry->status] ?? '' }}">
                {{ strtoupper($entry->status) }}
            </span>

            @if($entry->isDraft())
            <form method="POST" action="{{ route('finance.ledger.post', $entry) }}" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Post
                </button>
            </form>
            @endif

            @if(!$entry->isVoid())
            <form method="POST" action="{{ route('finance.ledger.void', $entry) }}" class="inline"
                  @submit.prevent="$dispatch('global-confirm', {
                      title: 'VOID JURNAL',
                      message: 'Yakin ingin membatalkan jurnal ini? Jurnal yang sudah divoid tidak dapat dikembalikan.',
                      actionUrl: '{{ route('finance.ledger.void', $entry) }}',
                      actionMethod: 'PATCH',
                      confirmText: 'VOID'
                  })">
                @csrf @method('PATCH')
                <button type="submit" class="px-4 py-2 bg-red-500/10 hover:bg-red-600 text-red-400 hover:text-white rounded-lg text-sm font-medium transition-colors flex items-center gap-2 border border-red-500/30">
                    <i data-lucide="x-circle" class="w-4 h-4"></i> Void
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Journal Items --}}
        <div class="xl:col-span-2">
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-700/50">
                    <h4 class="font-bold text-white flex items-center gap-2"><i data-lucide="table" class="w-4 h-4 text-indigo-400"></i> Detail Item</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                            <tr>
                                <th class="px-5 py-3 font-medium">Akun</th>
                                <th class="px-5 py-3 font-medium text-right">Debit</th>
                                <th class="px-5 py-3 font-medium text-right">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @foreach($entry->items as $item)
                            <tr class="hover:bg-gray-700/20">
                                <td class="px-5 py-4">
                                    <span class="font-mono text-xs text-indigo-400 mr-2">{{ $item->account->code }}</span>
                                    <span class="text-white">{{ $item->account->name }}</span>
                                </td>
                                <td class="px-5 py-4 text-right font-bold {{ $item->debit > 0 ? 'text-blue-400' : 'text-gray-600' }}">
                                    {{ $item->debit > 0 ? 'Rp ' . number_format($item->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-5 py-4 text-right font-bold {{ $item->credit > 0 ? 'text-orange-400' : 'text-gray-600' }}">
                                    {{ $item->credit > 0 ? 'Rp ' . number_format($item->credit, 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-600 bg-gray-900/50">
                            <tr>
                                <td class="px-5 py-4 text-right font-bold text-white">TOTAL</td>
                                <td class="px-5 py-4 text-right font-black text-blue-400">Rp {{ number_format($entry->items->sum('debit'), 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-right font-black text-orange-400">Rp {{ number_format($entry->items->sum('credit'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Info & Audit Trail --}}
        <div class="space-y-6">
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
                <h4 class="font-bold text-white mb-4 flex items-center gap-2"><i data-lucide="info" class="w-4 h-4 text-indigo-400"></i> Info Jurnal</h4>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-400">Cabang</span><span class="text-white font-medium">{{ $entry->branch->name ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Periode</span><span class="text-white font-medium">{{ $entry->fiscalPeriod->name ?? 'Tidak ada' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Cost Center</span><span class="text-white font-medium">{{ $entry->cost_center ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Project</span><span class="text-white font-medium">{{ $entry->project_code ?? '-' }}</span></div>
                    @if($entry->approver)
                    <div class="flex justify-between"><span class="text-gray-400">Disetujui oleh</span><span class="text-white font-medium">{{ $entry->approver->name }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-400">Tgl Approve</span><span class="text-white font-medium">{{ $entry->approved_at?->format('d M Y H:i') }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Audit Trail --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
                <h4 class="font-bold text-white mb-4 flex items-center gap-2"><i data-lucide="history" class="w-4 h-4 text-amber-400"></i> Audit Trail</h4>
                <div class="space-y-3">
                    @forelse($entry->auditTrails->take(10) as $trail)
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-6 h-6 rounded-full bg-gray-700 flex items-center justify-center text-[10px] font-bold text-gray-300 shrink-0 mt-0.5">
                            {{ substr($trail->user->name ?? 'S', 0, 1) }}
                        </div>
                        <div>
                            <div class="text-gray-300">{{ $trail->description ?? $trail->action }}</div>
                            <div class="text-[10px] text-gray-500">{{ $trail->created_at->format('d M Y H:i') }} · {{ $trail->user->name ?? 'System' }}</div>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-500 text-sm">Belum ada log audit.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('finance.ledger.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm">← Kembali ke General Ledger</a>
    </div>
</div>
@endsection

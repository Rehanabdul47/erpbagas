@extends('layouts.app')

@section('title', 'Periode Fiskal')
@section('breadcrumb')
<span class="text-gray-200">Divisi Keuangan</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Periode Fiskal</span>
@endsection

@section('content')
<div x-data="{ showModal: false, form: { name: '', start_date: '', end_date: '', notes: '' } }">
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
            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center shrink-0">
                <i data-lucide="calendar-range" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-xl font-bold text-white m-0">Periode Fiskal</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kelola periode akuntansi — buka, tutup, atau kunci periode</p>
            </div>
        </div>
        <button @click="showModal = true" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Buat Periode
        </button>
    </div>

    {{-- Periods Table --}}
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-4 font-medium">Nama Periode</th>
                        <th class="px-5 py-4 font-medium">Mulai</th>
                        <th class="px-5 py-4 font-medium">Berakhir</th>
                        <th class="px-5 py-4 font-medium text-center">Status</th>
                        <th class="px-5 py-4 font-medium">Ditutup Oleh</th>
                        <th class="px-5 py-4 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($periods as $period)
                    <tr class="hover:bg-gray-700/20 transition-colors group">
                        <td class="px-5 py-4 font-bold text-white">{{ $period->name }}</td>
                        <td class="px-5 py-4 text-gray-300">{{ $period->start_date->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-gray-300">{{ $period->end_date->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $sc = ['open' => 'bg-emerald-500/10 text-emerald-400', 'closed' => 'bg-amber-500/10 text-amber-400', 'locked' => 'bg-red-500/10 text-red-400'];
                                $si = ['open' => 'unlock', 'closed' => 'lock', 'locked' => 'shield'];
                            @endphp
                            <span class="text-[10px] px-2 py-1 rounded-full font-bold inline-flex items-center gap-1 {{ $sc[$period->status] ?? '' }}">
                                <i data-lucide="{{ $si[$period->status] ?? 'circle' }}" class="w-3 h-3"></i>
                                {{ strtoupper($period->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-gray-400 text-xs">
                            @if($period->closedByUser)
                                {{ $period->closedByUser->name }}<br>
                                <span class="text-gray-500">{{ $period->closed_at?->format('d M Y H:i') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if($period->isOpen())
                                <form method="POST" action="{{ route('finance.periods.close', $period) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 bg-amber-500/10 text-amber-400 hover:bg-amber-600 hover:text-white rounded-lg text-xs font-medium transition-colors flex items-center gap-1">
                                        <i data-lucide="lock" class="w-3 h-3"></i> Tutup
                                    </button>
                                </form>
                                @elseif($period->isClosed())
                                <form method="POST" action="{{ route('finance.periods.lock', $period) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 bg-red-500/10 text-red-400 hover:bg-red-600 hover:text-white rounded-lg text-xs font-medium transition-colors flex items-center gap-1">
                                        <i data-lucide="shield" class="w-3 h-3"></i> Kunci
                                    </button>
                                </form>
                                @endif
                                @if(!$period->isOpen())
                                <form method="POST" action="{{ route('finance.periods.reopen', $period) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-600 hover:text-white rounded-lg text-xs font-medium transition-colors flex items-center gap-1">
                                        <i data-lucide="unlock" class="w-3 h-3"></i> Buka
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-12 text-gray-500">Belum ada periode fiskal. Buat periode pertama untuk memulai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Create --}}
    <div x-show="showModal" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4">
        <div @click.away="showModal = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2 px-2 py-1 bg-purple-500/10 text-purple-400 rounded-lg">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i> Buat Periode Baru
                </h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('finance.periods.store') }}">
                @csrf
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Nama Periode</label>
                        <input type="text" name="name" x-model="form.name" placeholder="Maret 2026" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Tanggal Mulai</label>
                            <input type="date" name="start_date" x-model="form.start_date" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        </div>
                        <div class="space-y-1.5">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Tanggal Berakhir</label>
                            <input type="date" name="end_date" x-model="form.end_date" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Catatan</label>
                        <textarea name="notes" x-model="form.notes" rows="2" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Opsional"></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/20 flex justify-end gap-3">
                    <button type="button" @click="showModal = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all active:scale-95 flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i> Buat Periode
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

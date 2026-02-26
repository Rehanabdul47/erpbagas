@extends('layouts.app')

@section('title', 'Produksi')
@section('breadcrumb', 'Produksi')

@section('content')
<div class="space-y-6">
    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-3 text-red-500 flex items-center gap-3">
        <i data-lucide="x-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h3 class="text-xl font-bold text-white mb-0">Daftar Produksi</h3>
        <a href="{{ route('production.create') }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-medium transition-all shadow-lg shadow-indigo-600/20 active:scale-[0.98] w-full sm:w-auto">
            <i data-lucide="plus" class="w-4 h-4"></i> Mulai Produksi
        </a>
    </div>

    <!-- Table Container -->
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden text-sm">
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left whitespace-nowrap">
                <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                    <tr>
                        <th class="px-5 py-3 font-medium">ID</th>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Produk Final</th>
                        <th class="px-5 py-3 font-medium">Jumlah</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Pembuat</th>
                        <th class="px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    @forelse($productions as $prod)
                    <tr class="hover:bg-indigo-500/5 transition-colors group">
                        <td class="px-5 py-3 font-bold text-indigo-400 font-mono text-xs">#{{ $prod->id }}</td>
                        <td class="px-5 py-3 text-gray-300">{{ \Carbon\Carbon::parse($prod->date)->format('d M Y') }}</td>
                        <td class="px-5 py-3 font-bold text-white">{{ $prod->product->name }}</td>
                        <td class="px-5 py-3 text-gray-300 font-medium">{{ $prod->quantity }}</td>
                        <td class="px-5 py-3">
                            <div class="flex">
                                @if($prod->status === 'draft')
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold border flex items-center gap-1.5 bg-gray-500/10 text-gray-400 border-gray-500/20">Draft</span>
                                @elseif($prod->status === 'processing')
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold border flex items-center gap-1.5 bg-amber-500/10 text-amber-500 border-amber-500/20">⏳ Processing</span>
                                @elseif($prod->status === 'completed')
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold border flex items-center gap-1.5 bg-emerald-500/10 text-emerald-400 border-emerald-500/20">✅ Selesai</span>
                                @elseif($prod->status === 'cancelled')
                                    <span class="px-2 py-1 rounded-full text-[10px] font-bold border flex items-center gap-1.5 bg-red-500/10 text-red-400 border-red-500/20">❌ Batal</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold border bg-gray-500/10 text-gray-400 border-gray-500/20">{{ $prod->user->name ?? 'Sistem' }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            @if($prod->status === 'processing')
                                <div class="flex items-center gap-2 opacity-50 group-hover:opacity-100 transition-opacity">
                                    <form action="{{ route('production.update-status', $prod) }}" method="POST" onsubmit="return confirm('Tandai produksi selesai?')" class="m-0">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="p-1.5 rounded-lg text-emerald-400 hover:text-white hover:bg-emerald-500 transition-colors" title="Selesai">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('production.update-status', $prod) }}" method="POST" onsubmit="return confirm('Batalkan produksi dan kembalikan bahan baku?')" class="m-0">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:text-white hover:bg-red-500 transition-colors" title="Batal">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-gray-500 text-sm">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-500">
                            <div class="flex flex-col items-center justify-center gap-3">
                                <i data-lucide="package-open" class="w-12 h-12 text-gray-600 opacity-50"></i>
                                <span>Belum ada data produksi</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

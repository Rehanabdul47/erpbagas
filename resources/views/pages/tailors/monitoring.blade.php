@extends('layouts.app')

@section('title', 'Monitor Proses Produksi')
@section('breadcrumb', 'Monitor Proses Produksi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h3 class="text-xl font-bold text-white mb-1">Monitor Vendor Penjahit</h3>
            <p class="text-sm text-gray-400">Pantau progres produksi yang dialokasikan ke penjahit eksternal.</p>
        </div>
        <a href="{{ route('tailors.index') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors w-full sm:w-auto">
            <i data-lucide="scissors" class="w-4 h-4"></i> Kelola Vendor
        </a>
    </div>

    <!-- Kanban / Grid View for Tailors -->
    @if($tailors->isEmpty())
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-10 text-center flex flex-col items-center justify-center">
            <i data-lucide="folder-open" class="w-12 h-12 text-gray-600 mb-3 opacity-50"></i>
            <h4 class="text-white font-medium mb-1">Belum ada vendor penjahit</h4>
            <p class="text-gray-400 text-sm mb-4">Tambahkan data vendor terlebih dahulu untuk mulai memantau pekerjaan mereka.</p>
            <a href="{{ route('tailors.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors">
                Tambah Vendor
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 items-start">
            @foreach($tailors as $tailor)
                <!-- Tailor Card -->
                <div class="bg-gray-800 border border-gray-700/50 rounded-xl overflow-hidden flex flex-col shadow-sm">
                    <!-- Tailor Header -->
                    <div class="p-4 border-b border-gray-700/50 bg-gray-900/30 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center shrink-0 border border-indigo-500/30">
                                <i data-lucide="scissors" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-white">{{ $tailor->name }}</h4>
                                <div class="text-xs text-gray-400 flex items-center gap-1">
                                    <i data-lucide="phone" class="w-3 h-3"></i> {{ $tailor->phone ?? 'Tidak ada no. telp' }}
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $tailor->productions->count() > 0 ? 'bg-amber-500/10 text-amber-500 border-amber-500/20' : 'bg-gray-500/10 text-gray-400 border-gray-500/20' }}">
                            {{ $tailor->productions->count() }} Aktif
                        </span>
                    </div>

                    <!-- Active Productions List -->
                    <div class="p-4 flex flex-col gap-3 min-h-[150px] max-h-[400px] overflow-y-auto">
                        @forelse($tailor->productions as $production)
                            <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-3 hover:border-gray-600 transition-colors">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <div class="font-bold text-sm text-white">{{ $production->product->name }}</div>
                                        <div class="text-xs font-mono text-indigo-400 mt-0.5">#{{ $production->id }}</div>
                                    </div>
                                    <span class="px-2 py-0.5 bg-gray-800 border border-gray-700 rounded text-xs text-gray-300 font-medium">
                                        {{ $production->quantity }} unit
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-800">
                                    <div class="flex-1">
                                        <div class="text-[10px] text-gray-500 tracking-wider uppercase mb-1">Tahap Saat Ini</div>
                                        <div class="text-xs font-medium text-amber-400 flex items-center gap-1.5">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                            {{ $production->stage_label }}
                                        </div>
                                    </div>
                                    <a href="{{ route('production.index') }}?search={{ $production->id }}" class="shrink-0 p-1.5 rounded-lg bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 transition-colors" title="Lihat di Produksi">
                                        <i data-lucide="external-link" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="flex-1 flex flex-col items-center justify-center text-center py-6 opacity-60">
                                <i data-lucide="coffee" class="w-8 h-8 text-gray-500 mb-2"></i>
                                <span class="text-xs text-gray-400">Belum ada tanggungan produksi<br>saat ini.</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

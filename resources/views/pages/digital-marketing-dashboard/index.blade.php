@extends('layouts.app')

@section('title', 'Overview Digital Marketing')
@section('breadcrumb', 'Overview Digital Marketing')

@section('content')
<div class="flex flex-col items-center justify-center py-20 bg-gray-800 border border-gray-700/50 rounded-2xl shadow-sm">
    <div class="w-20 h-20 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-6 border border-indigo-500/20">
        <i data-lucide="layout-dashboard" class="w-10 h-10"></i>
    </div>
    <h3 class="text-2xl font-black text-white mb-2">Divisi Digital Marketing</h3>
    <p class="text-gray-400 text-center max-w-md px-6">Modul sedang dalam proses pembangunan ulang. Silakan hubungi tim pengembang untuk detail lebih lanjut.</p>
    
    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4 w-full max-w-2xl px-6">
        <div class="p-4 bg-gray-900/50 border border-gray-700/50 rounded-xl">
            <div class="flex items-center gap-3 mb-2">
                <i data-lucide="activity" class="w-5 h-5 text-emerald-400"></i>
                <h4 class="font-bold text-white text-sm uppercase tracking-wider">Status Modul</h4>
            </div>
            <p class="text-xs text-gray-400 line-clamp-2">Data Platform, E-Commerce Orders, dan Setting E-commerce telah dihapus untuk persiapan struktur baru.</p>
        </div>
        <div class="p-4 bg-gray-900/50 border border-gray-700/50 rounded-xl">
            <div class="flex items-center gap-3 mb-2">
                <i data-lucide="trending-down" class="w-5 h-5 text-red-400"></i>
                <h4 class="font-bold text-white text-sm uppercase tracking-wider">Iklan Online</h4>
            </div>
            <p class="text-xs text-gray-400">Total Biaya Iklan (Bulan Ini): <span class="text-white font-bold">Rp {{ number_format($totalExpense, 0, ',', '.') }}</span></p>
        </div>
    </div>
</div>
@endsection

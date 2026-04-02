@extends('layouts.app')

@section('title', 'Setting E-Commerce')
@section('breadcrumb', 'API Integration TikTok Shop')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-2xl font-black text-white flex items-center gap-2">
            <i data-lucide="settings" class="w-6 h-6 text-indigo-400"></i>
            Setting E-Commerce (TikTok Shop)
        </h3>
        <p class="text-sm text-gray-400 mt-1">Kelola credentials API untuk sinkronisasi otomatis dengan toko TikTok Anda.</p>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-1 border border-gray-700/50 bg-gray-800 rounded-xl p-5 shadow-sm">
        <h4 class="text-white font-bold mb-4 flex items-center gap-2">
            <i data-lucide="key" class="w-4 h-4 text-emerald-400"></i> API Credentials
        </h4>
        <form action="{{ route('digital-marketing.settings.update') }}" method="POST" class="space-y-4">
            @csrf
            
            <input type="hidden" name="platform_name" value="TikTok Shop">
            
            <div>
                <label class="block text-xs font-bold text-gray-400 mb-1">App Key</label>
                <input type="text" name="app_key" value="{{ old('app_key', $integration->app_key) }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-400 mb-1">App Secret</label>
                <input type="password" name="app_secret" value="{{ old('app_secret', $integration->app_secret) }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-400 mb-1">Access Token</label>
                <textarea name="access_token" rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>{{ old('access_token', $integration->access_token) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 mb-1">Shop Cipher (Opsional)</label>
                <input type="text" name="shop_cipher" value="{{ old('shop_cipher', $integration->shop_cipher) }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <label class="flex items-center gap-2 cursor-pointer mt-4">
                <input type="checkbox" name="is_active" {{ $integration->is_active ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-gray-800">
                <span class="text-sm font-medium text-gray-300">Aktifkan Integrasi</span>
            </label>

            <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold shadow-sm transition-colors mt-4">
                Simpan Konfigurasi
            </button>
        </form>
    </div>

    <div class="md:col-span-2 space-y-6">
        <div class="border border-gray-700/50 bg-gray-800 rounded-xl p-5 shadow-sm">
            <h4 class="text-white font-bold mb-4 flex items-center gap-2">
                <i data-lucide="webhook" class="w-4 h-4 text-blue-400"></i> Informasi Webhook
            </h4>
            <p class="text-xs text-gray-400 mb-4 leading-relaxed">
                Salin URL di bawah ini ke panel Partner Center TikTok Shop Anda untuk menerima pembaruan Order dan Stok secara Real-time.
            </p>
            <div class="flex items-center gap-2 bg-gray-900 p-3 rounded-lg border border-gray-700">
                <input type="text" value="{{ url('/api/tiktok/webhook') }}" class="bg-transparent border-none text-sm text-indigo-300 flex-1 outline-none font-mono" readonly id="webhookUrl">
                <button onclick="navigator.clipboard.writeText(document.getElementById('webhookUrl').value); alert('Tersalin!')" class="p-2 bg-gray-800 hover:bg-gray-700 rounded-md text-gray-300">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                </button>
            </div>
            <div class="mt-4 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg flex items-start gap-3">
                <i data-lucide="info" class="w-5 h-5 text-blue-400 shrink-0 mt-0.5"></i>
                <div class="text-xs text-blue-300/80 leading-relaxed">
                    Pastikan Anda telah mengisi <strong>App Secret</strong> dengan benar agar sistem dapat memvalidasi signature HMAC-SHA256 dari TikTok secara otomatis untuk menjaga keamanan data.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

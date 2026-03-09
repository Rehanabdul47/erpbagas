@extends('layouts.app')

@section('title', 'Settings')
@section('breadcrumb', 'Settings')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ 
    activeTab: 'company',
    darkMode: {{ ($settings['dark_mode'] ?? 'true') === 'true' ? 'true' : 'false' }},
    notifications: {{ ($settings['notifications'] ?? 'true') === 'true' ? 'true' : 'false' }},
    emailAlerts: {{ ($settings['email_alerts'] ?? 'true') === 'true' ? 'true' : 'false' }},
    autoBackup: {{ ($settings['auto_backup'] ?? 'false') === 'true' ? 'true' : 'false' }},
    twoFactor: {{ ($settings['two_factor'] ?? 'false') === 'true' ? 'true' : 'false' }},
    saveSettings() {
        this.$dispatch('global-alert', {
            type: 'success',
            title: 'SUCCESS',
            message: 'System configuration saved successfully! All updates have been synchronized.',
            confirmText: 'PROCEED'
        });
    }
}"
@if(session('success'))
x-init="$nextTick(() => { 
    $dispatch('global-alert', {
        type: 'success',
        title: 'SUCCESS',
        message: '{{ session('success') }}',
        confirmText: 'PROCEED'
    });
})"
@elseif(session('error'))
x-init="$nextTick(() => { 
    $dispatch('global-alert', {
        type: 'error',
        title: 'ERROR',
        message: '{{ session('error') }}',
        confirmText: 'CLOSE'
    });
})"
@endif
>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-2">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20">
                <i data-lucide="settings" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-2xl font-black text-white tracking-tight text-shadow-glow">System Configuration</h2>
                <p class="text-gray-500 text-sm">Manage your enterprise environment and preferences.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[10px] font-black tracking-widest uppercase">System Online</span>
            <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded-full text-[10px] font-black tracking-widest uppercase">Version 1.0.0</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Sidebar Navigation --}}
        <div class="lg:col-span-3 space-y-2">
            <nav class="space-y-1">
                <button @click="activeTab = 'company'" :class="activeTab === 'company' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 border-indigo-500' : 'bg-gray-800/50 text-gray-400 hover:text-white hover:bg-gray-800 border-gray-700/50'" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold transition-all border group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="building-2" class="w-4 h-4"></i> Company
                    </div>
                    <i data-lucide="chevron-right" class="w-4 h-4 opacity-0 group-hover:opacity-100 transition-opacity" :class="activeTab === 'company' && 'hidden'"></i>
                </button>
                
                <button @click="activeTab = 'localization'" :class="activeTab === 'localization' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 border-indigo-500' : 'bg-gray-800/50 text-gray-400 hover:text-white hover:bg-gray-800 border-gray-700/50'" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold transition-all border group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="globe" class="w-4 h-4"></i> Localization
                    </div>
                </button>

                <button @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 border-indigo-500' : 'bg-gray-800/50 text-gray-400 hover:text-white hover:bg-gray-800 border-gray-700/50'" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold transition-all border group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="shield-check" class="w-4 h-4"></i> Security
                    </div>
                </button>

                <button @click="activeTab = 'preferences'" :class="activeTab === 'preferences' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 border-indigo-500' : 'bg-gray-800/50 text-gray-400 hover:text-white hover:bg-gray-800 border-gray-700/50'" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold transition-all border group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="sliders" class="w-4 h-4"></i> App Appearance
                    </div>
                </button>

                <button @click="activeTab = 'backup'" :class="activeTab === 'backup' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 border-indigo-500' : 'bg-gray-800/50 text-gray-400 hover:text-white hover:bg-gray-800 border-gray-700/50'" class="w-full flex items-center justify-between px-4 py-3 rounded-xl text-sm font-bold transition-all border group">
                    <div class="flex items-center gap-3">
                        <i data-lucide="hard-drive" class="w-4 h-4"></i> Backup & Export
                    </div>
                </button>
            </nav>

            <div class="mt-8 p-4 bg-indigo-600/10 border border-indigo-500/20 rounded-2xl hidden lg:block">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 flex items-center justify-center text-indigo-400">
                        <i data-lucide="help-circle" class="w-4 h-4"></i>
                    </div>
                    <span class="text-xs font-black text-indigo-300 uppercase">Need Help?</span>
                </div>
                <p class="text-[10px] text-indigo-400/80 leading-relaxed font-bold">
                    Need assistance with configuration? 
                    <a href="https://docs.gupala.com" target="_blank" class="text-indigo-400 underline hover:text-indigo-300 italic tracking-tight">Read documentation</a> or <a href="mailto:support@gupala.com" class="text-indigo-400 underline hover:text-indigo-300 italic tracking-tight">contact support</a>.
                </p>
            </div>
        </div>

        {{-- Content Area --}}
        <div class="lg:col-span-9 space-y-6">
            
            {{-- Tab: Company Profile --}}
            <div x-show="activeTab === 'company'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 border-b border-gray-700/50 flex items-center justify-between bg-gradient-to-r from-gray-800 to-gray-800/50">
                        <h3 class="font-black text-white flex items-center gap-2 italic tracking-tight">
                            <i data-lucide="building-2" class="w-5 h-5 text-indigo-400"></i> CORPORATE IDENTITY
                        </h3>
                        <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Public Information</span>
                    </div>
                    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="group" value="company">
                        <div class="p-6 space-y-6">
                            <div class="flex flex-col sm:flex-row gap-6">
                                <label for="company_logo" class="w-24 h-24 sm:w-32 sm:h-32 rounded-3xl bg-gray-900 border-2 border-dashed border-gray-700 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-indigo-500 transition-all group shrink-0 relative overflow-hidden">
                                    @if(isset($settings['company_logo']) && $settings['company_logo'])
                                        <img src="{{ asset($settings['company_logo']) }}" alt="Company Logo" class="absolute inset-0 w-full h-full object-contain p-2 bg-white/5">
                                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center transition-all">
                                            <i data-lucide="upload-cloud" class="w-6 h-6 text-white mb-1"></i>
                                            <span class="text-[8px] font-black text-white uppercase text-center px-2">Change<br>Logo</span>
                                        </div>
                                    @else
                                        <i data-lucide="upload-cloud" class="w-6 h-6 text-gray-600 group-hover:text-indigo-400 group-hover:scale-110 transition-all"></i>
                                        <span class="text-[8px] font-black text-gray-500 uppercase text-center px-2 group-hover:text-indigo-400 transition-colors">Upload<br>Brand Logo</span>
                                    @endif
                                </label>
                                <input type="file" id="company_logo" name="company_logo" class="hidden" accept="image/*">

                                <div class="flex-1 space-y-6">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Company Name</label>
                                            <input type="text" name="company_name" value="{{ $settings['company_name'] ?? 'GUPALA INDONESIA' }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-bold">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Legal ENTITY / TYPE</label>
                                            <select name="company_type" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-bold">
                                                <option value="PT" {{ ($settings['company_type'] ?? '') == 'PT' ? 'selected' : '' }}>PT (Persero Terbatas)</option>
                                                <option value="CV" {{ ($settings['company_type'] ?? '') == 'CV' ? 'selected' : '' }}>CV (Commanditaire Vennootschap)</option>
                                                <option value="Individual" {{ ($settings['company_type'] ?? '') == 'Individual' ? 'selected' : '' }}>Individual</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Tax ID (NPWP)</label>
                                            <input type="text" name="tax_id" value="{{ $settings['tax_id'] ?? '01.234.567.8-901.000' }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-mono">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Industry Sector</label>
                                            <input type="text" name="industry_sector" value="{{ $settings['industry_sector'] ?? 'Manufacturing & Retail' }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-bold">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Business HQ Address</label>
                                <textarea name="address" rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">{{ $settings['address'] ?? 'Jl. pembatikan,warulor,wiradesa,Indonesia' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Primary Email</label>
                                    <input type="email" name="primary_email" value="{{ $settings['primary_email'] ?? 'corporate@gupala.com' }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Contact / WhatsApp</label>
                                    <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '+62 812 3456 7890' }}" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-900/50 border-t border-gray-700/50 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">
                                Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab: Localization --}}
            <div x-show="activeTab === 'localization'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 border-b border-gray-700/50 bg-gradient-to-r from-gray-800 to-gray-800/50">
                        <h3 class="font-black text-white flex items-center gap-2 italic tracking-tight">
                            <i data-lucide="globe" class="w-5 h-5 text-indigo-400"></i> REGIONAL & LOCALE
                        </h3>
                    </div>
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="group" value="localization">
                        <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Base Currency</label>
                                <select name="base_currency" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition-all font-bold">
                                    <option value="IDR" {{ ($settings['base_currency'] ?? '') == 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah (Rp)</option>
                                    <option value="USD" {{ ($settings['base_currency'] ?? '') == 'USD' ? 'selected' : '' }}>USD - US Dollar ($)</option>
                                    <option value="EUR" {{ ($settings['base_currency'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Server Timezone</label>
                                <select name="timezone" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition-all font-bold">
                                    <option value="GMT+7" {{ ($settings['timezone'] ?? '') == 'GMT+7' ? 'selected' : '' }}>(GMT+07:00) Western Indonesia Time</option>
                                    <option value="GMT+8" {{ ($settings['timezone'] ?? '') == 'GMT+8' ? 'selected' : '' }}>(GMT+08:00) Central Indonesia Time</option>
                                    <option value="GMT+9" {{ ($settings['timezone'] ?? '') == 'GMT+9' ? 'selected' : '' }}>(GMT+09:00) Eastern Indonesia Time</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Date Format</label>
                                <select name="date_format" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="DD/MM/YYYY" {{ ($settings['date_format'] ?? '') == 'DD/MM/YYYY' ? 'selected' : '' }}>DD/MM/YYYY (31/12/2026)</option>
                                    <option value="MM/DD/YYYY" {{ ($settings['date_format'] ?? '') == 'MM/DD/YYYY' ? 'selected' : '' }}>MM/DD/YYYY (12/31/2026)</option>
                                    <option value="YYYY-MM-DD" {{ ($settings['date_format'] ?? '') == 'YYYY-MM-DD' ? 'selected' : '' }}>YYYY-MM-DD (2026-12-31)</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Number Format</label>
                                <select name="number_format" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                                    <option value="id" {{ ($settings['number_format'] ?? '') == 'id' ? 'selected' : '' }}>1.234.567,89 (Decimal Comma)</option>
                                    <option value="en" {{ ($settings['number_format'] ?? '') == 'en' ? 'selected' : '' }}>1,234,567.89 (Decimal Point)</option>
                                </select>
                            </div>
                        </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-900/50 border-t border-gray-700/50 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">
                                Update Locale
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab: Security --}}
            <div x-show="activeTab === 'security'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 border-b border-gray-700/50 bg-gradient-to-r from-gray-800 to-gray-800/50">
                        <h3 class="font-black text-white flex items-center gap-2 italic tracking-tight">
                            <i data-lucide="shield-check" class="w-5 h-5 text-indigo-400"></i> SYSTEM SECURITY
                        </h3>
                    </div>
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="group" value="security">
                        <input type="hidden" name="two_factor" :value="twoFactor ? 'true' : 'false'">
                        <div class="p-6 space-y-6">
                            
                            {{-- Ubah Kata Sandi --}}
                            <div class="bg-gray-900/50 border border-gray-700 overflow-hidden rounded-2xl">
                                <div class="px-4 py-3 border-b border-gray-700/50 bg-gray-800/50">
                                    <h4 class="text-xs font-black text-white uppercase tracking-widest flex items-center gap-2">
                                        <i data-lucide="key" class="w-4 h-4 text-indigo-400"></i> Change Password
                                    </h4>
                                </div>
                                <div class="p-4 space-y-4">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Current Password</label>
                                        <input type="password" name="current_password" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" placeholder="Enter current password">
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">New Password</label>
                                            <input type="password" name="new_password" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" placeholder="Enter new password">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Confirm Password</label>
                                            <input type="password" name="new_password_confirmation" class="w-full bg-gray-800 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" placeholder="Confirm new password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-gray-900/50 border border-gray-700 rounded-2xl">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center">
                                        <i data-lucide="lock" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-white uppercase tracking-tight">Two-Factor Authentication</div>
                                        <div class="text-[10px] text-gray-500 font-bold">Require 2FA for all administrative accounts</div>
                                    </div>
                                </div>
                                <div @click="twoFactor = !twoFactor" class="w-12 h-6 rounded-full relative cursor-pointer transition-colors" :class="twoFactor ? 'bg-indigo-600' : 'bg-gray-700'">
                                    <div class="absolute top-1 w-4 h-4 bg-white rounded-full transition-transform" :class="twoFactor ? 'right-1' : 'left-1'"></div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-900/50 border border-gray-700 rounded-2xl">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                                        <i data-lucide="timer" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-white uppercase tracking-tight">Session Timeout</div>
                                        <div class="text-[10px] text-gray-500 font-bold">Auto-logout after period of inactivity</div>
                                    </div>
                                </div>
                                <select name="session_timeout" class="bg-gray-800 border border-gray-700 text-white text-xs rounded-lg px-2 py-1 outline-none">
                                    <option value="15" {{ ($settings['session_timeout'] ?? '') == '15' ? 'selected' : '' }}>15 Minutes</option>
                                    <option value="30" {{ ($settings['session_timeout'] ?? '') == '30' ? 'selected' : '' }}>30 Minutes</option>
                                    <option value="60" {{ ($settings['session_timeout'] ?? '') == '60' ? 'selected' : '' }}>1 Hour</option>
                                    <option value="240" {{ ($settings['session_timeout'] ?? '') == '240' ? 'selected' : '' }}>4 Hours</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-between p-4 bg-gray-900/50 border border-gray-700 rounded-2xl">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-gray-500/10 text-gray-400 flex items-center justify-center">
                                        <i data-lucide="fingerprint" class="w-5 h-5"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-black text-white uppercase tracking-tight">Password Policy</div>
                                        <div class="text-[10px] text-gray-500 font-bold uppercase mt-0.5">Enforce rules for all users</div>
                                    </div>
                                </div>
                                <select name="password_policy" class="bg-gray-800 border border-gray-700 text-white text-xs rounded-lg px-2 py-1 outline-none">
                                    <option value="low" {{ ($settings['password_policy'] ?? '') == 'low' ? 'selected' : '' }}>Low (Min 6 chars)</option>
                                    <option value="medium" {{ ($settings['password_policy'] ?? '') == 'medium' ? 'selected' : '' }}>Medium (Alphanumeric)</option>
                                    <option value="high" {{ ($settings['password_policy'] ?? 'high') == 'high' ? 'selected' : '' }}>High (Special chars + Uppercase)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-900/50 border-t border-gray-700/50 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">
                            Save Security
                        </button>
                    </div>
                    </form>
                </div>
            </div>

            {{-- Tab: Preferences --}}
            <div x-show="activeTab === 'preferences'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden p-6 space-y-6">
                    <h3 class="font-black text-white flex items-center gap-2 italic tracking-tight uppercase">
                        <i data-lucide="palette" class="w-5 h-5 text-indigo-400"></i> App Preferences
                    </h3>
                    <form action="{{ route('settings.update') }}" method="POST">
                    @csrf
                    <input type="hidden" name="group" value="preferences">
                    <input type="hidden" name="dark_mode" :value="darkMode ? 'true' : 'false'">
                    <input type="hidden" name="notifications" :value="notifications ? 'true' : 'false'">
                    <input type="hidden" name="email_alerts" :value="emailAlerts ? 'true' : 'false'">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div @click="darkMode = !darkMode; $nextTick(() => { $el.closest('form').submit() })" class="flex items-center justify-between p-4 bg-gray-900/50 border border-gray-700 rounded-2xl cursor-pointer hover:bg-gray-900 transition-all hover:border-indigo-500/50 group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i data-lucide="moon" class="w-5 h-5" x-show="darkMode"></i>
                                    <i data-lucide="sun" class="w-5 h-5" x-show="!darkMode"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-white uppercase tracking-tight">Dark Aesthetic</div>
                                    <div class="text-[10px] text-gray-500 font-bold uppercase">Modern high-contrast mode</div>
                                </div>
                            </div>
                            <div class="w-12 h-6 rounded-full relative shadow-inner transition-colors duration-300" :class="darkMode ? 'bg-indigo-600' : 'bg-gray-700'">
                                <div class="absolute top-1 w-4 h-4 bg-white rounded-full shadow-sm transition-all duration-300" :class="darkMode ? 'right-1' : 'left-1'"></div>
                            </div>
                        </div>
                        
                        <div @click="notifications = !notifications; $nextTick(() => { $el.closest('form').submit() })" class="flex items-center justify-between p-4 bg-gray-900/50 border border-gray-700 rounded-2xl cursor-pointer hover:bg-gray-900 transition-all hover:border-indigo-500/50 group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i data-lucide="zap" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-white uppercase tracking-tight">Real-time alerts</div>
                                    <div class="text-[10px] text-gray-500 font-bold uppercase">Instant system events</div>
                                </div>
                            </div>
                            <div class="w-12 h-6 rounded-full relative shadow-inner transition-colors duration-300" :class="notifications ? 'bg-emerald-600' : 'bg-gray-700'">
                                <div class="absolute top-1 w-4 h-4 bg-white rounded-full shadow-sm transition-all duration-300" :class="notifications ? 'right-1' : 'left-1'"></div>
                            </div>
                        </div>

                        <div @click="emailAlerts = !emailAlerts; $nextTick(() => { $el.closest('form').submit() })" class="flex items-center justify-between p-4 bg-gray-900/50 border border-gray-700 rounded-2xl cursor-pointer hover:bg-gray-900 transition-all hover:border-indigo-500/50 group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <i data-lucide="mail" class="w-5 h-5"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-white uppercase tracking-tight">Email Journals</div>
                                    <div class="text-[10px] text-gray-500 font-bold uppercase">Daily operations summary</div>
                                </div>
                            </div>
                            <div class="w-12 h-6 rounded-full relative shadow-inner transition-colors duration-300" :class="emailAlerts ? 'bg-indigo-600' : 'bg-gray-700'">
                                <div class="absolute top-1 w-4 h-4 bg-white rounded-full shadow-sm transition-all duration-300" :class="emailAlerts ? 'right-1' : 'left-1'"></div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>

            {{-- Tab: Backup --}}
            <div x-show="activeTab === 'backup'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
                    <div class="p-6 border-b border-gray-700/50 bg-gradient-to-r from-gray-800 to-gray-800/50 flex justify-between items-center">
                        <h3 class="font-black text-white flex items-center gap-2 italic tracking-tight uppercase">
                            <i data-lucide="hard-drive" class="w-5 h-5 text-indigo-400"></i> Data Management
                        </h3>
                        <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 text-[8px] font-black uppercase rounded border border-emerald-500/20">All Safe</span>
                    </div>
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="group" value="backup">
                        <input type="hidden" name="auto_backup" :value="autoBackup ? 'true' : 'false'">
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <button type="submit" formaction="{{ route('settings.backup.manual') }}" formmethod="POST" class="flex items-center gap-4 p-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl transition-all shadow-xl shadow-indigo-600/20 group w-full text-left">
                                <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center shrink-0">
                                    <i data-lucide="download" class="w-6 h-6 group-hover:bounce transition-all"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black uppercase tracking-tight">Manual Backup</div>
                                    <div class="text-[10px] opacity-70 font-bold">Download Full DB Snapshot</div>
                                </div>
                            </button>
                            <button type="submit" formaction="{{ route('settings.export.audit') }}" formmethod="POST" class="flex items-center gap-4 p-4 bg-gray-900 border border-gray-700 text-white rounded-2xl hover:border-gray-500 transition-all group w-full text-left">
                                <div class="w-12 h-12 rounded-xl bg-gray-800 flex items-center justify-center shrink-0">
                                    <i data-lucide="file-json" class="w-6 h-6 text-indigo-400"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black uppercase tracking-tight">Export Audit Log</div>
                                    <div class="text-[10px] text-gray-500 font-bold uppercase">CSV / JSON Format</div>
                                </div>
                            </button>
                        </div>

                        <div @click="autoBackup = !autoBackup; $el.closest('form').submit()" class="flex items-center justify-between p-4 bg-gray-900/30 border border-gray-700 border-dashed rounded-2xl cursor-pointer hover:bg-gray-900 transition-all">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                                    <i data-lucide="refresh-cw" class="w-5 h-5" :class="autoBackup && 'animate-spin-slow'"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-white uppercase tracking-tight">Automatic Cloud Backup</div>
                                    <div class="text-[10px] text-gray-500 font-bold uppercase">Shadow-copy every 24 hours</div>
                                </div>
                            </div>
                            <div class="w-12 h-6 rounded-full relative transition-colors duration-300" :class="autoBackup ? 'bg-indigo-600' : 'bg-gray-700'">
                                <div class="absolute top-1 w-4 h-4 bg-white rounded-full shadow-sm transition-all duration-300" :class="autoBackup ? 'right-1' : 'left-1'"></div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
    }
    .group:hover .group-hover\:bounce {
        animation: bounce 0.5s infinite;
    }
    .animate-spin-slow {
        animation: spin 3s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .text-shadow-glow {
        text-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
    }
</style>
@endsection

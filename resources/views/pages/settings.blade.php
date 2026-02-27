@extends('layouts.app')

@section('title', 'Settings')
@section('breadcrumb', 'Settings')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ 
    activeTab: 'company',
    darkMode: true,
    notifications: false,
    saveSettings() {
        // Mock save logic
        alert('Settings saved successfully!');
    }
}">
    <div class="flex items-center gap-4 mb-2">
        <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20">
            <i data-lucide="settings" class="w-6 h-6"></i>
        </div>
        <div>
            <h2 class="text-2xl font-black text-white tracking-tight">Application Settings</h2>
            <p class="text-gray-500 text-sm">Configure your ERP system and enterprise preferences.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- Navigation --}}
        <div class="space-y-2">
            <button @click="activeTab = 'company'" :class="activeTab === 'company' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 border-indigo-500' : 'bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 border-gray-700/50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all border">
                <i data-lucide="building-2" class="w-4 h-4"></i> Company Profile
            </button>
            <button @click="activeTab = 'localization'" :class="activeTab === 'localization' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 border-indigo-500' : 'bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 border-gray-700/50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all border">
                <i data-lucide="globe" class="w-4 h-4"></i> Localization
            </button>
            <button @click="activeTab = 'security'" :class="activeTab === 'security' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 border-indigo-500' : 'bg-gray-800 text-gray-400 hover:text-white hover:bg-gray-700 border-gray-700/50'" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition-all border">
                <i data-lucide="shield-check" class="w-4 h-4"></i> Security & Roles
            </button>
        </div>

        {{-- Content Area --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Tab: Company Profile --}}
            <div x-show="activeTab === 'company'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-xl overflow-hidden">
                    <div class="p-6 border-b border-gray-700/50">
                        <h3 class="font-bold text-white flex items-center gap-2 italic">
                            <i data-lucide="info" class="w-4 h-4 text-indigo-400"></i> Corporate Identity
                        </h3>
                    </div>
                    <form @submit.prevent="saveSettings()">
                        <div class="p-6 space-y-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Company Name</label>
                                    <input type="text" value="GUPALA INDONESIA" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-bold">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Tax ID (NPWP)</label>
                                    <input type="text" value="01.234.567.8-901.000" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-mono">
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Business Address</label>
                                <textarea rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">Jl. Antigravity No. 10, Jakarta Selatan, Indonesia</textarea>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Primary Email</label>
                                    <input type="email" value="corporate@gupala.com" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Hotline / WhatsApp</label>
                                    <input type="text" value="+62 812 3456 7890" class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 bg-gray-900/50 border-t border-gray-700/50 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-black uppercase tracking-widest transition-all shadow-lg active:scale-95">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tab: Localization --}}
            <div x-show="activeTab === 'localization'" x-cloak style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" class="bg-gray-800 border border-gray-700/50 rounded-2xl p-6 shadow-xl space-y-6">
                <h3 class="font-bold text-white flex items-center gap-2 italic">
                    <i data-lucide="globe" class="w-4 h-4 text-indigo-400"></i> Region & Currency
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Default Currency</label>
                        <select class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="IDR">IDR - Indonesian Rupiah (Rp)</option>
                            <option value="USD">USD - US Dollar ($)</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest pl-1">Timezone</label>
                        <select class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                            <option value="GMT+7">Jakarta (GMT+7)</option>
                            <option value="GMT+8">Makassar (GMT+8)</option>
                            <option value="GMT+9">Jayapura (GMT+9)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Preferences Toggles (System branding always visible at bottom or moved into sections) --}}
            <div class="bg-gray-800 border border-gray-700/50 rounded-2xl shadow-xl overflow-hidden p-6 space-y-6">
                <h3 class="font-bold text-white flex items-center gap-2 italic">
                    <i data-lucide="palette" class="w-4 h-4 text-indigo-400"></i> System Preferences
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div @click="darkMode = !darkMode" class="flex items-center justify-between p-4 bg-gray-900/50 border border-gray-700/50 rounded-xl cursor-pointer hover:bg-gray-900 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                                <i data-lucide="moon" class="w-5 h-5" x-show="darkMode"></i>
                                <i data-lucide="sun" class="w-5 h-5" x-show="!darkMode"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">Dark Mode</div>
                                <div class="text-[10px] text-gray-500 uppercase">Premium dark aesthetics</div>
                            </div>
                        </div>
                        <div class="w-12 h-6 rounded-full relative shadow-inner transition-colors duration-300" :class="darkMode ? 'bg-indigo-600' : 'bg-gray-700'">
                            <div class="absolute top-1 w-4 h-4 bg-white rounded-full shadow-sm transition-all duration-300" :class="darkMode ? 'right-1' : 'left-1'"></div>
                        </div>
                    </div>
                    
                    <div @click="notifications = !notifications" class="flex items-center justify-between p-4 bg-gray-900/50 border border-gray-700/50 rounded-xl cursor-pointer hover:bg-gray-900 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                                <i data-lucide="zap" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">Instant Alerts</div>
                                <div class="text-[10px] text-gray-500 uppercase">Real-time update stream</div>
                            </div>
                        </div>
                        <div class="w-12 h-6 rounded-full relative shadow-inner transition-colors duration-300" :class="notifications ? 'bg-emerald-600' : 'bg-gray-700'">
                            <div class="absolute top-1 w-4 h-4 bg-white rounded-full shadow-sm transition-all duration-300" :class="notifications ? 'right-1' : 'left-1'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

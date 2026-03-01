@extends('layouts.app')

@section('title', 'Tambah Cabang Baru')
@section('breadcrumb', 'Tambah Cabang')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700/50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
                <i data-lucide="store" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="font-bold text-white text-lg">Tambah Cabang / Toko</h3>
                <p class="text-sm text-gray-400">Masukkan detail informasi toko baru</p>
            </div>
        </div>

        <form action="{{ route('branches.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="space-y-6">
                <!-- Session Errors -->
                @if ($errors->any())
                <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl mb-4">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Cabang/Toko <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required 
                               class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" 
                               placeholder="Contoh: Toko Cabang Jakarta">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tipe Toko <span class="text-red-500">*</span></label>
                        <select name="type" required 
                                class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="branch" {{ old('type') == 'branch' ? 'selected' : '' }}>Cabang / Branch</option>
                            <option value="main" {{ old('type') == 'main' ? 'selected' : '' }}>Pusat / Main Store</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Kepala Toko / Manager</label>
                        <input type="text" name="manager_name" value="{{ old('manager_name') }}" 
                               class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" 
                               placeholder="Nama kepala toko">
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Alamat Lengkap</label>
                        <textarea name="address" rows="3" 
                                  class="w-full bg-gray-900 border border-gray-700 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" 
                                  placeholder="Masukkan alamat lengkap toko">{{ old('address') }}</textarea>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-wider">Nomor Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" 
                               class="w-full bg-gray-900 border border-gray-700 text-white font-mono rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all placeholder-gray-600" 
                               placeholder="Contoh: 08123456789">
                    </div>

                    <div class="space-y-2 flex flex-col justify-end">
                        <label class="flex items-center gap-3 cursor-pointer p-3 rounded-xl border border-gray-700 bg-gray-900/50 hover:bg-gray-800 transition-colors">
                            <div class="relative">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-500"></div>
                            </div>
                            <span class="text-sm font-medium text-white">Status Aktif</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-700/50 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <a href="{{ route('branches.index') }}" class="px-6 py-2.5 text-gray-400 hover:text-white hover:bg-gray-800 rounded-xl text-sm font-medium transition-all text-center">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-sm font-bold transition-all shadow-lg shadow-indigo-900/20 active:scale-95 flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Simpan Cabang
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

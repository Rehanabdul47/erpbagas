@php $indent = $depth * 24; @endphp
<tr class="hover:bg-gray-700/20 transition-colors group {{ $account->is_header ? 'bg-gray-900/30' : '' }}">
    <td class="px-5 py-3">
        <div class="flex items-center gap-2" style="padding-left: {{ $indent }}px">
            @if($account->children->count() > 0)
                <i data-lucide="folder" class="w-4 h-4 text-amber-500 shrink-0"></i>
            @else
                <i data-lucide="file-text" class="w-4 h-4 text-gray-500 shrink-0"></i>
            @endif
            <span class="font-mono text-indigo-400 font-bold text-xs">{{ $account->code }}</span>
        </div>
    </td>
    <td class="px-5 py-3">
        <span class="{{ $account->is_header ? 'font-bold text-white' : 'text-gray-300' }} {{ !$account->is_active ? 'line-through opacity-50' : '' }}">
            {{ $account->name }}
        </span>
        @if($account->is_control)
            <span class="ml-2 text-[9px] px-1.5 py-0.5 bg-amber-500/10 text-amber-400 rounded font-bold">CONTROL</span>
        @endif
    </td>
    <td class="px-5 py-3">
        @php
            $typeColors = [
                'asset' => 'bg-blue-500/10 text-blue-400',
                'liability' => 'bg-orange-500/10 text-orange-400',
                'equity' => 'bg-purple-500/10 text-purple-400',
                'revenue' => 'bg-emerald-500/10 text-emerald-400',
                'expense' => 'bg-red-500/10 text-red-400',
            ];
            $typeLabels = ['asset' => 'Aset', 'liability' => 'Liabilitas', 'equity' => 'Ekuitas', 'revenue' => 'Pendapatan', 'expense' => 'Beban'];
        @endphp
        <span class="text-[10px] px-2 py-1 rounded-full font-bold {{ $typeColors[$account->type] ?? '' }}">
            {{ $typeLabels[$account->type] ?? $account->type }}
        </span>
    </td>
    <td class="px-5 py-3 text-center">
        <span class="text-xs font-mono {{ $account->normal_balance === 'debit' ? 'text-blue-400' : 'text-orange-400' }}">
            {{ strtoupper($account->normal_balance ?? '-') }}
        </span>
    </td>
    <td class="px-5 py-3 text-center">
        @if($account->is_active)
            <span class="text-[10px] px-2 py-1 rounded-full font-bold bg-emerald-500/10 text-emerald-400">Aktif</span>
        @else
            <span class="text-[10px] px-2 py-1 rounded-full font-bold bg-gray-500/10 text-gray-400">Nonaktif</span>
        @endif
    </td>
    <td class="px-5 py-3 text-right">
        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            @if(!$account->is_header)
            <button @click="openCreate('{{ $account->id }}')" class="p-1.5 rounded-lg text-gray-400 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors" title="Tambah Sub-Akun">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
            </button>
            @endif
            <button @click="openEdit({{ json_encode($account) }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Edit">
                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
            </button>
            <form method="POST" action="{{ route('finance.accounts.toggle', $account) }}" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-amber-500/10 transition-colors" title="{{ $account->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                    <i data-lucide="{{ $account->is_active ? 'eye-off' : 'eye' }}" class="w-3.5 h-3.5"></i>
                </button>
            </form>
            <button type="button" 
                @click="$dispatch('global-confirm', { 
                    title: 'Hapus Akun', 
                    message: 'Apakah Anda yakin ingin menghapus akun {{ $account->code }}? Akun tidak dapat dihapus jika sudah memiliki transaksi atau sub-akun.', 
                    confirmText: 'Ya, Hapus', 
                    actionUrl: '{{ route('finance.accounts.destroy', $account) }}', 
                    actionMethod: 'DELETE' 
                })" 
                class="p-1.5 rounded-lg text-gray-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors" title="Hapus">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </td>
</tr>
@if($account->children->count() > 0)
    @foreach($account->children as $child)
        @include('pages.finance.accounts._row', ['account' => $child, 'depth' => $depth + 1])
    @endforeach
@endif

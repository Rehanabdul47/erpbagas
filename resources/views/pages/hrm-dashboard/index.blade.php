@extends('layouts.app')

@section('title', 'Overview HRM')
@section('breadcrumb')
<span class="text-gray-200">Divisi Sumber Daya Manusia</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">Overview HRM</span>
@endsection

@section('content')
<div>
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white m-0">Overview HRM</h3>
            <p class="text-xs text-gray-400 mt-0.5">Rangkuman data kepegawaian, presensi harian, dan payroll.</p>
        </div>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('hrm.kiosk') }}" target="_blank" class="flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-600/30 rounded-lg text-xs font-medium transition-colors">
                <i data-lucide="scan-line" class="w-3.5 h-3.5"></i> Buka Kiosk
            </a>
            <a href="{{ route('hrm.index') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-medium transition-colors shadow-sm shadow-indigo-600/20">
                <i data-lucide="users" class="w-3.5 h-3.5"></i> Kelola Karyawan
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="users" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['totalEmployees']) }}</div>
            <div class="text-gray-400 text-sm">Total Karyawan</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="user-check" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['activeEmployees']) }}</div>
            <div class="text-gray-400 text-sm">Karyawan Aktif</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm overflow-hidden relative" id="present-card">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4 relative z-10"><i data-lucide="calendar-check" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1 relative z-10" id="stat-present-today">{{ number_format($stats['presentToday']) }}</div>
            <div class="text-gray-400 text-sm relative z-10">Hadir Hari Ini</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center mb-4"><i data-lucide="wallet" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-white mb-1 truncate" title="Rp {{ number_format($stats['payrollTotal'], 0, ',', '.') }}">Rp {{ number_format($stats['payrollTotal'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Estimasi Payroll ({{ date('M Y') }})</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-6">
        {{-- Live Activity Stream --}}
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm flex flex-col h-full lg:col-span-2">
            <div class="p-5 border-b border-gray-700/50 flex items-center justify-between">
                <h4 class="font-bold text-white flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                    Live Presensi Hari Ini
                </h4>
                <div class="text-xs text-gray-400 font-mono">{{ date('d M Y') }}</div>
            </div>
            <div class="p-0 flex-1 relative min-h-[350px]">
                <div class="absolute inset-0 overflow-y-auto w-full">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider sticky top-0 z-10 border-b border-gray-700/50 backdrop-blur-md">
                            <tr>
                                <th class="px-5 py-3 font-medium">Karyawan</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium">Jam Masuk</th>
                                <th class="px-5 py-3 font-medium">Jam Keluar</th>
                            </tr>
                        </thead>
                        <tbody id="live-attendance-table" class="divide-y divide-gray-700/50">
                            @forelse($recentAttendances as $att)
                            <tr class="hover:bg-gray-700/20 transition-colors" id="att-row-{{ $att->employee_id }}">
                                <td class="px-5 py-3">
                                    <div class="font-bold text-white">{{ $att->employee->name }}</div>
                                    <div class="text-[11px] text-gray-500 font-mono mt-0.5">{{ $att->employee->nip }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border {{ $att->status === 'present' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($att->status === 'absent' ? 'bg-red-500/10 text-red-500 border-red-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20') }}">
                                        {{ ucfirst($att->status) }}
                                    </span>
                                    @if($att->is_late)
                                    <span class="mt-1 px-1.5 py-0.5 flex w-fit rounded bg-red-500/20 text-red-500 text-[9px] font-black tracking-tighter uppercase border border-red-500/30">Telat</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-300 font-mono att-in">{{ $att->clock_in ? substr($att->clock_in, 0, 5) : '-' }}</td>
                                <td class="px-5 py-3 text-gray-300 font-mono att-out">{{ $att->clock_out ? substr($att->clock_out, 0, 5) : '-' }}</td>
                            </tr>
                            @empty
                            <tr id="empty-state">
                                <td colspan="4" class="px-5 py-12 text-center text-gray-500">
                                    <i data-lucide="coffee" class="w-8 h-8 mx-auto mb-3 text-gray-600"></i>
                                    Belum ada presensi yang masuk hari ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick Actions & Information --}}
        <div class="space-y-6">
            <div class="bg-gradient-to-br from-indigo-900/50 to-purple-900/50 border border-indigo-500/20 rounded-xl p-6 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-500/20 rounded-full blur-xl group-hover:bg-indigo-500/30 transition-all"></div>
                <h4 class="font-bold text-white mb-2 flex items-center gap-2 relative z-10"><i data-lucide="scan-line" class="w-5 h-5 text-indigo-400"></i> Kiosk Scanner</h4>
                <p class="text-sm text-indigo-200/70 mb-4 relative z-10">Buka mode kiosk pada tablet atau layar sentuh untuk absensi karyawan via QR Code.</p>
                <a href="{{ route('hrm.kiosk') }}" target="_blank" class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 bg-indigo-500 hover:bg-indigo-400 text-white rounded-lg text-sm font-bold transition-all shadow-lg shadow-indigo-500/20 relative z-10">
                    Buka Kiosk
                </a>
            </div>

            <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
                 <h4 class="font-bold text-white mb-4 text-sm">Aksi Cepat Menu HRM</h4>
                 <div class="grid grid-cols-2 gap-3">
                     <a href="{{ route('hrm.index') }}?tab=employees" class="flex flex-col items-center justify-center gap-2 p-3 bg-gray-900/50 rounded-xl border border-gray-700/50 hover:bg-gray-700/50 hover:border-indigo-500/30 transition-all text-center group">
                         <div class="w-8 h-8 rounded-full bg-indigo-500/10 text-indigo-400 flex items-center justify-center group-hover:scale-110 transition-transform"><i data-lucide="user-plus" class="w-4 h-4"></i></div>
                         <span class="text-xs font-medium text-gray-300">Tambah Karyawan</span>
                     </a>
                     <a href="{{ route('hrm.index') }}?tab=payroll" class="flex flex-col items-center justify-center gap-2 p-3 bg-gray-900/50 rounded-xl border border-gray-700/50 hover:bg-gray-700/50 hover:border-emerald-500/30 transition-all text-center group">
                         <div class="w-8 h-8 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform"><i data-lucide="wallet" class="w-4 h-4"></i></div>
                         <span class="text-xs font-medium text-gray-300">Generate Slip Gaji</span>
                     </a>
                     <a href="{{ route('hrm.index') }}?tab=attendance" class="flex flex-col items-center justify-center gap-2 p-3 bg-gray-900/50 rounded-xl border border-gray-700/50 hover:bg-gray-700/50 hover:border-amber-500/30 transition-all text-center group col-span-2">
                         <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center group-hover:scale-110 transition-transform"><i data-lucide="calendar" class="w-4 h-4"></i></div>
                         <span class="text-xs font-medium text-gray-300">Manajemen Cuti & Izin</span>
                     </a>
                 </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(config('broadcasting.connections.pusher.key'))
<script src="https://js.pusher.com/8.0/pusher.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Pusher === 'undefined') return;

        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
            cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            forceTLS: true
        });

        const channel = pusher.subscribe('attendance');
        channel.bind('App\\Events\\AttendanceRecorded', function(data) {
            const att = data.attendance;
            const empName = att.employee ? att.employee.name : 'User';
            const empNip = att.employee ? att.employee.nip : '-';
            
            // Only sync today's attendance
            const todayStr = new Date().toISOString().split('T')[0];
            const attDateNormalized = att.date.includes('T') ? att.date.split('T')[0] : att.date;

            if (todayStr === attDateNormalized) {
                // Update counter if clock_in and not just updating clock_out
                if (!att.clock_out) {
                    const statEl = document.getElementById('stat-present-today');
                    if (statEl) {
                        let current = parseInt(statEl.textContent.replace(/\D/g,'')) || 0;
                        statEl.textContent = (current + 1).toLocaleString();
                        
                        const card = document.getElementById('present-card');
                        card.classList.add('ring-2', 'ring-amber-500', 'bg-amber-500/10');
                        setTimeout(() => card.classList.remove('ring-2', 'ring-amber-500', 'bg-amber-500/10'), 3000);
                    }
                }

                // Update Table
                const tbody = document.getElementById('live-attendance-table');
                let existingRow = document.getElementById('att-row-' + att.employee_id);
                
                let statusHtml = `<span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border ${att.status === 'present' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20'}">${att.status.charAt(0).toUpperCase() + att.status.slice(1)}</span>`;
                if (att.is_late) {
                    statusHtml += `<span class="mt-1 px-1.5 py-0.5 flex w-fit rounded bg-red-500/20 text-red-500 text-[9px] font-black tracking-tighter uppercase border border-red-500/30">Telat</span>`;
                }
                const inTime = att.clock_in ? att.clock_in.substring(0, 5) : '-';
                const outTime = att.clock_out ? att.clock_out.substring(0, 5) : '-';

                if (existingRow) {
                    // Update existing
                    existingRow.querySelector('td:nth-child(2)').innerHTML = statusHtml;
                    existingRow.querySelector('.att-in').textContent = inTime;
                    existingRow.querySelector('.att-out').textContent = outTime;
                    
                    existingRow.classList.add('bg-indigo-500/20');
                    setTimeout(() => existingRow.classList.remove('bg-indigo-500/20'), 3000);
                } else {
                    // Remove empty state if exists
                    const emptyState = document.getElementById('empty-state');
                    if(emptyState) emptyState.remove();

                    // Prepend new
                    const tr = document.createElement('tr');
                    tr.id = 'att-row-' + att.employee_id;
                    tr.className = 'hover:bg-gray-700/20 transition-colors bg-emerald-500/10';
                    tr.innerHTML = `
                        <td class="px-5 py-3">
                            <div class="font-bold text-white">${empName}</div>
                            <div class="text-[11px] text-gray-500 font-mono mt-0.5">${empNip}</div>
                        </td>
                        <td class="px-5 py-3">${statusHtml}</td>
                        <td class="px-5 py-3 text-gray-300 font-mono att-in">${inTime}</td>
                        <td class="px-5 py-3 text-gray-300 font-mono att-out">${outTime}</td>
                    `;
                    tbody.insertBefore(tr, tbody.firstChild);
                    
                    setTimeout(() => tr.classList.remove('bg-emerald-500/10'), 3000);
                }
            }
        });
    });
</script>
@endif
@endpush

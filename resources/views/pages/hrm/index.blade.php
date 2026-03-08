@extends('layouts.app')

@section('title', 'HRM & Payroll')
@section('breadcrumb')
<span class="text-gray-200">Divisi Sumber Daya Manusia</span>
<span class="mx-2 text-gray-600">/</span>
<span class="text-gray-200">HRM & Payroll</span>
@endsection

@section('content')
<div id="hrm-container" x-data="hrmData()">

    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center shrink-0 shadow-lg shadow-indigo-500/10">
            <i data-lucide="users" class="w-5 h-5"></i>
        </div>
        <div>
            <h3 class="text-xl font-bold text-white m-0">Manajemen HRM & Payroll</h3>
            <p class="text-xs text-gray-400 mt-0.5">Kelola data karyawan, presensi harian, cuti/izin, dan administrasi payroll terintegrasi.</p>
        </div>
        <div class="ml-auto flex gap-2">
            <a href="{{ route('hrm.dashboard') }}" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600/10 text-indigo-400 border border-indigo-500/20 hover:bg-indigo-600/20 rounded-lg text-xs font-medium transition-colors">
                <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i> Dashboard Overview
            </a>
            <button @click="openModalAddEmployee()" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-medium transition-colors shadow-lg shadow-indigo-600/20">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Karyawan
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-8">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm hover:border-indigo-500/30 transition-all group">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"><i data-lucide="users" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['totalEmployees']) }}</div>
            <div class="text-gray-400 text-sm">Total Karyawan</div>
        </div>
        
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm hover:border-amber-500/30 transition-all group relative overflow-hidden" id="present-card">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform relative z-10"><i data-lucide="calendar-check" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1 relative z-10" id="stat-present-today">{{ number_format($stats['presentToday']) }}</div>
            <div class="text-gray-400 text-sm relative z-10">Hadir Hari Ini</div>
        </div>

        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm hover:border-emerald-500/30 transition-all group">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"><i data-lucide="clipboard-list" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['leavesToday'] ?? 0) }}</div>
            <div class="text-gray-400 text-sm">Cuti & Izin Hari Ini</div>
        </div>

        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm hover:border-red-500/30 transition-all group">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center mb-4 group-hover:scale-110 transition-transform"><i data-lucide="wallet" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-white mb-1 truncate" title="Rp {{ number_format($stats['payrollTotal'], 0, ',', '.') }}">Rp {{ number_format($stats['payrollTotal'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Payroll ({{ date('M Y') }})</div>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
        {{-- Tabs Navigation --}}
        <div class="flex overflow-x-auto border-b border-gray-700/50 hide-scrollbar bg-gray-900/20">
            <button @click="switchTab('employees')" :class="activeTab === 'employees' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">Daftar Karyawan</button>
            <button @click="switchTab('attendance')" :class="activeTab === 'attendance' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">Presensi Harian</button>
            <button @click="switchTab('leaves')" :class="activeTab === 'leaves' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">Cuti & Izin</button>
            <button @click="switchTab('payroll')" :class="activeTab === 'payroll' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">Payroll / Gaji</button>
            <button @click="switchTab('settings')" :class="activeTab === 'settings' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">Pengaturan</button>
        </div>

        <div class="p-0">
            {{-- EMPLOYEES TAB --}}
            <div x-show="activeTab === 'employees'" class="p-5">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
                    <h4 class="font-bold text-white flex items-center gap-2"><i data-lucide="users" class="w-5 h-5 text-indigo-400"></i> Managemen Karyawan</h4>
                    <button @click="openModalAddEmployee()" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20 w-full sm:w-auto justify-center">
                        <i data-lucide="plus" class="w-4 h-4"></i> Tambah Karyawan
                    </button>
                </div>
                <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                            <tr><th class="px-4 py-3 font-medium">NIP</th><th class="px-4 py-3 font-medium">Nama</th><th class="px-4 py-3 font-medium">Jabatan</th><th class="px-4 py-3 font-medium">Cabang / Toko</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium text-right">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($employees as $emp)
                            <tr @click="viewEmployeeDetail({{ json_encode($emp) }})" class="hover:bg-indigo-500/10 transition-colors cursor-pointer group">
                                <td class="px-4 py-3"><span class="font-mono text-gray-400 bg-gray-900 px-2 py-1 rounded text-xs">{{ $emp->nip }}</span></td>
                                <td class="px-4 py-3 font-bold text-white">{{ $emp->name }}</td>
                                <td class="px-4 py-3 text-gray-300">
                                    {{ $emp->position }}
                                    <div class="text-[10px] text-gray-500 uppercase">{{ $emp->department }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5 text-indigo-400">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                        <span class="text-xs font-medium">{{ $emp->branch ? $emp->branch->name : 'Kantor Pusat' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border {{ $emp->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20' }}">
                                        {{ $emp->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-all">
                                        <button @click.stop="viewQRCode({{ json_encode($emp) }})" class="p-2 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors" title="Lihat QR Code"><i data-lucide="qr-code" class="w-4 h-4"></i></button>
                                        <button @click.stop="editEmployee({{ json_encode($emp) }})" class="p-2 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Edit"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                                        <form method="POST" action="{{ route('hrm.employees.destroy', $emp) }}" 
                                            @submit.prevent="$dispatch('global-confirm', { 
                                                title: 'PEMBERHENTIAN KARYAWAN', 
                                                message: 'Yakin ingin menghapus data karyawan {{ addslashes($emp->name) }}? Akses login dan riwayat payroll tetap tersimpan di database.',
                                                actionUrl: '{{ route('hrm.employees.destroy', $emp) }}',
                                                actionMethod: 'DELETE',
                                                confirmText: 'HAPUS'
                                            })" 
                                            @click.stop class="m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-2 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-10 text-gray-500">Belum ada data karyawan</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ATTENDANCE TAB --}}
            <div x-show="activeTab === 'attendance'" x-cloak style="display: none;" class="p-5">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
                    <form id="attendance-filter" action="{{ route('hrm.index') }}" method="GET" class="w-full sm:w-auto">
                        <input type="hidden" name="tab" value="attendance">
                        <div class="relative">
                            <i data-lucide="calendar" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                            <input type="date" name="date" value="{{ $attendanceDate }}" onchange="this.form.submit()" class="w-full sm:w-auto bg-gray-900 border border-gray-700 text-white rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer">
                        </div>
                    </form>
                    <div class="font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-4 py-2 rounded-lg text-sm w-full sm:w-auto text-center">
                        📅 Presensi <span class="font-bold">{{ Carbon\Carbon::parse($attendanceDate)->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                            <tr><th class="px-4 py-3 font-medium">Karyawan</th><th class="px-4 py-3 font-medium">Cabang / Toko</th><th class="px-4 py-3 font-medium">Foto</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Jam Masuk</th><th class="px-4 py-3 font-medium">Jam Keluar</th><th class="px-4 py-3 font-medium">Catatan</th><th class="px-4 py-3 font-medium text-right">Action</th></tr>
                        </thead>
                        <tbody id="attendance-table-body" class="divide-y divide-gray-700/50">
                            @foreach($employees as $emp)
                            @php $att = $attendances->firstWhere('employee_id', $emp->id); @endphp
                            <tr id="att-row-{{ $emp->id }}" @click="viewAttendanceDetail({{ json_encode($emp) }}, {{ json_encode($att) }})" class="hover:bg-indigo-500/10 transition-colors cursor-pointer group">
                                <td class="px-4 py-3">
                                    <div class="font-bold text-white">{{ $emp->name }}</div>
                                    <div class="text-[11px] text-gray-500 font-mono mt-0.5">NIP: {{ $emp->nip }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1.5 text-indigo-400">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-indigo-500/50"></i>
                                        <span class="text-xs font-medium">{{ $emp->branch ? $emp->branch->name : 'Kantor Pusat' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="att-photos flex gap-1">
                                        @if($att && $att->image)
                                            <img src="{{ asset('storage/attendances/' . $att->image) }}" class="att-img-in w-10 h-10 rounded-lg object-cover cursor-pointer border border-gray-700 shadow-sm hover:scale-110 transition-transform" @click.stop="window.open($event.target.src)" title="Foto Masuk">
                                        @endif
                                        @if($att && $att->image_out)
                                            <img src="{{ asset('storage/attendances/' . $att->image_out) }}" class="att-img-out w-10 h-10 rounded-lg object-cover cursor-pointer border border-gray-700 shadow-sm hover:scale-110 transition-transform" @click.stop="window.open($event.target.src)" title="Foto Keluar">
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 att-status">
                                    @if($att)
                                    <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border {{ $att->status === 'present' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($att->status === 'absent' ? 'bg-red-500/10 text-red-500 border-red-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20') }}">
                                        {{ ucfirst($att->status) }}
                                    </span>
                                    @if($att->is_late)
                                    <span class="mt-1 px-1.5 py-0.5 flex w-fit rounded bg-red-500/20 text-red-500 text-[9px] font-black tracking-tighter uppercase border border-red-500/30">Telat</span>
                                    @endif
                                    @else
                                    <span class="text-xs text-gray-500 italic">Belum mencatat</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-300 font-mono att-in">{{ $att->clock_in ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-300 font-mono att-out">{{ $att->clock_out ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-400 text-xs att-note max-w-[150px] truncate" title="{{ $att->note ?? '' }}">{{ $att->note ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button @click.stop="openAttendanceModal({{ json_encode($emp) }}, {{ json_encode($att) }})" class="opacity-0 group-hover:opacity-100 transition-all flex items-center justify-center gap-1.5 px-3 py-1.5 bg-indigo-600/10 text-indigo-400 border border-indigo-500/20 hover:bg-indigo-600 hover:text-white rounded-lg text-xs font-bold ml-auto shadow-sm">
                                        <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i> Log manual
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- LEAVES TAB --}}
            <div x-show="activeTab === 'leaves'" x-cloak style="display: none;" class="p-5">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
                    <h4 class="font-bold text-white flex items-center gap-2"><i data-lucide="calendar" class="w-5 h-5 text-indigo-400"></i> Riwayat Cuti & Izin</h4>
                    <button @click="openModalAddLeave()" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20 w-full sm:w-auto">
                        <i data-lucide="plus" class="w-4 h-4"></i> Catat Cuti/Izin
                    </button>
                </div>
                <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                            <tr><th class="px-4 py-3 font-medium">Tanggal</th><th class="px-4 py-3 font-medium">Karyawan</th><th class="px-4 py-3 font-medium">Cabang / Toko</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Keterangan</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($leaves as $lv)
                            <tr class="hover:bg-indigo-500/10 transition-colors group">
                                <td class="px-4 py-3 font-mono text-gray-300">{{ Carbon\Carbon::parse($lv->date)->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-bold text-white">{{ $lv->employee->name }}</td>
                                <td class="px-4 py-3 text-gray-400">
                                    <div class="flex items-center gap-1 text-[11px] text-indigo-400 font-medium">
                                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                                        {{ $lv->employee->branch ? $lv->employee->branch->name : 'Kantor Pusat' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border {{ $lv->status === 'leave' ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20' }}">
                                        {{ $lv->status === 'leave' ? 'Cuti/Izin' : 'Sakit' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-400 text-xs">{{ $lv->note }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center py-10 text-gray-500">Belum ada data cuti/izin</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAYROLL TAB --}}
            <div x-show="activeTab === 'payroll'" x-cloak style="display: none;" class="p-5">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
                    <form id="payroll-filter" action="{{ route('hrm.index') }}" method="GET" class="flex gap-3 w-full sm:w-auto">
                        <input type="hidden" name="tab" value="payroll">
                        <input type="month" name="month" class="w-full sm:w-auto bg-gray-900 border border-gray-700 text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all cursor-pointer" value="{{ $payrollMonth }}" onchange="this.form.submit()">
                    </form>
                    <form action="{{ route('hrm.payroll.generate') }}" method="POST" class="w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="month" value="{{ $payrollMonth }}">
                        <button type="submit" class="flex w-full sm:w-auto items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20">
                            <i data-lucide="refresh-cw" class="w-4 h-4"></i> Generate Payroll
                        </button>
                    </form>
                </div>
                <div class="overflow-x-auto w-full border border-gray-700/50 rounded-lg">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-900/50 text-gray-400 text-xs uppercase tracking-wider border-b border-gray-700/50">
                            <tr><th class="px-4 py-3 font-medium">Karyawan</th><th class="px-4 py-3 font-medium">Cabang / Toko</th><th class="px-4 py-3 font-medium">Gaji Pokok</th><th class="px-4 py-3 font-medium">Tunjangan</th><th class="px-4 py-3 font-medium">Potongan</th><th class="px-4 py-3 font-medium">Total Bersih</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium text-right">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($payrolls as $pay)
                            <tr @click="viewPayrollDetail({{ json_encode($pay->load('employee')) }})" class="hover:bg-indigo-500/10 transition-colors cursor-pointer group">
                                <td class="px-4 py-3 font-bold text-white">{{ $pay->employee->name }}</td>
                                <td class="px-4 py-3">
                                    <div class="text-[11px] text-gray-500 uppercase font-bold">{{ $pay->employee->branch ? $pay->employee->branch->name : 'Kantor Pusat' }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-300">Rp {{ number_format($pay->basic_salary, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-emerald-400">+ Rp {{ number_format($pay->allowance, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-red-500">- Rp {{ number_format($pay->deduction, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 font-bold text-white text-base">Rp {{ number_format($pay->net_salary, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border {{ $pay->status === 'paid' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20' }}">
                                        {{ $pay->status === 'paid' ? 'Dibayar' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button @click.stop="editPayroll({{ json_encode($pay) }})" class="p-2 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-all opacity-0 group-hover:opacity-100" title="Rincian & Bayar">
                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center py-12 text-gray-500 flex flex-col items-center justify-center gap-2"><i data-lucide="inbox" class="w-8 h-8 text-gray-600"></i> Klik Generate Payroll untuk memulai periode ini</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>



            {{-- SETTINGS TAB --}}
            <div x-show="activeTab === 'settings'" x-cloak style="display: none;" class="p-8">
                <div class="max-w-2xl mx-auto">
                    <div class="mb-8 text-center sm:text-left">
                        <h4 class="text-xl font-bold text-white mb-2 flex items-center justify-center sm:justify-start gap-2">
                            <i data-lucide="settings" class="w-6 h-6 text-indigo-400"></i> Pengaturan HRM & Kebijakan
                        </h4>
                        <p class="text-gray-400 text-sm">Atur jam masuk kantor dan denda keterlambatan otomatis.</p>
                    </div>

                    <form action="{{ route('hrm.settings.update') }}" method="POST" class="space-y-6">
                        @csrf
                        <div class="bg-gray-900/50 p-6 rounded-2xl border border-gray-700/50 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Waktu Masuk Kantor</label>
                                    <div class="relative">
                                        <i data-lucide="clock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500"></i>
                                        <input type="time" name="office_in" value="{{ $settings['office_in'] ?? '08:00' }}" 
                                            class="w-full bg-gray-800 border-2 border-gray-700 focus:border-indigo-500 rounded-xl py-3 pl-10 pr-4 text-white outline-none transition-all">
                                    </div>
                                    <p class="text-[10px] text-gray-500 italic mt-1">Lateness dihitung setelah jam ini.</p>
                                </div>
                                
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Denda Per Terlambat</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-bold">Rp</span>
                                        <input type="number" name="late_deduction" value="{{ $settings['late_deduction'] ?? 10000 }}"
                                            class="w-full bg-gray-800 border-2 border-gray-700 focus:border-indigo-500 rounded-xl py-3 pl-10 pr-4 text-white outline-none transition-all">
                                    </div>
                                    <p class="text-[10px] text-gray-500 italic mt-1">Akan otomatis memotong gaji saat generate payroll.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label class="text-xs font-black text-gray-400 uppercase tracking-widest">Tunjangan Lembur (Per Jam)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-500 font-bold">Rp</span>
                                        <input type="number" name="overtime_per_hour" value="{{ $settings['overtime_per_hour'] ?? 15000 }}" 
                                            class="w-full bg-gray-800 border-2 border-gray-700 focus:border-indigo-500 rounded-xl py-3 pl-10 pr-4 text-white outline-none transition-all">
                                    </div>
                                    <p class="text-[10px] text-gray-500 italic mt-1">Nilai lembur yang dihitung per jam kerja ekstra.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit" class="flex items-center gap-3 px-8 py-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-indigo-600/20 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                <i data-lucide="save" class="w-5 h-5"></i> Simpan Kebijakan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}

    {{-- Modal: Tambah/Edit Karyawan --}}
    <div x-show="modals.employee" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.employee = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2" x-text="empForm.title"></h3>
                <button @click="modals.employee = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="empForm.action" method="POST" class="overflow-y-auto">
                @csrf
                <input type="hidden" name="_method" value="PUT" x-show="empForm.method === 'PUT'" :disabled="empForm.method !== 'PUT'">
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">NIP</label><input type="text" name="nip" x-model="empForm.nip" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Nama</label><input type="text" name="name" x-model="empForm.name" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</label><input type="email" name="email" x-model="empForm.email" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Telepon</label><input type="text" name="phone" x-model="empForm.phone" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Jabatan</label>
                        <select name="position" x-model="empForm.position" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">-- Pilih Jabatan --</option>
                            <option value="Manager">Manager</option>
                            <option value="Supervisor">Supervisor</option>
                            <option value="Staff">Staff</option>
                            <option value="Operator Produksi">Operator Produksi</option>
                            <option value="Sales / Marketing">Sales / Marketing</option>
                            <option value="Admin">Admin</option>
                            <option value="Security">Security</option>
                            <option value="Driver">Driver</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Department</label>
                        <select name="department" x-model="empForm.department" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">-- Pilih Department --</option>
                            <option value="Produksi">Produksi</option>
                            <option value="Marketing">Marketing (Offline)</option>
                            <option value="Digital Marketing">Digital Marketing (Online)</option>
                            <option value="Keuangan">Keuangan</option>
                            <option value="HRD & GA">HRD & GA</option>
                            <option value="Warehouse / Logistik">Warehouse / Logistik</option>
                            <option value="IT Support">IT Support</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cabang / Lokasi Kerja</label>
                        <select name="branch_id" x-model="empForm.branch_id" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">Kantor Pusat</option>
                            @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tgl Bergabung</label><input type="date" name="join_date" x-model="empForm.join_date" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gaji Pokok</label><input type="number" name="basic_salary" x-model="empForm.basic_salary" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tunjangan Default</label><input type="number" name="allowance" x-model="empForm.allowance" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Potongan Default</label><input type="number" name="deduction" x-model="empForm.deduction" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5" x-show="empForm.method === 'PUT'"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</label><select name="status" x-model="empForm.status" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"><option value="active">Aktif</option><option value="inactive">Non-Aktif</option></select></div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="modals.employee = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm shadow-indigo-600/20" x-text="empForm.btnText"></button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Catat Absensi --}}
    <div x-show="modals.attendance" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.attendance = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2 px-2 py-1 bg-gray-700/50 text-gray-200 rounded-lg"><i data-lucide="clipboard-check" class="w-4 h-4 text-emerald-400"></i> Catat Presensi</h3>
                <button type="button" @click="modals.attendance = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="POST" action="{{ route('hrm.attendance.store') }}">
                @csrf
                <input type="hidden" name="employee_id" x-model="attForm.employee_id">
                <input type="hidden" name="date" value="{{ $attendanceDate }}">
                <div class="p-5 flex flex-col gap-4">
                    <div class="bg-gray-900/50 border border-gray-700 rounded-xl p-3 text-center mb-1">
                        <div class="font-bold text-white text-lg" x-text="attForm.employee_name"></div>
                        <div class="text-xs text-indigo-400 font-medium">Tanggal: {{ Carbon\Carbon::parse($attendanceDate)->translatedFormat('d F Y') }}</div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</label>
                        <select name="status" x-model="attForm.status" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="present">Hadir</option><option value="absent">Alpa</option><option value="leave">Izin</option><option value="sick">Sakit</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Jam Masuk</label><input type="time" name="clock_in" x-model="attForm.clock_in" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                        <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Jam Keluar</label><input type="time" name="clock_out" x-model="attForm.clock_out" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Catatan</label>
                        <input type="text" name="note" x-model="attForm.note" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" placeholder="Catatan tambahan...">
                    </div>
                    
                    <div x-show="attForm.image || attForm.image_out" class="flex gap-3 mt-2">
                        <template x-if="attForm.image">
                            <div class="flex-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Foto Masuk</label>
                                <img :src="'/storage/attendances/' + attForm.image" class="w-full h-24 object-cover rounded-lg border border-gray-600">
                            </div>
                        </template>
                        <template x-if="attForm.image_out">
                            <div class="flex-1">
                                <label class="text-[10px] font-bold text-gray-500 uppercase block mb-1">Foto Keluar</label>
                                <img :src="'/storage/attendances/' + attForm.image_out" class="w-full h-24 object-cover rounded-lg border border-gray-600">
                            </div>
                        </template>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="modals.attendance = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">Simpan Presensi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Edit Payroll --}}
    <div x-show="modals.editPayroll" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.editPayroll = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2"><i data-lucide="settings" class="w-5 h-5 text-indigo-400"></i> Pengaturan Slip Gaji</h3>
                <button type="button" @click="modals.editPayroll = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form :action="'/hrm/payroll/' + payForm.id" method="POST">
                @csrf @method('PUT')
                <div class="p-5 flex flex-col gap-4">
                    <div class="bg-gray-900 border border-gray-700 rounded-xl p-4 mb-2 text-center shadow-inner">
                        <div class="font-bold text-white text-lg" x-text="payForm.employee_name"></div>
                        <div class="text-xs text-gray-400 mt-1 uppercase tracking-widest font-mono" x-text="'Periode: ' + payForm.period"></div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gaji Pokok</label>
                        <input type="text" x-model="payForm.basicStr" class="w-full bg-gray-900/50 border border-gray-700 text-gray-400 rounded-lg px-4 py-2.5 text-sm cursor-not-allowed" readonly disabled>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tunjangan (Allowance)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                            <input type="number" name="allowance" x-model="payForm.allowance" class="w-full bg-gray-900 border border-gray-700 text-emerald-400 font-bold rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Potongan (Deduction)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                            <input type="number" name="deduction" x-model="payForm.deduction" class="w-full bg-gray-900 border border-gray-700 text-red-400 font-bold rounded-lg pl-10 pr-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all" required>
                        </div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Status Pembayaran</label>
                        <select name="status" x-model="payForm.status" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="draft">Draft</option><option value="paid">Sudah Dibayar</option>
                        </select>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="modals.editPayroll = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">Update Slip Gaji</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Catat Cuti & Izin --}}
    <div x-show="modals.leaveForm" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.leaveForm = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-md overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2"><i data-lucide="calendar" class="w-5 h-5 text-indigo-400"></i> Pengajuan Cuti & Izin</h3>
                <button type="button" @click="modals.leaveForm = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form action="{{ route('hrm.leave.store') }}" method="POST">
                @csrf
                <div class="p-5 flex flex-col gap-4">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Karyawan</label>
                        <select name="employee_id" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="">Pilih Karyawan</option>
                            @foreach($employees->where('status', 'active') as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Mulai Tgl</label><input type="date" name="start_date" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                        <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sampai Tgl</label><input type="date" name="end_date" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tipe</label>
                        <select name="status" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                            <option value="leave">Izin / Cuti</option>
                            <option value="sick">Sakit</option>
                        </select>
                    </div>
                    
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Keterangan / Alasan</label>
                        <textarea name="note" required rows="3" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></textarea>
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end gap-3">
                    <button type="button" @click="modals.leaveForm = false" class="px-4 py-2 hover:bg-gray-700 text-gray-300 rounded-lg text-sm font-medium transition-colors">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-medium transition-colors shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Detail View --}}
    <div x-show="modals.detail" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[150] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.detail = false" class="bg-gray-800 border border-gray-700 rounded-xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-gray-700/50 flex items-center justify-between">
                <h3 class="font-bold text-white flex items-center gap-2" x-html="detail.title"></h3>
                <button @click="modals.detail = false" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <div class="p-5 overflow-y-auto max-h-[70vh]" x-html="detail.content">
            </div>
            <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-800/50 flex justify-end">
                <button @click="modals.detail = false" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition-colors">Tutup</button>
            </div>
        </div>
    </div>

    {{-- Modal: QR Code Khusus NIP Karyawan --}}
    <div x-show="modals.qrCode" x-cloak style="display: none;" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm z-[160] flex items-center justify-center p-4 transition-opacity">
        <div @click.away="modals.qrCode = false" class="bg-gray-800 border-2 border-indigo-500/50 rounded-2xl shadow-[0_0_50px_-12px_rgba(99,102,241,0.5)] w-full max-w-sm overflow-hidden flex flex-col relative">
            <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-indigo-500"></div>
            
            <div class="px-5 pt-8 pb-2 text-center relative z-10">
                <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-indigo-600 to-purple-600 p-1 mx-auto mb-3 shadow-lg">
                    <div class="w-full h-full bg-gray-900 rounded-full flex items-center justify-center text-white font-bold text-xl" x-text="qrData.initials"></div>
                </div>
                <h3 class="font-bold text-white text-xl mb-0.5" x-text="qrData.name"></h3>
                <p class="text-indigo-400 text-sm font-medium uppercase tracking-wider text-xs" x-text="qrData.position"></p>
                <div class="mt-2 inline-block px-3 py-1 bg-gray-900 border border-gray-700 rounded text-gray-300 font-mono text-sm shadow-inner" x-text="qrData.nip"></div>
            </div>

            <div class="p-6 flex flex-col items-center justify-center bg-white m-5 rounded-xl shadow-inner relative group">
                <div class="absolute inset-0 border-2 border-dashed border-gray-200 rounded-xl pointer-events-none"></div>
                <div class="opacity-0 group-hover:opacity-100 absolute top-2 right-2 transition-opacity z-20">
                    <button @click="downloadQR" class="p-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg shadow-sm" title="Download QR"><i data-lucide="download" class="w-4 h-4"></i></button>
                </div>
                <canvas id="employee-qrcode" class="w-full max-w-[200px] h-auto"></canvas>
            </div>

            <div class="px-5 py-4 border-t border-gray-700/50 bg-gray-900/50 flex justify-center">
                <button @click="modals.qrCode = false" class="px-8 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-full text-sm font-bold transition-all shadow-md hover:shadow-lg focus:ring-2 focus:ring-gray-500 outline-none w-full">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/qrcodegen.js') }}"></script>
<script src="https://js.pusher.com/8.0/pusher.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('hrmData', () => ({
        activeTab: new URLSearchParams(window.location.search).get('tab') || 'employees',
        modals: {
            employee: false,
            attendance: false,
            editPayroll: false,
            detail: false,
            qrCode: false,
            leaveForm: false
        },
        empForm: { action: '', method: '', title: '', btnText: '', id: '', nip: '', name: '', email: '', phone: '', position: '', department: '', branch_id: '', join_date: '', basic_salary: '', allowance: 0, deduction: 0, status: 'active' },
        attForm: { employee_id: '', employee_name: '', status: 'present', clock_in: '08:00', clock_out: '17:00', note: '', image: '', image_out: '' },
        payForm: { id: '', employee_name: '', period: '', basicStr: '', allowance: 0, deduction: 0, status: 'draft' },
        detail: { title: '', content: '' },
        qrData: { nip: '', name: '', position: '', initials: '' },
        
        switchTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
        },
        
        openModalAddEmployee() {
            this.empForm = { action: '{{ route("hrm.employees.store") }}', method: 'POST', title: '👤 Tambah Karyawan', btnText: 'Simpan', id: '', nip: '', name: '', email: '', phone: '', position: '', department: '', branch_id: '', join_date: '', basic_salary: '', allowance: 0, deduction: 0, status: 'active' };
            this.modals.employee = true;
        },
        
        editEmployee(emp) {
            this.empForm = { 
                action: '/hrm/employees/' + emp.id, method: 'PUT', title: '✏️ Edit Karyawan', btnText: 'Update Karyawan',
                id: emp.id, nip: emp.nip, name: emp.name, email: emp.email, phone: emp.phone, position: emp.position, department: emp.department, 
                branch_id: emp.branch_id || '',
                join_date: emp.join_date ? emp.join_date.split('T')[0] : '', basic_salary: emp.basic_salary, allowance: emp.allowance || 0, deduction: emp.deduction || 0, status: emp.status 
            };
            this.modals.employee = true;
        },

        openModalAddLeave() {
            this.modals.leaveForm = true;
        },
        
        openAttendanceModal(emp, att) {
            this.attForm.employee_id = emp.id;
            this.attForm.employee_name = emp.name;
            if (att) {
                this.attForm.status = att.status;
                this.attForm.clock_in = att.clock_in ? att.clock_in.substring(0, 5) : '';
                this.attForm.clock_out = att.clock_out ? att.clock_out.substring(0, 5) : '';
                this.attForm.note = att.note || '';
                this.attForm.image = att.image || '';
                this.attForm.image_out = att.image_out || '';
            } else {
                this.attForm.status = 'present';
                this.attForm.clock_in = '08:00';
                this.attForm.clock_out = '17:00';
                this.attForm.note = '';
                this.attForm.image = '';
                this.attForm.image_out = '';
            }
            this.modals.attendance = true;
        },
        
        editPayroll(pay) {
            this.payForm.id = pay.id;
            this.payForm.employee_name = pay.employee.name;
            this.payForm.period = pay.period_month + '/' + pay.period_year;
            this.payForm.basicStr = 'Rp ' + Number(pay.basic_salary).toLocaleString('id-ID');
            this.payForm.allowance = pay.allowance;
            this.payForm.deduction = pay.deduction;
            this.payForm.status = pay.status;
            this.modals.editPayroll = true;
        },
        
        viewEmployeeDetail(emp) {
            this.detail.title = '👤 Profil Karyawan';
            const initials = emp.name.substring(0, 1).toUpperCase();
            const joinDate = new Date(emp.join_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
            
            this.detail.content = `
                <div class="text-center mb-6">
                    <div class="w-20 h-20 rounded-full bg-indigo-500 text-white flex items-center justify-center text-3xl font-bold mx-auto mb-3 shadow-lg shadow-indigo-500/30">${initials}</div>
                    <h4 class="text-xl font-bold text-white mb-1">${emp.name}</h4>
                    <div class="text-indigo-400 text-sm font-medium"><span class="font-mono text-gray-400 bg-gray-900 px-1.5 py-0.5 rounded text-xs mr-2">${emp.nip}</span> ${emp.position}</div>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Email</div>
                        <div class="text-gray-300 text-sm break-all">${emp.email}</div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Telepon</div>
                        <div class="text-gray-300 text-sm">${emp.phone}</div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Departemen</div>
                        <div class="text-gray-300 text-sm">${emp.department}</div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tgl Bergabung / Lokasi</div>
                        <div class="text-gray-300 text-sm">${joinDate}</div>
                        <div class="text-indigo-400 text-xs mt-1 font-bold">${emp.branch ? emp.branch.name : 'Kantor Pusat'}</div>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-4 rounded-xl sm:col-span-2">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Gaji Pokok</div>
                        <div class="text-emerald-400 font-bold text-lg">Rp ${Number(emp.basic_salary).toLocaleString('id-ID')}</div>
                    </div>
                </div>
            `;
            this.modals.detail = true;
        },
        
        viewAttendanceDetail(emp, att) {
            this.detail.title = '📅 Detail Presensi';
            const date = '{{ Carbon\Carbon::parse($attendanceDate)->translatedFormat("d F Y") }}';
            
            let attContent = att ? `
                <div class="grid grid-cols-2 gap-3 mt-4">
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Status</div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold border ${att.status === 'present' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20'}">${att.status.toUpperCase()}</span>
                    </div>
                    <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Jam Kerja</div>
                        <div class="font-mono text-gray-300 text-sm">${att.clock_in || '--:--'} - ${att.clock_out || '--:--'}</div>
                    </div>
                </div>
                <div class="bg-gray-900/50 border border-gray-700/50 p-3 rounded-xl mt-3">
                    <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Catatan Karyawan</div>
                    <div class="text-gray-300 text-sm italic">${att.note || '-'}</div>
                </div>
                ${att.image || att.image_out ? `
                    <div class="mt-4">
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2">Bukti Foto</div>
                        <div class="grid grid-cols-2 gap-3">
                            ${att.image ? `<img src="/storage/attendances/${att.image}" class="w-full h-32 object-cover rounded-xl border border-gray-600 shadow-sm cursor-pointer" onclick="window.open(this.src)">` : ''}
                            ${att.image_out ? `<img src="/storage/attendances/${att.image_out}" class="w-full h-32 object-cover rounded-xl border border-gray-600 shadow-sm cursor-pointer" onclick="window.open(this.src)">` : ''}
                        </div>
                    </div>
                ` : ''}
            ` : `
                <div class="text-center py-8 bg-gray-900/50 rounded-xl mt-4 border border-dashed border-gray-700 flex flex-col items-center justify-center">
                    <div class="text-gray-500 mb-2"><i data-lucide="calendar-x" class="w-8 h-8"></i></div>
                    <div class="text-gray-400 text-sm">Karyawan belum mencatat presensi hari ini</div>
                </div>
            `;

            this.detail.content = `
                <div class="flex items-center gap-4 bg-gray-800 p-4 rounded-xl border border-gray-700 shadow-sm">
                    <div class="w-12 h-12 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-xl">${emp.name.substring(0,1)}</div>
                    <div>
                        <div class="font-bold text-white text-lg">${emp.name}</div>
                        <div class="text-xs text-indigo-400">${date}</div>
                    </div>
                </div>
                ${attContent}
            `;
            this.modals.detail = true;
        },
        
        viewPayrollDetail(pay) {
            this.detail.title = '💸 Rincian Slip Gaji';
            const net = Number(pay.net_salary).toLocaleString('id-ID');
            const basic = Number(pay.basic_salary).toLocaleString('id-ID');
            const allowance = Number(pay.allowance).toLocaleString('id-ID');
            const deduction = Number(pay.deduction).toLocaleString('id-ID');

            this.detail.content = `
                <div class="text-center mb-6 pb-5 border-b border-gray-700/50">
                    <div class="text-xs text-gray-400 uppercase tracking-widest font-mono mb-2">Periode: ${pay.period_month}/${pay.period_year}</div>
                    <div class="text-xl font-bold text-white">${pay.employee.name}</div>
                </div>

                <div class="space-y-3 bg-gray-900/50 border border-gray-700/50 p-5 rounded-xl">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-400">Gaji Pokok</span>
                        <span class="font-bold text-white font-mono">Rp ${basic}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-400">Tunjangan</span>
                        <span class="font-bold text-emerald-400 font-mono">+ Rp ${allowance}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm pb-3 border-b border-dashed border-gray-700">
                        <span class="text-gray-400">Potongan</span>
                        <span class="font-bold text-red-500 font-mono">- Rp ${deduction}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2">
                        <span class="font-bold text-white">Total Bersih</span>
                        <span class="font-black text-indigo-400 text-xl font-mono">Rp ${net}</span>
                    </div>
                </div>

                <div class="mt-5 p-4 rounded-xl bg-gray-800 border border-gray-700 flex flex-col items-center justify-center gap-2">
                    <div class="text-[10px] text-gray-500 uppercase tracking-wider font-bold">Status Pembayaran</div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border ${pay.status === 'paid' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20'}">${pay.status.toUpperCase()}</span>
                </div>
            `;
            this.modals.detail = true;
        },

        viewQRCode(emp) {
            this.qrData = {
                nip: emp.nip,
                name: emp.name,
                position: emp.position,
                initials: emp.name.substring(0, 2).toUpperCase()
            };
            
            this.modals.qrCode = true;

            // Render QR shortly after modal opens so canvas is visible in DOM
            this.$nextTick(() => {
                try {
                    if (typeof qrcodegen === 'undefined') {
                        throw new Error("Pustaka QR Code (Nayuki) belum termuat.");
                    }

                    const canvas = document.getElementById("employee-qrcode");
                    if (!canvas) {
                        throw new Error("Elemen canvas tidak ditemukan.");
                    }

                    // Generate the QR Code
                    const qr = qrcodegen.QrCode.encodeText(this.qrData.nip, qrcodegen.QrCode.Ecc.MEDIUM);
                    
                    // Use the library's built-in drawCanvas method
                    // Parameters: scale, border, canvas
                    qr.drawCanvas(8, 2, canvas);
                    
                    // Refresh lucide icons in the modal if any
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                } catch (err) {
                    console.error("Gagal membuat QR Code:", err.message);
                }
            });
        },

        downloadQR() {
            const canvas = document.getElementById("employee-qrcode");
            const link = document.createElement('a');
            link.download = `QR_${this.qrData.nip}.png`;
            link.href = canvas.toDataURL("image/png");
            link.click();
        }
    }));
});
</script>

@if(config('broadcasting.connections.pusher.key'))
<script>
// Pusher Real-time Events Synchronization
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Pusher === 'undefined') {
        console.warn("Pusher library not detected. Real-time sync disabled.");
        return;
    }

    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
        forceTLS: true
    });

    const channel = pusher.subscribe('attendance');
    channel.bind('App\\Events\\AttendanceRecorded', function(data) {
        const att = data.attendance;
        const empId = att.employee_id;
        const employee = att.employee || { name: 'Karyawan' };
        
        // 1. Sync HRM Table Row
        const row = document.getElementById('att-row-' + empId);
        // Normalize dates for comparison (att.date might be ISO string)
        const attDateNormalized = att.date.includes('T') ? att.date.split('T')[0] : att.date;
        
        if (row && '{{ $attendanceDate }}' === attDateNormalized) {
            const statusBadge = att.status === 'present' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : (att.status === 'absent' ? 'bg-red-500/10 text-red-500 border-red-500/20' : 'bg-amber-500/10 text-amber-500 border-amber-500/20');
            let statusHtml = `<span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border ${statusBadge}">${att.status.charAt(0).toUpperCase() + att.status.slice(1)}</span>`;
            if (att.is_late) {
                statusHtml += `<span class="mt-1 px-1.5 py-0.5 flex w-fit rounded bg-red-500/20 text-red-500 text-[9px] font-black tracking-tighter uppercase border border-red-500/30">Telat</span>`;
            }
            row.querySelector('.att-status').innerHTML = statusHtml;
            row.querySelector('.att-in').textContent = att.clock_in ? att.clock_in.substring(0, 5) : '-';
            row.querySelector('.att-out').textContent = att.clock_out ? att.clock_out.substring(0, 5) : '-';
            
            const noteEl = row.querySelector('.att-note');
            if (noteEl) noteEl.innerHTML = att.note || '-';
            
            const photoEl = row.querySelector('.att-photos');
            if (photoEl) {
                let photosHtml = '';
                if (att.image) photosHtml += `<img src="/storage/attendances/${att.image}" class="w-10 h-10 rounded-lg object-cover cursor-pointer border border-gray-700 shadow-sm hover:scale-110 transition-transform" onclick="window.open(this.src)" title="Foto Masuk"> `;
                if (att.image_out) photosHtml += `<img src="/storage/attendances/${att.image_out}" class="w-10 h-10 rounded-lg object-cover cursor-pointer border border-gray-700 shadow-sm hover:scale-110 transition-transform" onclick="window.open(this.src)" title="Foto Keluar">`;
                photoEl.innerHTML = photosHtml;
            }
            
            row.classList.add('bg-indigo-500/20', 'transition-all');
            setTimeout(() => row.classList.remove('bg-indigo-500/20'), 3000);
        }

        // 2. Sync Global Stats Counter
        const todayStr = new Date().toISOString().split('T')[0];
        if (attDateNormalized === todayStr && !att.clock_out) {
            const statEl = document.getElementById('stat-present-today');
            if (statEl) {
                let current = parseInt(statEl.textContent.replace(/\D/g,'')) || 0;
                statEl.textContent = (current + 1).toLocaleString();
                
                const card = document.getElementById('present-card');
                if (card) {
                    card.classList.add('ring-2', 'ring-amber-500', 'bg-amber-500/10');
                    setTimeout(() => card.classList.remove('ring-2', 'ring-amber-500', 'bg-amber-500/10'), 4000);
                }
            }
        }
    });
});
</script>
@endif
@endpush

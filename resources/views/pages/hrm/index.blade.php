@extends('layouts.app')

@section('title', 'HRM & Payroll')
@section('breadcrumb', 'HRM & Payroll')

@section('content')
<div id="hrm-container" x-data="hrmData()">

    @if(session('success'))
    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl px-4 py-3 mb-6 text-emerald-400 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 shrink-0"></i> 
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    
    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-4"><i data-lucide="users" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $stats['totalEmployees'] }}</div>
            <div class="text-gray-400 text-sm">Total Karyawan</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4"><i data-lucide="user-check" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1">{{ $stats['activeEmployees'] }}</div>
            <div class="text-gray-400 text-sm">Karyawan Aktif</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center mb-4"><i data-lucide="calendar-check" class="w-5 h-5"></i></div>
            <div class="text-3xl font-bold text-white mb-1" id="stat-present-today">{{ $stats['presentToday'] }}</div>
            <div class="text-gray-400 text-sm">Hadir Hari Ini</div>
        </div>
        <div class="bg-gray-800 border border-gray-700/50 rounded-xl p-5 shadow-sm">
            <div class="w-10 h-10 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center mb-4"><i data-lucide="wallet" class="w-5 h-5"></i></div>
            <div class="text-2xl font-bold text-white mb-1 truncate" title="Rp {{ number_format($stats['payrollTotal'], 0, ',', '.') }}">Rp {{ number_format($stats['payrollTotal'], 0, ',', '.') }}</div>
            <div class="text-gray-400 text-sm">Total Gaji Bulan Ini</div>
        </div>
    </div>

    <div class="bg-gray-800 border border-gray-700/50 rounded-xl shadow-sm overflow-hidden flex flex-col">
        {{-- Tabs Navigation --}}
        <div class="flex overflow-x-auto border-b border-gray-700/50 hide-scrollbar bg-gray-900/20">
            <button @click="switchTab('employees')" :class="activeTab === 'employees' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">Daftar Karyawan</button>
            <button @click="switchTab('attendance')" :class="activeTab === 'attendance' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">Presensi Harian</button>
            <button @click="switchTab('payroll')" :class="activeTab === 'payroll' ? 'text-indigo-400 border-b-2 border-indigo-500 bg-gray-800' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'" class="whitespace-nowrap px-6 py-4 text-sm font-medium transition-colors">Payroll / Gaji</button>
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
                            <tr><th class="px-4 py-3 font-medium">NIP</th><th class="px-4 py-3 font-medium">Nama</th><th class="px-4 py-3 font-medium">Jabatan</th><th class="px-4 py-3 font-medium">Departemen</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium text-right">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($employees as $emp)
                            <tr @click="viewEmployeeDetail({{ json_encode($emp) }})" class="hover:bg-indigo-500/10 transition-colors cursor-pointer group">
                                <td class="px-4 py-3"><span class="font-mono text-gray-400 bg-gray-900 px-2 py-1 rounded text-xs">{{ $emp->nip }}</span></td>
                                <td class="px-4 py-3 font-bold text-white">{{ $emp->name }}</td>
                                <td class="px-4 py-3 text-gray-300">{{ $emp->position }}</td>
                                <td class="px-4 py-3 text-gray-400">{{ $emp->department }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border {{ $emp->status === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-red-500/10 text-red-500 border-red-500/20' }}">
                                        {{ $emp->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click.stop="viewQRCode({{ json_encode($emp) }})" class="p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-700 transition-colors" title="Lihat QR Code"><i data-lucide="qr-code" class="w-4 h-4"></i></button>
                                        <button @click.stop="editEmployee({{ json_encode($emp) }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Edit"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                                        <form method="POST" action="{{ route('hrm.employees.destroy', $emp) }}" @submit.stop="return confirm('Hapus karyawan ini?')" @click.stop class="m-0">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
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
                            <tr><th class="px-4 py-3 font-medium">Karyawan</th><th class="px-4 py-3 font-medium">Foto</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium">Jam Masuk</th><th class="px-4 py-3 font-medium">Jam Keluar</th><th class="px-4 py-3 font-medium">Catatan</th><th class="px-4 py-3 font-medium text-right">Action</th></tr>
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
                                    @else
                                    <span class="text-xs text-gray-500 italic">Belum mencatat</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-300 font-mono att-in">{{ $att->clock_in ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-300 font-mono att-out">{{ $att->clock_out ?? '-' }}</td>
                                <td class="px-4 py-3 text-gray-400 text-xs att-note max-w-[150px] truncate" title="{{ $att->note ?? '' }}">{{ $att->note ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button @click.stop="openAttendanceModal({{ json_encode($emp) }}, {{ json_encode($att) }})" class="btn-log flex items-center justify-center gap-1.5 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-xs font-medium transition-colors ml-auto">
                                        <i data-lucide="clipboard-check" class="w-3.5 h-3.5"></i> Log
                                    </button>
                                </td>
                            </tr>
                            @endforeach
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
                            <tr><th class="px-4 py-3 font-medium">Karyawan</th><th class="px-4 py-3 font-medium">Gaji Pokok</th><th class="px-4 py-3 font-medium">Tunjangan</th><th class="px-4 py-3 font-medium">Potongan</th><th class="px-4 py-3 font-medium">Total Bersih</th><th class="px-4 py-3 font-medium">Status</th><th class="px-4 py-3 font-medium text-right">Aksi</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700/50">
                            @forelse($payrolls as $pay)
                            <tr @click="viewPayrollDetail({{ json_encode($pay->load('employee')) }})" class="hover:bg-indigo-500/10 transition-colors cursor-pointer group">
                                <td class="px-4 py-3 font-bold text-white">{{ $pay->employee->name }}</td>
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
                                    <button @click.stop="editPayroll({{ json_encode($pay) }})" class="p-1.5 rounded-lg text-gray-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors opacity-0 group-hover:opacity-100" title="Edit">
                                        <i data-lucide="settings" class="w-4 h-4"></i>
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
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Jabatan</label><input type="text" name="position" x-model="empForm.position" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Department</label><input type="text" name="department" x-model="empForm.department" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tgl Bergabung</label><input type="date" name="join_date" x-model="empForm.join_date" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
                    <div class="space-y-1.5"><label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gaji Pokok</label><input type="number" name="basic_salary" x-model="empForm.basic_salary" required class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition-all"></div>
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
            qrCode: false
        },
        empForm: { action: '', method: '', title: '', btnText: '', id: '', nip: '', name: '', email: '', phone: '', position: '', department: '', join_date: '', basic_salary: '', status: 'active' },
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
            this.empForm = { action: '{{ route("hrm.employees.store") }}', method: 'POST', title: '👤 Tambah Karyawan', btnText: 'Simpan', id: '', nip: '', name: '', email: '', phone: '', position: '', department: '', join_date: '', basic_salary: '', status: 'active' };
            this.modals.employee = true;
        },
        
        editEmployee(emp) {
            this.empForm = { 
                action: '/hrm/employees/' + emp.id, method: 'PUT', title: '✏️ Edit Karyawan', btnText: 'Update Karyawan',
                id: emp.id, nip: emp.nip, name: emp.name, email: emp.email, phone: emp.phone, position: emp.position, department: emp.department, 
                join_date: emp.join_date ? emp.join_date.split('T')[0] : '', basic_salary: emp.basic_salary, status: emp.status 
            };
            this.modals.employee = true;
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
                        <div class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1">Tgl Bergabung</div>
                        <div class="text-gray-300 text-sm">${joinDate}</div>
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
            
            row.querySelector('.att-status').innerHTML = `<span class="px-2 py-1 flex w-fit rounded-full text-[10px] font-bold border ${statusBadge}">${att.status.charAt(0).toUpperCase() + att.status.slice(1)}</span>`;
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
        if (att.date === todayStr && !att.clock_out) {
            const statEl = document.getElementById('stat-present-today');
            if (statEl) {
                let current = parseInt(statEl.textContent) || 0;
                statEl.textContent = current + 1;
                const card = statEl.parentElement;
                card.classList.add('ring-2', 'ring-amber-500', 'bg-amber-500/5');
                setTimeout(() => card.classList.remove('ring-2', 'ring-amber-500', 'bg-amber-500/5'), 4000);
            }
        }
    });
});
</script>
@endif
@endpush

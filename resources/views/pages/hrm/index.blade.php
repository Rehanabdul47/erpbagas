@extends('layouts.app')

@section('title', 'HRM & Payroll')
@section('breadcrumb', 'HRM & Payroll')

@section('content')
@if(session('success'))
<div class="alert alert-success"><i data-lucide="check-circle" style="width:18px;height:18px"></i> {{ session('success') }}</div>
@endif

{{-- Stats --}}
<div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px">
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--primary-500)"><i data-lucide="users"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $stats['totalEmployees'] }}</span><span class="stat-label">Total Karyawan</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--success-500)"><i data-lucide="user-check"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $stats['activeEmployees'] }}</span><span class="stat-label">Karyawan Aktif</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--warning-500)"><i data-lucide="calendar-check"></i></div>
        <div class="stat-info"><span class="stat-value">{{ $stats['presentToday'] }}</span><span class="stat-label">Hadir Hari Ini</span></div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon" style="background:var(--danger-500)"><i data-lucide="wallet"></i></div>
        <div class="stat-info"><span class="stat-value">Rp {{ number_format($stats['payrollTotal'], 0, ',', '.') }}</span><span class="stat-label">Total Gaji Bulan Ini</span></div>
    </div>
</div>

<div class="card" style="padding:0">
    {{-- Tabs Navigation --}}
    <div class="tabs-header" style="display:flex;border-bottom:1px solid var(--border-color);padding:0 20px">
        <button class="tab-btn {{ $activeTab === 'employees' ? 'active' : '' }}" onclick="switchTab('employees')">Daftar Karyawan</button>
        <button class="tab-btn {{ $activeTab === 'attendance' ? 'active' : '' }}" onclick="switchTab('attendance')">Presensi Harian</button>
        <button class="tab-btn {{ $activeTab === 'payroll' ? 'active' : '' }}" onclick="switchTab('payroll')">Payroll / Gaji</button>
    </div>

    {{-- tab-content --}}
    <div style="padding:20px">
        {{-- EMPLOYEES TAB --}}
        <div id="tab-employees" class="tab-pane {{ $activeTab === 'employees' ? '' : 'hidden' }}">
            <div style="display:flex;justify-content:space-between;margin-bottom:16px;align-items:center">
                <h4 style="margin:0">👥 Managemen Karyawan</h4>
                <button class="btn btn-primary btn-sm" onclick="openModal('modal-add-employee')"><i data-lucide="plus"></i> Tambah Karyawan</button>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Departemen</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $emp)
                        <tr class="clickable-row employee-row" style="cursor:pointer" data-employee='@json($emp)'>
                            <td><code>{{ $emp->nip }}</code></td>
                            <td style="font-weight:600">{{ $emp->name }}</td>
                            <td>{{ $emp->position }}</td>
                            <td>{{ $emp->department }}</td>
                            <td>
                                <span class="badge {{ $emp->status === 'active' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $emp->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px">
                                    <button class="btn btn-ghost btn-xs" title="Edit" onclick='event.stopPropagation(); editEmployee(@json($emp))'><i data-lucide="edit-2" style="width:14px"></i></button>
                                    <form method="POST" action="{{ route('hrm.employees.destroy', $emp) }}" onsubmit="event.stopPropagation(); return confirm('Hapus karyawan ini?')" onclick="event.stopPropagation()">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs text-danger"><i data-lucide="trash-2" style="width:14px"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;padding:30px">Belum ada data karyawan</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ATTENDANCE TAB --}}
        <div id="tab-attendance" class="tab-pane {{ $activeTab === 'attendance' ? '' : 'hidden' }}">
            <div style="display:flex;justify-content:space-between;margin-bottom:16px;align-items:center">
                <form id="attendance-filter" action="{{ route('hrm.index') }}" method="GET">
                    <input type="hidden" name="tab" value="attendance">
                    <input type="date" name="date" class="form-control" value="{{ $attendanceDate }}" onchange="this.form.submit()">
                </form>
                <div style="font-weight:500;color:var(--text-secondary)">📅 Presensi Tanggal {{ Carbon\Carbon::parse($attendanceDate)->format('d M Y') }}</div>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>Karyawan</th><th>Foto</th><th>Status</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Catatan</th><th>Update</th></tr>
                    </thead>
                    <tbody id="attendance-table-body">
                        @foreach($employees as $emp)
                        @php $att = $attendances->firstWhere('employee_id', $emp->id); @endphp
                        <tr id="att-row-{{ $emp->id }}" class="clickable-row attendance-row" style="cursor:pointer" data-emp='@json($emp)' data-att='@json($att)'>
                            <td>
                                <div style="font-weight:500">{{ $emp->name }}</div>
                                <div style="font-size:12px;color:var(--text-tertiary)">NIP: {{ $emp->nip }}</div>
                            </td>
                            <td class="att-photos">
                                @if($att && $att->image)
                                    <img src="{{ asset('storage/attendances/' . $att->image) }}" class="att-img-in" style="width:40px;height:40px;border-radius:4px;object-fit:cover;cursor:pointer" onclick="event.stopPropagation(); window.open(this.src)" title="Foto Masuk">
                                @endif
                                @if($att && $att->image_out)
                                    <img src="{{ asset('storage/attendances/' . $att->image_out) }}" class="att-img-out" style="width:40px;height:40px;border-radius:4px;object-fit:cover;cursor:pointer;margin-left:4px" onclick="event.stopPropagation(); window.open(this.src)" title="Foto Keluar">
                                @endif
                            </td>
                            <td class="att-status">
                                @if($att)
                                <span class="badge {{ $att->status === 'present' ? 'badge-success' : ($att->status === 'absent' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ ucfirst($att->status) }}
                                </span>
                                @else
                                <span style="font-size:12px;color:var(--text-tertiary)">Belum mencatat</span>
                                @endif
                            </td>
                            <td class="att-in">{{ $att->clock_in ?? '-' }}</td>
                            <td class="att-out">{{ $att->clock_out ?? '-' }}</td>
                            <td class="att-note"><span style="font-size:12px">{{ $att->note ?? '-' }}</span></td>
                            <td>
                                <button class="btn btn-ghost btn-xs btn-log" onclick='event.stopPropagation(); openAttendanceModal(@json($emp), @json($att))'><i data-lucide="clipboard-check" style="width:14px"></i> Log</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PAYROLL TAB --}}
        <div id="tab-payroll" class="tab-pane {{ $activeTab === 'payroll' ? '' : 'hidden' }}">
            <div style="display:flex;justify-content:space-between;margin-bottom:16px;align-items:center">
                <form id="payroll-filter" action="{{ route('hrm.index') }}" method="GET" style="display:flex;gap:10px">
                    <input type="hidden" name="tab" value="payroll">
                    <input type="month" name="month" class="form-control" value="{{ $payrollMonth }}" onchange="this.form.submit()">
                </form>
                <form action="{{ route('hrm.payroll.generate') }}" method="POST">
                    @csrf
                    <input type="hidden" name="month" value="{{ $payrollMonth }}">
                    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="refresh-cw"></i> Generate Payroll</button>
                </form>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>Karyawan</th><th>Gaji Pokok</th><th>Tunjangan</th><th>Potongan</th><th>Total Bersih</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse($payrolls as $pay)
                        <tr class="clickable-row payroll-row" style="cursor:pointer" data-payroll='@json($pay->load("employee"))'>
                            <td>{{ $pay->employee->name }}</td>
                            <td>Rp {{ number_format($pay->basic_salary, 0, ',', '.') }}</td>
                            <td class="text-success">+ Rp {{ number_format($pay->allowance, 0, ',', '.') }}</td>
                            <td class="text-danger">- Rp {{ number_format($pay->deduction, 0, ',', '.') }}</td>
                            <td style="font-weight:600">Rp {{ number_format($pay->net_salary, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge {{ $pay->status === 'paid' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $pay->status === 'paid' ? 'Dibayar' : 'Draft' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-ghost btn-xs" onclick='event.stopPropagation(); editPayroll(@json($pay))'><i data-lucide="settings" style="width:14px"></i> Edit</button>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center;padding:30px">Klik Generate Payroll untuk memulai periode ini</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}

{{-- Modal: Tambah/Edit Karyawan --}}
<div id="modal-employee" class="modal-overlay hidden">
    <div class="modal" style="max-width:600px">
        <div class="modal-header"><h3 id="employee-modal-title">👤 Data Karyawan</h3><button class="btn-icon" onclick="closeModal('modal-employee')"><i data-lucide="x"></i></button></div>
        <form id="employee-form" method="POST" action="">
            @csrf
            <div id="employee-method"></div>
            <div class="modal-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div class="form-group"><label>NIP</label><input type="text" name="nip" id="emp-nip" class="form-control" required></div>
                <div class="form-group"><label>Nama</label><input type="text" name="name" id="emp-name" class="form-control" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" id="emp-email" class="form-control" required></div>
                <div class="form-group"><label>Telepon</label><input type="text" name="phone" id="emp-phone" class="form-control" required></div>
                <div class="form-group"><label>Jabatan</label><input type="text" name="position" id="emp-position" class="form-control" required></div>
                <div class="form-group"><label>Department</label><input type="text" name="department" id="emp-department" class="form-control" required></div>
                <div class="form-group"><label>Tgl Bergabung</label><input type="date" name="join_date" id="emp-join_date" class="form-control" required></div>
                <div class="form-group"><label>Gaji Pokok</label><input type="number" name="basic_salary" id="emp-basic_salary" class="form-control" required></div>
                <div class="form-group" id="emp-status-group" style="display:none"><label>Status</label><select name="status" id="emp-status" class="form-control"><option value="active">Aktif</option><option value="inactive">Non-Aktif</option></select></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary" id="emp-submit-btn">Simpan</button></div>
        </form>
    </div>
</div>

{{-- Modal: Catat Absensi --}}
<div id="modal-attendance" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header"><h3>📒 Catat Presensi</h3><button class="btn-icon" onclick="closeModal('modal-attendance')"><i data-lucide="x"></i></button></div>
        <form method="POST" action="{{ route('hrm.attendance.store') }}">
            @csrf
            <input type="hidden" name="employee_id" id="att-emp-id">
            <input type="hidden" name="date" value="{{ $attendanceDate }}">
            <div class="modal-body">
                <div style="font-weight:600;margin-bottom:12px" id="att-emp-name">Nama Karyawan</div>
                <div class="form-group"><label>Status</label><select name="status" id="att-status" class="form-control"><option value="present">Hadir</option><option value="absent">Alpa</option><option value="leave">Izin</option><option value="sick">Sakit</option></select></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group"><label>Jam Masuk</label><input type="time" name="clock_in" id="att-in" class="form-control"></div>
                    <div class="form-group"><label>Jam Keluar</label><input type="time" name="clock_out" id="att-out" class="form-control"></div>
                </div>
                <div class="form-group"><label>Catatan</label><input type="text" name="note" id="att-note" class="form-control"></div>
                <div id="att-photo-preview" style="margin-top:12px;display:flex;gap:12px"></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Simpan Presensi</button></div>
        </form>
    </div>
</div>

{{-- Modal: Edit Payroll --}}
<div id="modal-edit-payroll" class="modal-overlay hidden">
    <div class="modal">
        <div class="modal-header"><h3>💸 Pengaturan Slip Gaji</h3><button class="btn-icon" onclick="closeModal('modal-edit-payroll')"><i data-lucide="x"></i></button></div>
        <form id="payroll-form" method="POST" action="">
            @csrf @method('PUT')
            <div class="modal-body">
                <div style="font-weight:600;margin-bottom:4px" id="pay-emp-name">Nama Karyawan</div>
                <div style="font-size:14px;color:var(--text-tertiary);margin-bottom:16px" id="pay-period">Periode</div>
                <div class="form-group"><label>Gaji Pokok</label><input type="text" id="pay-basic" class="form-control" readonly disabled></div>
                <div class="form-group"><label>Tunjangan (Allowance)</label><input type="number" name="allowance" id="pay-allowance" class="form-control" required></div>
                <div class="form-group"><label>Potongan (Deduction)</label><input type="number" name="deduction" id="pay-deduction" class="form-control" required></div>
                <div class="form-group"><label>Status Pembayaran</label><select name="status" id="pay-status" class="form-control"><option value="draft">Draft</option><option value="paid">Sudah Dibayar</option></select></div>
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Update Slip Gaji</button></div>
        </form>
    </div>
</div>

</div>

{{-- Modal: Detail View --}}
<div id="modal-hrm-detail" class="modal-overlay hidden" onclick="if(event.target===this)closeModal('modal-hrm-detail')">
    <div class="modal" style="max-width:550px">
        <div class="modal-header">
            <h3 id="hrm-detail-title">Detail</h3>
            <button class="btn-icon" onclick="closeModal('modal-hrm-detail')"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" id="hrm-detail-content">
            {{-- Content will be injected by JS --}}
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-hrm-detail')">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function switchTab(tabId) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.location.href = url.href;
}

document.addEventListener('DOMContentLoaded', function() {
    // Employee Row Click
    document.querySelectorAll('.employee-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.closest('button') || e.target.closest('form')) return;
            const emp = JSON.parse(this.getAttribute('data-employee'));
            viewEmployeeDetail(emp);
        });
    });

    // Attendance Row Click
    document.querySelectorAll('.attendance-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.closest('button') || e.target.closest('img')) return;
            const emp = JSON.parse(this.getAttribute('data-emp'));
            const att = JSON.parse(this.getAttribute('data-att'));
            viewAttendanceDetail(emp, att);
        });
    });

    // Payroll Row Click
    document.querySelectorAll('.payroll-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.closest('button')) return;
            const pay = JSON.parse(this.getAttribute('data-payroll'));
            viewPayrollDetail(pay);
        });
    });
});

function viewEmployeeDetail(emp) {
    document.getElementById('hrm-detail-title').innerText = '👤 Profil Karyawan';
    const initials = emp.name.substring(0, 1).toUpperCase();
    const joinDate = new Date(emp.join_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    
    document.getElementById('hrm-detail-content').innerHTML = `
        <div style="text-align:center;margin-bottom:24px">
            <div style="width:70px;height:70px;border-radius:50%;background:var(--primary-500);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:24px;margin:0 auto 12px;box-shadow:0 8px 15px -3px rgba(var(--primary-rgb), 0.3)">
                ${initials}
            </div>
            <h4 style="font-size:18px;margin:0;font-weight:700;color:var(--text-primary)">${emp.name}</h4>
            <div style="color:var(--text-secondary);font-size:14px;margin-top:2px">${emp.nip} • ${emp.position}</div>
        </div>
        
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value" style="font-size:13px">${emp.email}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Telepon</div>
                <div class="detail-value">${emp.phone}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Departemen</div>
                <div class="detail-value">${emp.department}</div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Tgl Bergabung</div>
                <div class="detail-value">${joinDate}</div>
            </div>
            <div class="detail-item" style="grid-column: span 2">
                <div class="detail-label">Gaji Pokok</div>
                <div class="detail-value" style="color:var(--success-600)">Rp ${Number(emp.basic_salary).toLocaleString('id-ID')}</div>
            </div>
        </div>
    `;
    openModal('modal-hrm-detail');
}

function viewAttendanceDetail(emp, att) {
    document.getElementById('hrm-detail-title').innerText = '📅 Detail Presensi';
    const date = '{{ Carbon\Carbon::parse($attendanceDate)->format("d F Y") }}';
    
    let attContent = att ? `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:16px">
            <div class="detail-item">
                <div class="detail-label">Status</div>
                <div class="detail-value"><span class="badge ${att.status === 'present' ? 'badge-success' : 'badge-danger'}">${att.status.toUpperCase()}</span></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Jam Kerja</div>
                <div class="detail-value">${att.clock_in || '--:--'} - ${att.clock_out || '--:--'}</div>
            </div>
        </div>
        <div class="detail-item" style="margin-top:12px">
            <div class="detail-label">Catatan Karyawan</div>
            <div class="detail-value" style="font-weight:400;font-style:italic">${att.note || '-'}</div>
        </div>
        ${att.image ? `
            <div style="margin-top:16px">
                <div class="detail-label">Bukti Foto</div>
                <div style="display:flex;gap:8px;margin-top:8px">
                    <img src="/storage/attendances/${att.image}" style="flex:1;height:120px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color)">
                    ${att.image_out ? `<img src="/storage/attendances/${att.image_out}" style="flex:1;height:120px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color)">` : ''}
                </div>
            </div>
        ` : ''}
    ` : `
        <div style="text-align:center;padding:20px;background:var(--bg-secondary);border-radius:12px;margin-top:16px;border:1px dashed var(--border-color)">
            <i data-lucide="calendar-x" style="width:32px;height:32px;color:var(--text-tertiary);margin-bottom:8px"></i>
            <div style="color:var(--text-secondary)">Karyawan belum mencatat presensi hari ini</div>
        </div>
    `;

    document.getElementById('hrm-detail-content').innerHTML = `
        <div style="display:flex;align-items:center;gap:12px;background:var(--bg-secondary);padding:12px;border-radius:12px;border:1px solid var(--border-color)">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--primary-500);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700">${emp.name.substring(0,1)}</div>
            <div>
                <div style="font-weight:700;color:var(--text-primary)">${emp.name}</div>
                <div style="font-size:12px;color:var(--text-tertiary)">${date}</div>
            </div>
        </div>
        ${attContent}
    `;
    openModal('modal-hrm-detail');
}

function viewPayrollDetail(pay) {
    document.getElementById('hrm-detail-title').innerText = '💸 Rincian Slip Gaji';
    const net = Number(pay.net_salary).toLocaleString('id-ID');
    const basic = Number(pay.basic_salary).toLocaleString('id-ID');
    const allowance = Number(pay.allowance).toLocaleString('id-ID');
    const deduction = Number(pay.deduction).toLocaleString('id-ID');

    document.getElementById('hrm-detail-content').innerHTML = `
        <div style="text-align:center;margin-bottom:20px;padding-bottom:15px;border-bottom:1px solid var(--border-color)">
            <div style="font-size:12px;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:1px">Periode: ${pay.period_month}/${pay.period_year}</div>
            <div style="font-size:18px;font-weight:700;color:var(--text-primary)">${pay.employee.name}</div>
        </div>

        <div style="display:grid;gap:10px">
            <div style="display:flex;justify-content:space-between">
                <span style="color:var(--text-secondary)">Gaji Pokok</span>
                <span style="font-weight:600">Rp ${basic}</span>
            </div>
            <div style="display:flex;justify-content:space-between">
                <span style="color:var(--text-secondary)">Tunjangan</span>
                <span style="font-weight:600;color:var(--success-500)">+ Rp ${allowance}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding-bottom:10px;border-bottom:1px dashed var(--border-color)">
                <span style="color:var(--text-secondary)">Potongan</span>
                <span style="font-weight:600;color:var(--danger-500)">- Rp ${deduction}</span>
            </div>
            <div style="display:flex;justify-content:space-between;padding-top:10px;font-size:18px">
                <span style="font-weight:700">Total Bersih</span>
                <span style="font-weight:800;color:var(--primary-600)">Rp ${net}</span>
            </div>
        </div>

        <div style="margin-top:20px;padding:12px;border-radius:10px;background:var(--bg-secondary);text-align:center">
            <div style="font-size:12px;color:var(--text-tertiary);margin-bottom:4px">Status Pembayaran</div>
            <span class="badge ${pay.status === 'paid' ? 'badge-success' : 'badge-warning'}">${pay.status.toUpperCase()}</span>
        </div>
    `;
    openModal('modal-hrm-detail');
}

function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

function openModalWithData(id, setupFn) {
    setupFn();
    openModal(id);
}

function editEmployee(emp) {
    document.getElementById('employee-modal-title').innerText = '✏️ Edit Karyawan';
    document.getElementById('employee-form').action = '/hrm/employees/' + emp.id;
    document.getElementById('employee-method').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('emp-submit-btn').innerText = 'Update Karyawan';
    document.getElementById('emp-status-group').style.display = 'block';
    
    document.getElementById('emp-nip').value = emp.nip;
    document.getElementById('emp-name').value = emp.name;
    document.getElementById('emp-email').value = emp.email;
    document.getElementById('emp-phone').value = emp.phone;
    document.getElementById('emp-position').value = emp.position;
    document.getElementById('emp-department').value = emp.department;
    document.getElementById('emp-join_date').value = emp.join_date.split('T')[0];
    document.getElementById('emp-basic_salary').value = emp.basic_salary;
    document.getElementById('emp-status').value = emp.status;

    openModal('modal-employee');
}

function openModalAddEmployee() {
    document.getElementById('employee-modal-title').innerText = '👤 Tambah Karyawan';
    document.getElementById('employee-form').action = '{{ route("hrm.employees.store") }}';
    document.getElementById('employee-method').innerHTML = '';
    document.getElementById('emp-submit-btn').innerText = 'Simpan';
    document.getElementById('employee-form').reset();
    document.getElementById('emp-status-group').style.display = 'none';
}

function openAttendanceModal(emp, att) {
    document.getElementById('att-emp-id').value = emp.id;
    document.getElementById('att-emp-name').innerText = emp.name;
    
    if (att) {
        document.getElementById('att-status').value = att.status;
        document.getElementById('att-in').value = att.clock_in ? att.clock_in.substring(0, 5) : '';
        document.getElementById('att-out').value = att.clock_out ? att.clock_out.substring(0, 5) : '';
        document.getElementById('att-note').value = att.note || '';
        document.getElementById('att-note').value = att.note || '';

        // Show photos in modal
        let photoHtml = '';
        if (att.image) {
            photoHtml += `<div><label style="font-size:12px;display:block;margin-bottom:4px">Foto Masuk:</label><img src="/storage/attendances/${att.image}" style="width:100%;max-width:200px;border-radius:8px"></div>`;
        }
        if (att.image_out) {
            photoHtml += `<div><label style="font-size:12px;display:block;margin-bottom:4px">Foto Keluar:</label><img src="/storage/attendances/${att.image_out}" style="width:100%;max-width:200px;border-radius:8px"></div>`;
        }
        document.getElementById('att-photo-preview').innerHTML = photoHtml;
    } else {
        document.getElementById('att-status').value = 'present';
        document.getElementById('att-in').value = '08:00';
        document.getElementById('att-out').value = '17:00';
        document.getElementById('att-note').value = '';
        document.getElementById('att-photo-preview').innerHTML = '';
    }
    openModal('modal-attendance');
}

function editPayroll(pay) {
    document.getElementById('payroll-form').action = '/hrm/payroll/' + pay.id;
    document.getElementById('pay-emp-name').innerText = pay.employee.name;
    document.getElementById('pay-period').innerText = 'Periode: ' + pay.period_month + '/' + pay.period_year;
    document.getElementById('pay-basic').value = 'Rp ' + Number(pay.basic_salary).toLocaleString('id-ID');
    document.getElementById('pay-allowance').value = pay.allowance;
    document.getElementById('pay-deduction').value = pay.deduction;
    document.getElementById('pay-status').value = pay.status;
    openModal('modal-edit-payroll');
}

// Real-time updates with Pusher
@if(config('broadcasting.connections.pusher.key'))
const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
    cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
    forceTLS: true
});

const channel = pusher.subscribe('attendance');
channel.bind('App\\Events\\AttendanceRecorded', function(data) {
    const att = data.attendance;
    const empId = att.employee_id;
    const row = document.getElementById('att-row-' + empId);
    
    if (row && '{{ $attendanceDate }}' === att.date) {
        // Update Status
        const statusBadge = att.status === 'present' ? 'badge-success' : (att.status === 'absent' ? 'badge-danger' : 'badge-warning');
        row.querySelector('.att-status').innerHTML = `<span class="badge ${statusBadge}">${att.status.charAt(0).toUpperCase() + att.status.slice(1)}</span>`;
        
        // Update Clock In/Out
        row.querySelector('.att-in').textContent = att.clock_in || '-';
        row.querySelector('.att-out').textContent = att.clock_out || '-';
        row.querySelector('.att-note').innerHTML = `<span style="font-size:12px">${att.note || '-'}</span>`;
        
        // Update Photos
        let photosHtml = '';
        if (att.image) {
            photosHtml += `<img src="/storage/attendances/${att.image}" class="att-img-in" style="width:40px;height:40px;border-radius:4px;object-fit:cover;cursor:pointer" onclick="window.open(this.src)" title="Foto Masuk"> `;
        }
        if (att.image_out) {
            photosHtml += `<img src="/storage/attendances/${att.image_out}" class="att-img-out" style="width:40px;height:40px;border-radius:4px;object-fit:cover;cursor:pointer;margin-left:4px" onclick="window.open(this.src)" title="Foto Keluar">`;
        }
        row.querySelector('.att-photos').innerHTML = photosHtml;
        
        // Update Modal Data binding on the button
        const logBtn = row.querySelector('.btn-log');
        const empData = @json($employees->keyBy('id'));
        logBtn.onclick = () => openAttendanceModal(empData[empId], att);

        // Highlight Effect
        row.style.background = 'rgba(79, 70, 229, 0.1)';
        setTimeout(() => { row.style.background = 'transparent'; }, 2000);
        
        // If showing attendance tab, play subtle sound or toast could be added here
    }
});
@endif

// Initial binding for add button
document.querySelector('[onclick="openModal(\'modal-add-employee\')"]').onclick = () => {
    openModalAddEmployee();
    openModal('modal-employee');
}
</script>
<style>
.tab-btn {
    background: none;
    border: none;
    padding: 15px 20px;
    font-size: 14px;
    font-weight: 600;
    color: var(--text-tertiary);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}
.tab-btn:hover { color: var(--text-secondary); }
.tab-btn.active { color: var(--primary-500); border-bottom-color: var(--primary-500); }
.tab-pane.hidden { display: none; }
.text-success { color: var(--success-500); }
.text-danger { color: var(--danger-500); }

.detail-item {
    background: var(--bg-secondary);
    padding: 12px;
    border-radius: 10px;
    border: 1px solid var(--border-color);
}
.detail-label {
    font-size: 11px;
    color: var(--text-tertiary);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 700;
    margin-bottom: 4px;
}
.detail-value {
    font-weight: 600;
    color: var(--text-primary);
}
.modal-overlay {
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}
.modal-overlay.hidden { display: none; }
.modal {
    background: var(--bg-card, white);
    padding: 24px;
    border-radius: 16px;
    border: 1px solid var(--border-color);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
}
.clickable-row:hover {
    background: rgba(var(--primary-rgb), 0.05) !important;
}
</style>
@endpush

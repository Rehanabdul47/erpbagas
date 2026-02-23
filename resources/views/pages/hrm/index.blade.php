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
                        <tr>
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
                                    <button class="btn btn-ghost btn-xs" title="Edit" onclick='editEmployee(@json($emp))'><i data-lucide="edit-2" style="width:14px"></i></button>
                                    <form method="POST" action="{{ route('hrm.employees.destroy', $emp) }}" onsubmit="return confirm('Hapus karyawan ini?')">
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
                        <tr><th>Karyawan</th><th>Status</th><th>Jam Masuk</th><th>Jam Keluar</th><th>Catatan</th><th>Update</th></tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $emp)
                        @php $att = $attendances->firstWhere('employee_id', $emp->id); @endphp
                        <tr>
                            <td>
                                <div style="font-weight:500">{{ $emp->name }}</div>
                                <div style="font-size:12px;color:var(--text-tertiary)">NIP: {{ $emp->nip }}</div>
                            </td>
                            <td>
                                @if($att)
                                <span class="badge {{ $att->status === 'present' ? 'badge-success' : ($att->status === 'absent' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ ucfirst($att->status) }}
                                </span>
                                @else
                                <span style="font-size:12px;color:var(--text-tertiary)">Belum mencatat</span>
                                @endif
                            </td>
                            <td>{{ $att->clock_in ?? '-' }}</td>
                            <td>{{ $att->clock_out ?? '-' }}</td>
                            <td><span style="font-size:12px">{{ $att->note ?? '-' }}</span></td>
                            <td>
                                <button class="btn btn-ghost btn-xs" onclick='openAttendanceModal(@json($emp), @json($att))'><i data-lucide="clipboard-check" style="width:14px"></i> Log</button>
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
                        <tr>
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
                                <button class="btn btn-ghost btn-xs" onclick='editPayroll(@json($pay))'><i data-lucide="settings" style="width:14px"></i> Edit</button>
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

@endsection

@push('scripts')
<script>
function switchTab(tabId) {
    const url = new URL(window.location);
    url.searchParams.set('tab', tabId);
    window.location.href = url.href;
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
    } else {
        document.getElementById('att-status').value = 'present';
        document.getElementById('att-in').value = '08:00';
        document.getElementById('att-out').value = '17:00';
        document.getElementById('att-note').value = '';
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
</style>
@endpush

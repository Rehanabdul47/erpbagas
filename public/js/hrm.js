/* ===================================
   HRM & Payroll Module
   =================================== */

function renderHRM(container) {
    const totalEmp = Store.employees.length;
    const activeEmp = Store.employees.filter(e => e.status === 'active').length;
    const presentToday = Store.attendance.filter(a => a.status === 'present').length;
    const lateToday = Store.attendance.filter(a => a.status === 'late').length;
    const absentToday = Store.attendance.filter(a => a.status === 'absent').length;

    container.innerHTML = `
        <div class="page-header">
            <h1 class="page-title"><i data-lucide="users"></i> HRM & Payroll</h1>
            <button class="btn btn-primary btn-sm" onclick="showAddEmployeeModal()"><i data-lucide="user-plus"></i> Tambah Karyawan</button>
        </div>

        <!-- Stats -->
        <div class="grid-4 mb-6">
            <div class="stat-card" style="--stat-color: var(--primary-400)">
                <div class="stat-card-icon"><i data-lucide="users"></i></div>
                <div class="stat-card-value">${totalEmp}</div>
                <div class="stat-card-label">Total Karyawan</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--success-400)">
                <div class="stat-card-icon" style="background:rgba(16,185,129,0.1)"><i data-lucide="user-check"></i></div>
                <div class="stat-card-value">${presentToday}</div>
                <div class="stat-card-label">Hadir Hari Ini</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--warning-400)">
                <div class="stat-card-icon" style="background:rgba(245,158,11,0.1)"><i data-lucide="clock"></i></div>
                <div class="stat-card-value">${lateToday}</div>
                <div class="stat-card-label">Terlambat</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--danger-400)">
                <div class="stat-card-icon" style="background:rgba(239,68,68,0.1)"><i data-lucide="user-x"></i></div>
                <div class="stat-card-value">${absentToday}</div>
                <div class="stat-card-label">Absen</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchHRMTab(this,'employees')">👥 Karyawan</button>
            <button class="tab" onclick="switchHRMTab(this,'attendance')">📋 Absensi</button>
            <button class="tab" onclick="switchHRMTab(this,'performance')">📊 KPI</button>
            <button class="tab" onclick="switchHRMTab(this,'payroll')">💰 Payroll</button>
        </div>

        <div id="hrm-tab-content">
            ${renderEmployeesTab()}
        </div>
    `;
}

function switchHRMTab(el, tab) {
    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    const content = document.getElementById('hrm-tab-content');
    if (tab === 'employees') content.innerHTML = renderEmployeesTab();
    else if (tab === 'attendance') content.innerHTML = renderAttendanceTab();
    else if (tab === 'performance') content.innerHTML = renderPerformanceTab();
    else if (tab === 'payroll') content.innerHTML = renderPayrollTab();
    if (window.lucide) lucide.createIcons();
}

function renderEmployeesTab() {
    return `
        <div class="card">
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Nama</th><th>Departemen</th><th>Posisi</th><th>Tanggal Masuk</th><th>Status</th><th>KPI</th></tr></thead>
                    <tbody>
                        ${Store.employees.map(emp => {
        const statusBadge = emp.status === 'active' ? 'badge-success' : emp.status === 'on_leave' ? 'badge-info' : 'badge-danger';
        const statusLabel = emp.status === 'active' ? 'Aktif' : emp.status === 'on_leave' ? 'Cuti' : 'Nonaktif';
        const kpiColor = emp.kpiScore >= 90 ? 'var(--success-400)' : emp.kpiScore >= 75 ? 'var(--warning-400)' : 'var(--danger-400)';
        return `<tr>
                                <td style="font-weight:600;color:var(--text-primary)">${emp.id}</td>
                                <td style="font-weight:600">${emp.name}</td>
                                <td><span class="badge badge-neutral">${emp.department}</span></td>
                                <td>${emp.position}</td>
                                <td>${emp.joinDate}</td>
                                <td><span class="badge ${statusBadge}">${statusLabel}</span></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div class="progress-bar" style="width:60px"><div class="progress-bar-fill" style="width:${emp.kpiScore}%;background:${kpiColor}"></div></div>
                                        <span style="font-weight:700;font-size:var(--font-xs);color:${kpiColor}">${emp.kpiScore}%</span>
                                    </div>
                                </td>
                            </tr>`;
    }).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderAttendanceTab() {
    return `
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i data-lucide="calendar-check"></i> Absensi Hari Ini — 21 Februari 2026</div>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Nama</th><th>Check In</th><th>Check Out</th><th>Durasi</th><th>Status</th></tr></thead>
                    <tbody>
                        ${Store.attendance.map(att => {
        const emp = Store.getEmployee(att.empId);
        if (!emp) return '';
        const statusMap = {
            present: { badge: 'badge-success', label: '✅ Hadir' },
            late: { badge: 'badge-warning', label: '⏰ Terlambat' },
            absent: { badge: 'badge-danger', label: '❌ Absen' },
            on_leave: { badge: 'badge-info', label: '🏖️ Cuti' }
        };
        const st = statusMap[att.status] || { badge: 'badge-neutral', label: att.status };
        let duration = '-';
        if (att.checkIn && att.checkOut) {
            const [hi, mi] = att.checkIn.split(':').map(Number);
            const [ho, mo] = att.checkOut.split(':').map(Number);
            const hours = ho - hi;
            const mins = mo - mi;
            duration = `${hours}j ${mins >= 0 ? mins : 60 + mins}m`;
        }
        return `<tr>
                                <td style="font-weight:600;color:var(--text-primary)">${att.empId}</td>
                                <td style="font-weight:600">${emp.name}</td>
                                <td>${att.checkIn || '-'}</td>
                                <td>${att.checkOut || '-'}</td>
                                <td>${duration}</td>
                                <td><span class="badge ${st.badge}">${st.label}</span></td>
                            </tr>`;
    }).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderPerformanceTab() {
    const sorted = [...Store.employees].sort((a, b) => b.kpiScore - a.kpiScore);
    return `
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i data-lucide="award"></i> KPI Karyawan</div>
            </div>
            <div style="padding:var(--space-2)">
                ${sorted.map((emp, i) => {
        const kpiColor = emp.kpiScore >= 90 ? 'var(--success-400)' : emp.kpiScore >= 75 ? 'var(--warning-400)' : 'var(--danger-400)';
        return `<div style="display:flex;align-items:center;gap:var(--space-4);padding:var(--space-3);border-bottom:1px solid var(--border-subtle)">
                        <span style="width:28px;text-align:center;font-weight:700;color:${i < 3 ? 'var(--warning-400)' : 'var(--text-tertiary)'}">#${i + 1}</span>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;font-size:var(--font-sm)">${emp.name}</div>
                            <div style="font-size:var(--font-xs);color:var(--text-tertiary)">${emp.position} · ${emp.department}</div>
                        </div>
                        <div style="width:120px">
                            <div class="progress-bar"><div class="progress-bar-fill" style="width:${emp.kpiScore}%;background:${kpiColor}"></div></div>
                        </div>
                        <span style="font-weight:800;color:${kpiColor};min-width:45px;text-align:right">${emp.kpiScore}%</span>
                    </div>`;
    }).join('')}
            </div>
        </div>
    `;
}

function renderPayrollTab() {
    const totalPayroll = Store.employees.reduce((s, e) => s + e.salary, 0);
    return `
        <div class="card mb-6">
            <div class="card-header">
                <div class="card-title"><i data-lucide="calculator"></i> Payroll — Februari 2026</div>
                <button class="btn btn-success btn-sm" onclick="showToast('info','Info','Slip gaji di-generate untuk semua karyawan')"><i data-lucide="printer"></i> Generate Slip</button>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Nama</th><th>Posisi</th><th>Gaji Pokok</th><th>Kehadiran</th><th>Potongan</th><th>Bonus KPI</th><th>Total</th></tr></thead>
                    <tbody>
                        ${Store.employees.map(emp => {
        const att = Store.attendance.find(a => a.empId === emp.id);
        const attStatus = att ? att.status : 'unknown';
        const deduction = attStatus === 'absent' ? Math.round(emp.salary * 0.04) : attStatus === 'late' ? Math.round(emp.salary * 0.01) : 0;
        const bonus = emp.kpiScore >= 90 ? Math.round(emp.salary * 0.1) : emp.kpiScore >= 80 ? Math.round(emp.salary * 0.05) : 0;
        const total = emp.salary - deduction + bonus;
        return `<tr>
                                <td style="font-weight:600;color:var(--text-primary)">${emp.id}</td>
                                <td style="font-weight:600">${emp.name}</td>
                                <td>${emp.position}</td>
                                <td>${Store.formatCurrency(emp.salary)}</td>
                                <td><span class="badge ${attStatus === 'present' ? 'badge-success' : attStatus === 'late' ? 'badge-warning' : attStatus === 'absent' ? 'badge-danger' : 'badge-info'}">${attStatus}</span></td>
                                <td style="color:var(--danger-400)">${deduction > 0 ? '-' + Store.formatCurrency(deduction) : '-'}</td>
                                <td style="color:var(--success-400)">${bonus > 0 ? '+' + Store.formatCurrency(bonus) : '-'}</td>
                                <td style="font-weight:700">${Store.formatCurrency(total)}</td>
                            </tr>`;
    }).join('')}
                    </tbody>
                </table>
            </div>
            <div style="padding:var(--space-4);text-align:right;border-top:1px solid var(--border-subtle)">
                <strong>Total Payroll: <span style="color:var(--primary-400)">${Store.formatCurrency(totalPayroll)}</span></strong>
            </div>
        </div>
    `;
}

function showAddEmployeeModal() {
    showModal('👤 Tambah Karyawan Baru', `
        <div class="form-row">
            <div class="form-group"><label>Nama Lengkap</label><input type="text" class="form-control" id="emp-name" placeholder="Nama karyawan"></div>
            <div class="form-group"><label>Departemen</label>
                <select class="form-control" id="emp-dept"><option>Warehouse</option><option>Finance</option><option>Sales</option><option>HR</option><option>IT</option><option>Management</option></select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Posisi</label><input type="text" class="form-control" id="emp-position" placeholder="Posisi/jabatan"></div>
            <div class="form-group"><label>Gaji Pokok</label><input type="number" class="form-control" id="emp-salary" placeholder="0" min="0"></div>
        </div>
    `, `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
        <button class="btn btn-primary" onclick="addEmployee()"><i data-lucide="check"></i> Simpan</button>`);
}

function addEmployee() {
    const name = document.getElementById('emp-name').value;
    const dept = document.getElementById('emp-dept').value;
    const position = document.getElementById('emp-position').value;
    const salary = parseInt(document.getElementById('emp-salary').value) || 0;
    if (name && position) {
        Store.employees.push({
            id: 'EMP-' + (Store.employees.length + 1).toString().padStart(3, '0'),
            name, department: dept, position, salary, status: 'active',
            joinDate: new Date().toISOString().slice(0, 10), kpiScore: 0
        });
        closeModal();
        showToast('success', 'Karyawan Ditambahkan', `${name} berhasil ditambahkan`);
        renderHRM(document.getElementById('page-content'));
        setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50);
    }
}

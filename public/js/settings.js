/* ===================================
   Settings Module
   =================================== */

function renderSettings(container) {
    const user = Store.currentUser;
    container.innerHTML = `
        <div class="page-header">
            <h1 class="page-title"><i data-lucide="settings"></i> Settings</h1>
        </div>

        <div class="grid-2">
            <!-- Profile -->
            <div class="card settings-section">
                <h3><i data-lucide="user"></i> Profil</h3>
                <div class="profile-avatar-large"><i data-lucide="user"></i></div>
                <div class="form-group"><label>Nama</label><input type="text" class="form-control" value="${user.name}"></div>
                <div class="form-group"><label>Email</label><input type="email" class="form-control" value="${user.email}"></div>
                <div class="form-group"><label>Role</label><input type="text" class="form-control" value="${user.roleName}" disabled></div>
                <button class="btn btn-primary btn-sm" onclick="showToast('success','Berhasil','Profil disimpan')"><i data-lucide="save"></i> Simpan</button>
            </div>

            <!-- Company -->
            <div class="card settings-section">
                <h3><i data-lucide="building"></i> Perusahaan</h3>
                <div class="form-group"><label>Nama Perusahaan</label><input type="text" class="form-control" value="GUPALA Indonesia"></div>
                <div class="form-group"><label>Alamat</label><textarea class="form-control" rows="2">Jl. Sudirman No. 123, Jakarta Selatan, 12190</textarea></div>
                <div class="form-group"><label>Telepon</label><input type="text" class="form-control" value="021-5551234"></div>
                <div class="form-group"><label>Email</label><input type="email" class="form-control" value="info@gupala.com"></div>
                <button class="btn btn-primary btn-sm" onclick="showToast('success','Berhasil','Data perusahaan disimpan')"><i data-lucide="save"></i> Simpan</button>
            </div>

            <!-- Team -->
            <div class="card settings-section">
                <h3><i data-lucide="users"></i> Manajemen Tim</h3>
                ${Object.values(Store.users).map(u => `
                    <div class="team-member">
                        <div class="user-avatar"><i data-lucide="user"></i></div>
                        <div style="flex:1;min-width:0">
                            <div style="font-weight:600;font-size:var(--font-sm)">${u.name}</div>
                            <div style="font-size:var(--font-xs);color:var(--text-tertiary)">${u.email} · ${u.roleName}</div>
                        </div>
                        <span class="badge badge-${u.role === 'super_admin' ? 'primary' : u.role === 'manager' ? 'info' : 'neutral'}">${u.roleName}</span>
                    </div>
                `).join('')}
            </div>

            <!-- Notifications -->
            <div class="card settings-section">
                <h3><i data-lucide="bell"></i> Notifikasi</h3>
                ${[
            { label: 'Order baru masuk', checked: true },
            { label: 'Stok rendah / habis', checked: true },
            { label: 'Invoice jatuh tempo', checked: true },
            { label: 'PO diterima', checked: false },
            { label: 'Karyawan absen', checked: false },
            { label: 'Laporan harian', checked: true },
        ].map((n, i) => `
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:var(--space-3) 0;border-bottom:1px solid var(--border-subtle)">
                        <span style="font-size:var(--font-sm)">${n.label}</span>
                        <label style="position:relative;width:44px;height:24px;cursor:pointer">
                            <input type="checkbox" ${n.checked ? 'checked' : ''} style="opacity:0;width:0;height:0" id="notif-toggle-${i}">
                            <span style="position:absolute;inset:0;background:${n.checked ? 'var(--primary-500)' : 'var(--bg-elevated)'};border-radius:12px;transition:0.3s" onclick="this.previousElementSibling.checked=!this.previousElementSibling.checked;this.style.background=this.previousElementSibling.checked?'var(--primary-500)':'var(--bg-elevated)';this.children[0].style.transform=this.previousElementSibling.checked?'translateX(20px)':'translateX(0)'">
                                <span style="position:absolute;top:2px;left:2px;width:20px;height:20px;background:white;border-radius:50%;transition:0.3s;transform:${n.checked ? 'translateX(20px)' : 'translateX(0)'}"></span>
                            </span>
                        </label>
                    </div>
                `).join('')}
                <button class="btn btn-primary btn-sm mt-4" onclick="showToast('success','Berhasil','Pengaturan notifikasi disimpan')"><i data-lucide="save"></i> Simpan</button>
            </div>
        </div>
    `;
}

/* ===================================
   Procurement Module
   =================================== */

function renderProcurement(container) {
    const totalPO = Store.purchaseOrders.length;
    const pendingPO = Store.purchaseOrders.filter(p => ['draft', 'submitted', 'partial'].includes(p.status)).length;
    const totalVendors = Store.vendors.length;
    const totalPOValue = Store.purchaseOrders.reduce((s, p) => s + p.total, 0);

    container.innerHTML = `
        <div class="page-header">
            <h1 class="page-title"><i data-lucide="truck"></i> Procurement</h1>
            <button class="btn btn-primary btn-sm" onclick="showCreatePOModal()"><i data-lucide="file-plus"></i> Buat PO</button>
        </div>

        <div class="grid-4 mb-6">
            <div class="stat-card" style="--stat-color:var(--primary-400)">
                <div class="stat-card-icon"><i data-lucide="file-text"></i></div>
                <div class="stat-card-value">${totalPO}</div>
                <div class="stat-card-label">Total PO</div>
            </div>
            <div class="stat-card" style="--stat-color:var(--warning-400)">
                <div class="stat-card-icon" style="background:rgba(245,158,11,0.1)"><i data-lucide="clock"></i></div>
                <div class="stat-card-value">${pendingPO}</div>
                <div class="stat-card-label">PO Pending</div>
            </div>
            <div class="stat-card" style="--stat-color:var(--info-400)">
                <div class="stat-card-icon" style="background:rgba(59,130,246,0.1)"><i data-lucide="building"></i></div>
                <div class="stat-card-value">${totalVendors}</div>
                <div class="stat-card-label">Vendor</div>
            </div>
            <div class="stat-card" style="--stat-color:var(--accent-400)">
                <div class="stat-card-icon" style="background:rgba(14,165,233,0.1)"><i data-lucide="banknote"></i></div>
                <div class="stat-card-value">${Store.formatCurrencyShort(totalPOValue)}</div>
                <div class="stat-card-label">Nilai PO</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchProcTab(this,'po')">📄 Purchase Orders</button>
            <button class="tab" onclick="switchProcTab(this,'vendors')">🏢 Vendors</button>
        </div>
        <div id="proc-tab-content">${renderPOTab()}</div>
    `;
}

function switchProcTab(el, tab) {
    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    const c = document.getElementById('proc-tab-content');
    c.innerHTML = tab === 'po' ? renderPOTab() : renderVendorsTab();
    if (window.lucide) lucide.createIcons();
}

function renderPOTab() {
    return `<div class="card"><div class="table-container"><table>
        <thead><tr><th>PO ID</th><th>Vendor</th><th>Items</th><th>Total</th><th>Order Date</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>${Store.purchaseOrders.map(po => {
        const v = Store.getVendor(po.vendorId);
        return `<tr>
                <td style="font-weight:600;color:var(--text-primary)">${po.id}</td>
                <td>${v ? v.name : '-'}</td>
                <td>${po.items.map(i => i.name).join(', ')}</td>
                <td style="font-weight:600">${Store.formatCurrency(po.total)}</td>
                <td>${po.orderDate}</td>
                <td><span class="badge ${Store.getPOStatusBadge(po.status)}">${po.status.toUpperCase()}</span></td>
                <td>${po.status === 'submitted' ? `<button class="btn btn-success btn-xs" onclick="receivePO('${po.id}')">Terima</button>` :
                po.status === 'draft' ? `<button class="btn btn-primary btn-xs" onclick="submitPO('${po.id}')">Submit</button>` : ''}</td>
            </tr>`;
    }).join('')}</tbody>
    </table></div></div>`;
}

function renderVendorsTab() {
    return `<div class="card"><div class="table-container"><table>
        <thead><tr><th>ID</th><th>Nama</th><th>Kontak</th><th>Email</th><th>Alamat</th><th>Rating</th><th>Total PO</th></tr></thead>
        <tbody>${Store.vendors.map(v => `<tr>
            <td style="font-weight:600;color:var(--text-primary)">${v.id}</td>
            <td style="font-weight:600">${v.name}</td>
            <td>${v.contact} · ${v.phone}</td>
            <td>${v.email}</td>
            <td>${v.address}</td>
            <td><span class="vendor-star">★</span> ${v.rating}/5</td>
            <td>${v.totalPO}</td>
        </tr>`).join('')}</tbody>
    </table></div></div>`;
}

function submitPO(id) {
    const po = Store.purchaseOrders.find(p => p.id === id);
    if (po) { po.status = 'submitted'; showToast('info', 'PO Submitted', `${id} dikirim ke vendor`); renderProcurement(document.getElementById('page-content')); setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50); }
}

function receivePO(id) {
    const po = Store.purchaseOrders.find(p => p.id === id);
    if (po) { po.status = 'received'; po.receivedDate = new Date().toISOString().slice(0, 10); showToast('success', 'PO Diterima', `${id} barang diterima`); renderProcurement(document.getElementById('page-content')); setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50); }
}

function showCreatePOModal() {
    showModal('📄 Buat Purchase Order', `
        <div class="form-group"><label>Vendor</label><select class="form-control">${Store.vendors.map(v => `<option value="${v.id}">${v.name}</option>`).join('')}</select></div>
        <div class="form-row">
            <div class="form-group"><label>Item</label><input type="text" class="form-control" placeholder="Nama barang"></div>
            <div class="form-group"><label>Qty</label><input type="number" class="form-control" min="1" value="1"></div>
        </div>
        <div class="form-group"><label>Harga Satuan</label><input type="number" class="form-control" placeholder="0" min="0"></div>
    `, `<button class="btn btn-secondary" onclick="closeModal()">Batal</button><button class="btn btn-primary" onclick="closeModal();showToast('success','Berhasil','PO berhasil dibuat')">Simpan</button>`);
}

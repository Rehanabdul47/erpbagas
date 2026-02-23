/* ===================================
   Sales & CRM Module
   =================================== */

function renderSales(container) {
    const leads = Store.pipeline.filter(p => p.stage === 'lead');
    const negotiations = Store.pipeline.filter(p => p.stage === 'negotiation');
    const proposals = Store.pipeline.filter(p => p.stage === 'proposal');
    const won = Store.pipeline.filter(p => p.stage === 'won');
    const lost = Store.pipeline.filter(p => p.stage === 'lost');
    const totalPipelineValue = Store.pipeline.filter(p => !['won', 'lost'].includes(p.stage)).reduce((s, p) => s + p.value, 0);

    container.innerHTML = `
        <div class="page-header">
            <h1 class="page-title"><i data-lucide="trending-up"></i> Sales & CRM</h1>
            <button class="btn btn-primary btn-sm" onclick="showAddLeadModal()"><i data-lucide="user-plus"></i> Tambah Lead</button>
        </div>

        <!-- Stats -->
        <div class="grid-4 mb-6">
            <div class="stat-card" style="--stat-color: var(--info-400)">
                <div class="stat-card-icon" style="background:rgba(59,130,246,0.1)"><i data-lucide="users"></i></div>
                <div class="stat-card-value">${leads.length + negotiations.length + proposals.length}</div>
                <div class="stat-card-label">Pipeline Aktif</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--primary-400)">
                <div class="stat-card-icon"><i data-lucide="target"></i></div>
                <div class="stat-card-value">${Store.formatCurrencyShort(totalPipelineValue)}</div>
                <div class="stat-card-label">Nilai Pipeline</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--success-400)">
                <div class="stat-card-icon" style="background:rgba(16,185,129,0.1)"><i data-lucide="trophy"></i></div>
                <div class="stat-card-value">${won.length}</div>
                <div class="stat-card-label">Deal Won</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--danger-400)">
                <div class="stat-card-icon" style="background:rgba(239,68,68,0.1)"><i data-lucide="x-circle"></i></div>
                <div class="stat-card-value">${lost.length}</div>
                <div class="stat-card-label">Deal Lost</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchSalesTab(this,'pipeline')">🎯 Pipeline</button>
            <button class="tab" onclick="switchSalesTab(this,'orders')">📦 Order Tracking</button>
            <button class="tab" onclick="switchSalesTab(this,'customers')">👥 Customers</button>
        </div>

        <div id="sales-tab-content">
            ${renderPipelineTab(leads, negotiations, proposals, won, lost)}
        </div>
    `;
}

function switchSalesTab(el, tab) {
    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    const content = document.getElementById('sales-tab-content');
    if (tab === 'pipeline') {
        const leads = Store.pipeline.filter(p => p.stage === 'lead');
        const negotiations = Store.pipeline.filter(p => p.stage === 'negotiation');
        const proposals = Store.pipeline.filter(p => p.stage === 'proposal');
        const won = Store.pipeline.filter(p => p.stage === 'won');
        const lost = Store.pipeline.filter(p => p.stage === 'lost');
        content.innerHTML = renderPipelineTab(leads, negotiations, proposals, won, lost);
    } else if (tab === 'orders') content.innerHTML = renderOrderTrackingTab();
    else if (tab === 'customers') content.innerHTML = renderCustomersTab();
    if (window.lucide) lucide.createIcons();
}

function renderPipelineTab(leads, negotiations, proposals, won, lost) {
    function renderCards(items, color) {
        return items.map(p => `
            <div class="kanban-card">
                <div class="kanban-card-title">${p.company}</div>
                <div style="font-size:var(--font-sm);font-weight:700;color:${color};margin:4px 0">${Store.formatCurrency(p.value)}</div>
                <div class="kanban-card-meta">👤 ${p.contact} · ${p.date}</div>
                <div style="font-size:var(--font-xs);color:var(--text-tertiary);margin-top:4px">${p.notes}</div>
            </div>
        `).join('');
    }

    return `
        <div class="kanban-board">
            <div class="kanban-column">
                <div class="kanban-column-header">
                    <span class="kanban-column-title" style="color:var(--info-400)">📥 Lead</span>
                    <span class="kanban-count">${leads.length}</span>
                </div>
                ${renderCards(leads, 'var(--info-400)')}
            </div>
            <div class="kanban-column">
                <div class="kanban-column-header">
                    <span class="kanban-column-title" style="color:var(--warning-400)">💬 Negotiation</span>
                    <span class="kanban-count">${negotiations.length}</span>
                </div>
                ${renderCards(negotiations, 'var(--warning-400)')}
            </div>
            <div class="kanban-column">
                <div class="kanban-column-header">
                    <span class="kanban-column-title" style="color:var(--primary-400)">📄 Proposal</span>
                    <span class="kanban-count">${proposals.length}</span>
                </div>
                ${renderCards(proposals, 'var(--primary-400)')}
            </div>
            <div class="kanban-column">
                <div class="kanban-column-header">
                    <span class="kanban-column-title" style="color:var(--success-400)">🏆 Won</span>
                    <span class="kanban-count">${won.length}</span>
                </div>
                ${renderCards(won, 'var(--success-400)')}
            </div>
            <div class="kanban-column">
                <div class="kanban-column-header">
                    <span class="kanban-column-title" style="color:var(--danger-400)">❌ Lost</span>
                    <span class="kanban-count">${lost.length}</span>
                </div>
                ${renderCards(lost, 'var(--danger-400)')}
            </div>
        </div>
    `;
}

function renderOrderTrackingTab() {
    const statusFlow = ['new', 'processing', 'ready', 'shipped', 'completed'];
    return `
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i data-lucide="truck"></i> Status Pesanan Pelanggan</div>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>Order ID</th><th>Customer</th><th>Channel</th><th>Total</th><th>Status Flow</th><th>Aksi</th></tr></thead>
                    <tbody>
                        ${Store.orders.filter(o => o.status !== 'cancelled').map(o => {
        const currentIdx = statusFlow.indexOf(o.status);
        const progressHtml = statusFlow.map((s, i) => {
            const done = i <= currentIdx;
            const color = done ? 'var(--success-400)' : 'var(--text-tertiary)';
            const labels = { new: 'Baru', processing: 'Proses', ready: 'Siap', shipped: 'Kirim', completed: 'Selesai' };
            return `<span style="font-size:10px;color:${color};font-weight:${done ? '700' : '400'}">${labels[s]}</span>`;
        }).join(' → ');
        return `<tr>
                                <td style="font-weight:600;color:var(--text-primary)">${o.channelOrderId}</td>
                                <td>${o.customer}</td>
                                <td>${Store.getChannelIcon(o.channel)} ${o.channel}</td>
                                <td style="font-weight:600">${Store.formatCurrency(o.total)}</td>
                                <td>${progressHtml}</td>
                                <td>${o.status !== 'completed' ? `<button class="btn btn-primary btn-xs" onclick="advanceOrder('${o.id}')">Next →</button>` : '<span class="badge badge-success">✅ Done</span>'}</td>
                            </tr>`;
    }).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderCustomersTab() {
    return `
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i data-lucide="users"></i> Database Customer</div>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Phone</th><th>Total Order</th><th>Total Spent</th><th>Last Order</th></tr></thead>
                    <tbody>
                        ${Store.customers.map(c => `
                            <tr>
                                <td style="font-weight:600;color:var(--text-primary)">${c.id}</td>
                                <td style="font-weight:600">${c.name}</td>
                                <td>${c.email}</td>
                                <td>${c.phone}</td>
                                <td style="text-align:center">${c.totalOrders}</td>
                                <td style="font-weight:600">${Store.formatCurrency(c.totalSpent)}</td>
                                <td>${c.lastOrder}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function advanceOrder(orderId) {
    const statusFlow = ['new', 'processing', 'ready', 'shipped', 'completed'];
    const order = Store.orders.find(o => o.id === orderId);
    if (!order) return;
    const currentIdx = statusFlow.indexOf(order.status);
    if (currentIdx < statusFlow.length - 1) {
        order.status = statusFlow[currentIdx + 1];
        showToast('success', 'Order Updated', `${order.channelOrderId} → ${Store.getOrderStatusLabel(order.status)}`);
        // Re-render
        const content = document.getElementById('sales-tab-content');
        content.innerHTML = renderOrderTrackingTab();
        if (window.lucide) lucide.createIcons();
    }
}

function showAddLeadModal() {
    showModal('🎯 Tambah Lead Baru', `
        <div class="form-row">
            <div class="form-group"><label>Perusahaan</label><input type="text" class="form-control" id="lead-company" placeholder="Nama perusahaan"></div>
            <div class="form-group"><label>Kontak</label><input type="text" class="form-control" id="lead-contact" placeholder="Nama kontak"></div>
        </div>
        <div class="form-group"><label>Nilai Estimasi</label><input type="number" class="form-control" id="lead-value" placeholder="0" min="0"></div>
        <div class="form-group"><label>Catatan</label><textarea class="form-control" id="lead-notes" rows="2" placeholder="Catatan tentang lead..."></textarea></div>
    `, `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
        <button class="btn btn-primary" onclick="addLead()"><i data-lucide="check"></i> Simpan</button>`);
}

function addLead() {
    const company = document.getElementById('lead-company').value;
    const contact = document.getElementById('lead-contact').value;
    const value = parseInt(document.getElementById('lead-value').value) || 0;
    const notes = document.getElementById('lead-notes').value;
    if (company && contact) {
        Store.pipeline.push({
            id: 'LEAD-' + Date.now(), company, contact, value, stage: 'lead', notes, date: new Date().toISOString().slice(0, 10)
        });
        closeModal();
        showToast('success', 'Lead Ditambahkan', `${company} berhasil ditambahkan ke pipeline`);
        renderSales(document.getElementById('page-content'));
        setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50);
    } else {
        showToast('warning', 'Peringatan', 'Mohon isi perusahaan dan kontak');
    }
}

/* ===================================
   Orders Page (Standalone)
   =================================== */

let currentOrderFilter = 'all';

function renderOrders(container) {
    const counts = {
        all: Store.orders.length,
        new: Store.orders.filter(o => o.status === 'new').length,
        processing: Store.orders.filter(o => o.status === 'processing').length,
        ready: Store.orders.filter(o => o.status === 'ready').length,
        shipped: Store.orders.filter(o => o.status === 'shipped').length,
        completed: Store.orders.filter(o => o.status === 'completed').length,
        cancelled: Store.orders.filter(o => o.status === 'cancelled').length,
    };
    const filtered = Store.getOrdersByStatus(currentOrderFilter);

    container.innerHTML = `
        <div class="page-header">
            <h1 class="page-title"><i data-lucide="shopping-cart"></i> Order Management</h1>
        </div>
        <div class="grid-4 mb-6">
            <div class="stat-card" style="--stat-color:var(--info-400)"><div class="stat-card-icon" style="background:rgba(59,130,246,0.1)"><i data-lucide="shopping-bag"></i></div><div class="stat-card-value">${counts.all}</div><div class="stat-card-label">Total Order</div></div>
            <div class="stat-card" style="--stat-color:var(--primary-400)"><div class="stat-card-icon"><i data-lucide="plus-circle"></i></div><div class="stat-card-value">${counts.new}</div><div class="stat-card-label">Order Baru</div></div>
            <div class="stat-card" style="--stat-color:var(--success-400)"><div class="stat-card-icon" style="background:rgba(16,185,129,0.1)"><i data-lucide="package-check"></i></div><div class="stat-card-value">${counts.ready}</div><div class="stat-card-label">Siap Kirim</div></div>
            <div class="stat-card" style="--stat-color:var(--accent-400)"><div class="stat-card-icon" style="background:rgba(14,165,233,0.1)"><i data-lucide="truck"></i></div><div class="stat-card-value">${counts.shipped}</div><div class="stat-card-label">Dalam Pengiriman</div></div>
        </div>
        <div class="tabs">
            ${['all', 'new', 'processing', 'ready', 'shipped', 'completed', 'cancelled'].map(s => {
        const labels = { all: 'Semua', new: '🆕 Baru', processing: '⏳ Proses', ready: '📦 Siap', shipped: '🚚 Dikirim', completed: '✅ Selesai', cancelled: '❌ Batal' };
        return `<button class="tab ${currentOrderFilter === s ? 'active' : ''}" onclick="filterOrders(this,'${s}')">${labels[s]}${counts[s] ? ` <span class='tab-badge'>${counts[s]}</span>` : ''}</button>`;
    }).join('')}
        </div>
        <div class="card">
            <div class="table-container"><table><thead><tr><th>Order ID</th><th>Channel</th><th>Customer</th><th>Alamat</th><th>Items</th><th>Total</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody id="orders-tbody">${renderOrderRows(filtered)}</tbody></table></div>
            <div class="pagination"><span class="pagination-info">Menampilkan ${filtered.length} order</span></div>
        </div>`;
}

function renderOrderRows(orders) {
    return orders.map(o => `<tr>
        <td style="font-weight:600;color:var(--text-primary)">${o.channelOrderId}</td>
        <td>${Store.getChannelIcon(o.channel)} ${o.channel}</td>
        <td style="font-weight:500">${o.customer}</td>
        <td style="font-size:var(--font-xs);color:var(--text-tertiary)">${o.address}</td>
        <td>${o.items.length} item</td>
        <td style="font-weight:600">${Store.formatCurrency(o.total)}</td>
        <td style="font-size:var(--font-xs)">${o.date}</td>
        <td><span class="badge ${Store.getOrderStatusBadge(o.status)}">${Store.getOrderStatusLabel(o.status)}</span></td>
        <td>${o.status !== 'completed' && o.status !== 'cancelled' ? `<button class="btn btn-primary btn-xs" onclick="advanceOrderFromList('${o.id}')">Next →</button>` : ''}</td>
    </tr>`).join('');
}

function filterOrders(el, status) {
    currentOrderFilter = status;
    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('orders-tbody').innerHTML = renderOrderRows(Store.getOrdersByStatus(status));
}

function advanceOrderFromList(orderId) {
    const statusFlow = ['new', 'processing', 'ready', 'shipped', 'completed'];
    const order = Store.orders.find(o => o.id === orderId);
    if (!order) return;
    const idx = statusFlow.indexOf(order.status);
    if (idx < statusFlow.length - 1) {
        order.status = statusFlow[idx + 1];
        showToast('success', 'Order Updated', `${order.channelOrderId} → ${Store.getOrderStatusLabel(order.status)}`);
        renderOrders(document.getElementById('page-content'));
        setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50);
    }
}


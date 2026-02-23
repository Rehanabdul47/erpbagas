/* ===================================
   Finance & Accounting Module
   =================================== */

function renderFinance(container) {
    const totalAR = Store.invoices.filter(i => i.status !== 'paid').reduce((s, i) => s + i.amount, 0);
    const totalAP = Store.purchaseOrders.filter(p => p.status !== 'received').reduce((s, p) => s + p.total, 0);
    const totalRevenue = Store.orders.reduce((s, o) => s + o.total, 0);
    const totalExpenses = Store.getTotalExpenses();
    const netProfit = totalRevenue - totalExpenses;

    container.innerHTML = `
        <div class="page-header">
            <h1 class="page-title"><i data-lucide="wallet"></i> Finance & Accounting</h1>
            <div class="flex gap-3">
                <button class="btn btn-primary btn-sm" onclick="showCreateInvoiceModal()"><i data-lucide="receipt"></i> Buat Invoice</button>
                <button class="btn btn-secondary btn-sm" onclick="showAddExpenseModal()"><i data-lucide="minus-circle"></i> Input Pengeluaran</button>
            </div>
        </div>

        <!-- Finance Stats -->
        <div class="grid-4 mb-6">
            <div class="stat-card" style="--stat-color: var(--success-400)">
                <div class="stat-card-icon" style="background:rgba(16,185,129,0.1)"><i data-lucide="landmark"></i></div>
                <div class="stat-card-value">${Store.formatCurrencyShort(Store.cashBalance)}</div>
                <div class="stat-card-label">Saldo Kas</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--info-400)">
                <div class="stat-card-icon" style="background:rgba(59,130,246,0.1)"><i data-lucide="arrow-down-circle"></i></div>
                <div class="stat-card-value">${Store.formatCurrencyShort(totalAR)}</div>
                <div class="stat-card-label">Piutang (AR)</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--warning-400)">
                <div class="stat-card-icon" style="background:rgba(245,158,11,0.1)"><i data-lucide="arrow-up-circle"></i></div>
                <div class="stat-card-value">${Store.formatCurrencyShort(totalAP)}</div>
                <div class="stat-card-label">Hutang (AP)</div>
            </div>
            <div class="stat-card" style="--stat-color: ${netProfit >= 0 ? 'var(--success-400)' : 'var(--danger-400)'}">
                <div class="stat-card-icon" style="background:${netProfit >= 0 ? 'rgba(16,185,129,0.1)' : 'rgba(239,68,68,0.1)'}"><i data-lucide="trending-up"></i></div>
                <div class="stat-card-value">${Store.formatCurrencyShort(netProfit)}</div>
                <div class="stat-card-label">Net Profit</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchFinanceTab(this,'invoices')">📄 Invoices</button>
            <button class="tab" onclick="switchFinanceTab(this,'expenses')">💸 Pengeluaran</button>
            <button class="tab" onclick="switchFinanceTab(this,'cashflow')">📊 Cash Flow</button>
            <button class="tab" onclick="switchFinanceTab(this,'reports')">📋 Laporan Keuangan</button>
        </div>

        <div id="finance-tab-content">
            ${renderInvoicesTab()}
        </div>
    `;
}

function switchFinanceTab(el, tab) {
    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    const content = document.getElementById('finance-tab-content');
    if (tab === 'invoices') content.innerHTML = renderInvoicesTab();
    else if (tab === 'expenses') content.innerHTML = renderExpensesTab();
    else if (tab === 'cashflow') content.innerHTML = renderCashFlowTab();
    else if (tab === 'reports') content.innerHTML = renderReportsTab();
    if (window.lucide) lucide.createIcons();
    if (tab === 'cashflow') {
        setTimeout(() => {
            drawBarChart('cashflowChart', Store.dailySales.map(d => ({ label: Store.formatDateShort(d.date), value: d.amount })), {
                height: 240, color: '#10B981', colorEnd: '#059669'
            });
        }, 100);
    }
}

function renderInvoicesTab() {
    return `
        <div class="card">
            <div class="table-container">
                <table>
                    <thead><tr><th>Invoice ID</th><th>Customer</th><th>Jumlah</th><th>Tanggal</th><th>Jatuh Tempo</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        ${Store.invoices.map(inv => `
                            <tr>
                                <td style="font-weight:600;color:var(--text-primary)">${inv.id}</td>
                                <td>${inv.customer}</td>
                                <td style="font-weight:600">${Store.formatCurrency(inv.amount)}</td>
                                <td>${inv.issueDate}</td>
                                <td>${inv.dueDate}</td>
                                <td><span class="badge ${Store.getInvoiceStatusBadge(inv.status)}">${inv.status.toUpperCase()}</span></td>
                                <td>
                                    ${inv.status === 'sent' || inv.status === 'overdue' ? `<button class="btn btn-success btn-xs" onclick="markInvoicePaid('${inv.id}')">Bayar</button>` : ''}
                                    ${inv.status === 'draft' ? `<button class="btn btn-primary btn-xs" onclick="sendInvoice('${inv.id}')">Kirim</button>` : ''}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderExpensesTab() {
    return `
        <div class="card">
            <div class="table-container">
                <table>
                    <thead><tr><th>ID</th><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Jumlah</th><th>Bukti</th></tr></thead>
                    <tbody>
                        ${Store.expenses.map(exp => `
                            <tr>
                                <td style="font-weight:600;color:var(--text-primary)">${exp.id}</td>
                                <td>${exp.date}</td>
                                <td><span class="badge badge-neutral">${exp.category}</span></td>
                                <td>${exp.description}</td>
                                <td style="font-weight:600;color:var(--danger-400)">${Store.formatCurrency(exp.amount)}</td>
                                <td>${exp.receipt ? '<span class="badge badge-success">✅ Ada</span>' : '<span class="badge badge-warning">❌ Tidak</span>'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            <div style="padding:var(--space-4);text-align:right;border-top:1px solid var(--border-subtle)">
                <strong>Total Pengeluaran: <span style="color:var(--danger-400)">${Store.formatCurrency(Store.getTotalExpenses())}</span></strong>
            </div>
        </div>
    `;
}

function renderCashFlowTab() {
    return `
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i data-lucide="bar-chart-3"></i> Arus Kas Harian</div>
            </div>
            <div class="chart-container"><canvas id="cashflowChart"></canvas></div>
        </div>
        <div class="grid-2 mt-4">
            <div class="card">
                <div class="card-title mb-4" style="color:var(--success-400)"><i data-lucide="arrow-down-circle"></i> Uang Masuk (Today)</div>
                <div style="font-size:var(--font-2xl);font-weight:800;color:var(--success-400)">${Store.formatCurrency(Store.getTodayRevenue())}</div>
                <p style="color:var(--text-tertiary);font-size:var(--font-sm);margin-top:var(--space-2)">Dari ${Store.getTodayOrders()} order</p>
            </div>
            <div class="card">
                <div class="card-title mb-4" style="color:var(--danger-400)"><i data-lucide="arrow-up-circle"></i> Uang Keluar (Today)</div>
                <div style="font-size:var(--font-2xl);font-weight:800;color:var(--danger-400)">${Store.formatCurrency(3250000)}</div>
                <p style="color:var(--text-tertiary);font-size:var(--font-sm);margin-top:var(--space-2)">Operasional & logistik</p>
            </div>
        </div>
    `;
}

function renderReportsTab() {
    const totalRevenue = Store.orders.reduce((s, o) => s + o.total, 0);
    const totalCogs = Store.orders.reduce((s, o) => s + o.items.reduce((si, item) => {
        const p = Store.getProduct(item.productId);
        return si + (p ? p.cogs * item.qty : 0);
    }, 0), 0);
    const grossProfit = totalRevenue - totalCogs;
    const opex = Store.getTotalExpenses();
    const netProfit = grossProfit - opex;

    return `
        <div class="grid-2">
            <!-- P&L -->
            <div class="card">
                <div class="card-header"><div class="card-title"><i data-lucide="file-text"></i> Laporan Laba Rugi</div></div>
                <table style="width:100%">
                    <tbody>
                        <tr><td style="padding:8px 0">Pendapatan Penjualan</td><td style="text-align:right;font-weight:600">${Store.formatCurrency(totalRevenue)}</td></tr>
                        <tr><td style="padding:8px 0">Harga Pokok Penjualan (COGS)</td><td style="text-align:right;color:var(--danger-400)">(${Store.formatCurrency(totalCogs)})</td></tr>
                        <tr style="border-top:1px solid var(--border-default)"><td style="padding:8px 0;font-weight:700">Laba Kotor</td><td style="text-align:right;font-weight:700;color:var(--success-400)">${Store.formatCurrency(grossProfit)}</td></tr>
                        <tr><td style="padding:8px 0">Beban Operasional</td><td style="text-align:right;color:var(--danger-400)">(${Store.formatCurrency(opex)})</td></tr>
                        <tr style="border-top:2px solid var(--border-strong)"><td style="padding:8px 0;font-weight:800;font-size:var(--font-md)">Laba Bersih</td><td style="text-align:right;font-weight:800;font-size:var(--font-md);color:${netProfit >= 0 ? 'var(--success-400)' : 'var(--danger-400)'}">${Store.formatCurrency(netProfit)}</td></tr>
                    </tbody>
                </table>
            </div>
            <!-- Balance Sheet Summary -->
            <div class="card">
                <div class="card-header"><div class="card-title"><i data-lucide="scale"></i> Neraca (Ringkasan)</div></div>
                <table style="width:100%">
                    <tbody>
                        <tr style="border-bottom:1px solid var(--border-subtle)"><td colspan="2" style="padding:8px 0;font-weight:700;color:var(--primary-400)">ASET</td></tr>
                        <tr><td style="padding:6px 0 6px 16px">Kas & Bank</td><td style="text-align:right;font-weight:600">${Store.formatCurrency(Store.cashBalance)}</td></tr>
                        <tr><td style="padding:6px 0 6px 16px">Piutang Usaha</td><td style="text-align:right;font-weight:600">${Store.formatCurrency(Store.invoices.filter(i => i.status !== 'paid').reduce((s, i) => s + i.amount, 0))}</td></tr>
                        <tr><td style="padding:6px 0 6px 16px">Persediaan</td><td style="text-align:right;font-weight:600">${Store.formatCurrency(Store.products.reduce((s, p) => s + p.stock * p.cogs, 0))}</td></tr>
                        <tr style="border-top:1px solid var(--border-default)"><td style="padding:8px 0;font-weight:700">Total Aset</td><td style="text-align:right;font-weight:700">${Store.formatCurrency(Store.cashBalance + Store.invoices.filter(i => i.status !== 'paid').reduce((s, i) => s + i.amount, 0) + Store.products.reduce((s, p) => s + p.stock * p.cogs, 0))}</td></tr>
                        <tr style="border-bottom:1px solid var(--border-subtle)"><td colspan="2" style="padding:8px 0;font-weight:700;color:var(--warning-400)">KEWAJIBAN</td></tr>
                        <tr><td style="padding:6px 0 6px 16px">Hutang Usaha</td><td style="text-align:right;font-weight:600">${Store.formatCurrency(Store.purchaseOrders.filter(p => p.status !== 'received').reduce((s, p) => s + p.total, 0))}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function showCreateInvoiceModal() {
    showModal('📄 Buat Invoice Baru', `
        <div class="form-group"><label>Customer</label><input type="text" class="form-control" placeholder="Nama customer/perusahaan"></div>
        <div class="form-row">
            <div class="form-group"><label>Jumlah</label><input type="number" class="form-control" placeholder="0" min="0"></div>
            <div class="form-group"><label>Jatuh Tempo</label><input type="date" class="form-control"></div>
        </div>
        <div class="form-group"><label>Keterangan</label><textarea class="form-control" rows="3" placeholder="Detail invoice..."></textarea></div>
    `, `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
        <button class="btn btn-primary" onclick="closeModal();showToast('success','Berhasil','Invoice berhasil dibuat')"><i data-lucide="check"></i> Simpan</button>`);
}

function showAddExpenseModal() {
    showModal('💸 Input Pengeluaran', `
        <div class="form-row">
            <div class="form-group"><label>Kategori</label>
                <select class="form-control"><option>Operasional</option><option>Logistik</option><option>Marketing</option><option>Office Supply</option><option>Gaji</option><option>Lainnya</option></select>
            </div>
            <div class="form-group"><label>Jumlah</label><input type="number" class="form-control" placeholder="0" min="0"></div>
        </div>
        <div class="form-group"><label>Deskripsi</label><input type="text" class="form-control" placeholder="Keterangan pengeluaran"></div>
        <div class="form-group"><label>Upload Bukti (Struk)</label><input type="file" class="form-control" accept="image/*"></div>
    `, `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
        <button class="btn btn-success" onclick="closeModal();showToast('success','Berhasil','Pengeluaran berhasil dicatat')"><i data-lucide="check"></i> Simpan</button>`);
}

function markInvoicePaid(id) {
    const inv = Store.invoices.find(i => i.id === id);
    if (inv) {
        inv.status = 'paid';
        inv.paidDate = new Date().toISOString().slice(0, 10);
        Store.cashBalance += inv.amount;
        showToast('success', 'Invoice Dibayar', `${inv.id} — ${Store.formatCurrency(inv.amount)} diterima`);
        renderFinance(document.getElementById('page-content'));
        setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50);
    }
}

function sendInvoice(id) {
    const inv = Store.invoices.find(i => i.id === id);
    if (inv) {
        inv.status = 'sent';
        showToast('info', 'Invoice Dikirim', `${inv.id} berhasil dikirim ke ${inv.customer}`);
        renderFinance(document.getElementById('page-content'));
        setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50);
    }
}

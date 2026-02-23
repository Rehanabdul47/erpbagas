/* ===================================
   Dashboard Module
   =================================== */

// Default date range: last 14 days
let chartStartDate = '2026-02-08';
let chartEndDate = '2026-02-21';

function renderDashboard(container) {
    const todayOrders = Store.getTodayOrders();
    const pendingOrders = Store.getPendingOrders();
    const readyOrders = Store.orders.filter(o => o.status === 'ready').length;
    const todayRevenue = Store.getTodayRevenue();
    const lowStock = Store.getLowStockProducts().length;
    const outOfStock = Store.getOutOfStockProducts().length;

    container.innerHTML = `
        <!-- Welcome Banner -->
        <div class="dashboard-welcome">
            <div>
                <h2>Selamat Datang, ${Store.currentUser.name} 👋</h2>
                <p>Berikut ringkasan bisnis hari ini — Jumat, 21 Februari 2026</p>
            </div>
            <div class="quick-actions">
                <button class="btn btn-primary btn-sm" onclick="location.hash='#/orders'"><i data-lucide="plus"></i> Order Baru</button>
                <button class="btn btn-secondary btn-sm" onclick="location.hash='#/inventory'"><i data-lucide="package-plus"></i> Tambah Stok</button>
                <button class="btn btn-secondary btn-sm" onclick="location.hash='#/finance'"><i data-lucide="receipt"></i> Buat Invoice</button>
            </div>
        </div>

        <!-- KPI Stats -->
        <div class="dashboard-stats grid-4">
            <div class="stat-card" style="--stat-color: var(--info-400)">
                <div class="stat-card-icon" style="background: rgba(59,130,246,0.1)"><i data-lucide="shopping-bag"></i></div>
                <div class="stat-card-value">${todayOrders}</div>
                <div class="stat-card-label">Order Hari Ini</div>
                <div class="stat-card-trend up"><i data-lucide="trending-up"></i> +12% vs kemarin</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--warning-400)">
                <div class="stat-card-icon" style="background: rgba(245,158,11,0.1)"><i data-lucide="clock"></i></div>
                <div class="stat-card-value">${pendingOrders}</div>
                <div class="stat-card-label">Order Pending</div>
                <div class="stat-card-trend down"><i data-lucide="trending-down"></i> Butuh tindakan</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--success-400)">
                <div class="stat-card-icon" style="background: rgba(16,185,129,0.1)"><i data-lucide="banknote"></i></div>
                <div class="stat-card-value">${Store.formatCurrencyShort(todayRevenue)}</div>
                <div class="stat-card-label">Revenue Hari Ini</div>
                <div class="stat-card-trend up"><i data-lucide="trending-up"></i> +8% vs kemarin</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--danger-400)">
                <div class="stat-card-icon" style="background: rgba(239,68,68,0.1)"><i data-lucide="alert-triangle"></i></div>
                <div class="stat-card-value">${lowStock + outOfStock}</div>
                <div class="stat-card-label">Alert Stok</div>
                <div class="stat-card-trend down"><i data-lucide="arrow-down"></i> ${outOfStock} habis, ${lowStock} rendah</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="dashboard-charts">
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i data-lucide="bar-chart-3"></i> Trend Penjualan</div>
                </div>
                <!-- Date Range Picker -->
                <div style="padding:0 var(--space-4);display:flex;align-items:center;gap:var(--space-3);flex-wrap:wrap">
                    <div style="display:flex;align-items:center;gap:var(--space-2)">
                        <label style="font-size:var(--font-xs);color:var(--text-tertiary);white-space:nowrap">Dari</label>
                        <input type="date" id="chart-date-start" class="form-control" value="${chartStartDate}" onchange="updateSalesChart()" style="padding:6px 10px;font-size:var(--font-xs);width:auto">
                    </div>
                    <span style="color:var(--text-tertiary)">—</span>
                    <div style="display:flex;align-items:center;gap:var(--space-2)">
                        <label style="font-size:var(--font-xs);color:var(--text-tertiary);white-space:nowrap">Sampai</label>
                        <input type="date" id="chart-date-end" class="form-control" value="${chartEndDate}" onchange="updateSalesChart()" style="padding:6px 10px;font-size:var(--font-xs);width:auto">
                    </div>
                    <div style="display:flex;gap:var(--space-2);margin-left:auto">
                        <button class="btn btn-ghost btn-xs" onclick="setChartRange(7)" style="font-size:11px">7 hari</button>
                        <button class="btn btn-ghost btn-xs" onclick="setChartRange(14)" style="font-size:11px">14 hari</button>
                        <button class="btn btn-ghost btn-xs" onclick="setChartRange(0)" style="font-size:11px">Semua</button>
                    </div>
                </div>
                <div style="padding:4px var(--space-4) 0;font-size:var(--font-xs);color:var(--text-tertiary)" id="chart-range-info"></div>
                <div class="chart-container">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i data-lucide="bell"></i> Notifikasi</div>
                    <span class="badge badge-danger">${Store.getUnreadNotifications()} baru</span>
                </div>
                <div class="notification-list">
                    ${Store.notifications.map(n => `
                        <div class="notification-item">
                            <div class="notification-dot ${n.type}"></div>
                            <div class="notification-text">
                                <strong>${n.title}</strong>
                                <small>${n.message}</small>
                            </div>
                            <span class="notification-time">${n.time}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i data-lucide="list-ordered"></i> Order Terbaru</div>
                <a href="#/orders" class="btn btn-ghost btn-sm">Lihat Semua →</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Channel</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Store.orders.slice(0, 5).map(o => `
                            <tr>
                                <td style="color:var(--text-primary);font-weight:600">${o.channelOrderId}</td>
                                <td>${Store.getChannelIcon(o.channel)} ${o.channel}</td>
                                <td>${o.customer}</td>
                                <td>${o.items.length} item</td>
                                <td style="font-weight:600">${Store.formatCurrency(o.total)}</td>
                                <td><span class="badge ${Store.getOrderStatusBadge(o.status)}">${Store.getOrderStatusLabel(o.status)}</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bottom Row: Low Stock & Cash Flow -->
        <div class="grid-2 mt-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i data-lucide="alert-triangle"></i> Stok Menipis</div>
                    <a href="#/inventory" class="btn btn-ghost btn-sm">Lihat Semua →</a>
                </div>
                <div class="table-container">
                    <table>
                        <thead><tr><th>SKU</th><th>Produk</th><th>Stok</th><th>Min</th><th>Status</th></tr></thead>
                        <tbody>
                            ${[...Store.getLowStockProducts(), ...Store.getOutOfStockProducts()].map(p => `
                                <tr>
                                    <td style="font-weight:600;color:var(--text-primary)">${p.id}</td>
                                    <td>${p.name}</td>
                                    <td style="font-weight:700;color:${p.stock === 0 ? 'var(--danger-400)' : 'var(--warning-400)'}">${p.stock}</td>
                                    <td>${p.safetyStock}</td>
                                    <td><span class="badge ${p.stock === 0 ? 'badge-danger' : 'badge-warning'}">${p.stock === 0 ? '🔴 Habis' : '⚠️ Rendah'}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <div class="card-title"><i data-lucide="wallet"></i> Ringkasan Keuangan</div>
                </div>
                <div style="padding: var(--space-3) 0;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:var(--space-4);padding-bottom:var(--space-4);border-bottom:1px solid var(--border-subtle)">
                        <div>
                            <div style="font-size:var(--font-xs);color:var(--text-tertiary)">Saldo Kas</div>
                            <div style="font-size:var(--font-xl);font-weight:800;color:var(--success-400)">${Store.formatCurrency(Store.cashBalance)}</div>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4)">
                        <div>
                            <div style="font-size:var(--font-xs);color:var(--text-tertiary);margin-bottom:4px">Piutang (AR)</div>
                            <div style="font-weight:700;color:var(--info-400)">${Store.formatCurrencyShort(Store.invoices.filter(i => i.status !== 'paid').reduce((s, i) => s + i.amount, 0))}</div>
                        </div>
                        <div>
                            <div style="font-size:var(--font-xs);color:var(--text-tertiary);margin-bottom:4px">Hutang (AP)</div>
                            <div style="font-weight:700;color:var(--warning-400)">${Store.formatCurrencyShort(Store.purchaseOrders.filter(p => p.status !== 'received').reduce((s, p) => s + p.total, 0))}</div>
                        </div>
                        <div>
                            <div style="font-size:var(--font-xs);color:var(--text-tertiary);margin-bottom:4px">Total Revenue</div>
                            <div style="font-weight:700">${Store.formatCurrencyShort(Store.orders.reduce((s, o) => s + o.total, 0))}</div>
                        </div>
                        <div>
                            <div style="font-size:var(--font-xs);color:var(--text-tertiary);margin-bottom:4px">Total Expense</div>
                            <div style="font-weight:700;color:var(--danger-400)">${Store.formatCurrencyShort(Store.getTotalExpenses())}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Draw sales chart with current date range
    setTimeout(() => { updateSalesChart(); }, 100);
}

function updateSalesChart() {
    const startEl = document.getElementById('chart-date-start');
    const endEl = document.getElementById('chart-date-end');
    if (!startEl || !endEl) return;

    chartStartDate = startEl.value;
    chartEndDate = endEl.value;

    const salesData = Store.getSalesByDateRange(chartStartDate, chartEndDate);
    const totalSales = salesData.reduce((s, d) => s + d.amount, 0);
    const avgSales = salesData.length > 0 ? Math.round(totalSales / salesData.length) : 0;

    const infoEl = document.getElementById('chart-range-info');
    if (infoEl) {
        infoEl.innerHTML = `📊 ${salesData.length} hari · Total: <strong style="color:var(--primary-400)">${Store.formatCurrency(totalSales)}</strong> · Rata-rata: <strong>${Store.formatCurrency(avgSales)}</strong>/hari`;
    }

    drawBarChart('salesChart', salesData.map(d => ({
        label: Store.formatDateShort(d.date),
        value: d.amount
    })), {
        height: 220,
        color: '#818CF8',
        colorEnd: '#4F46E5'
    });
}

function setChartRange(days) {
    const startEl = document.getElementById('chart-date-start');
    const endEl = document.getElementById('chart-date-end');
    if (!startEl || !endEl) return;

    endEl.value = '2026-02-21';
    if (days === 0) {
        startEl.value = '2026-02-01';
    } else {
        const start = new Date('2026-02-21');
        start.setDate(start.getDate() - days + 1);
        startEl.value = start.toISOString().slice(0, 10);
    }

    updateSalesChart();
}


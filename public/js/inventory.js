/* ===================================
   Inventory Module
   =================================== */

function renderInventory(container) {
    const totalSku = Store.products.length;
    const totalStock = Store.products.reduce((s, p) => s + p.stock, 0);
    const lowStock = Store.getLowStockProducts().length;
    const outOfStock = Store.getOutOfStockProducts().length;

    container.innerHTML = `
        <div class="page-header">
            <h1 class="page-title"><i data-lucide="package"></i> Inventory & Warehouse</h1>
            <div class="flex gap-3">
                <button class="btn btn-primary btn-sm" onclick="showStockInModal()"><i data-lucide="package-plus"></i> Stock In</button>
                <button class="btn btn-secondary btn-sm" onclick="showStockOutModal()"><i data-lucide="package-minus"></i> Stock Out</button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid-4 mb-6">
            <div class="stat-card" style="--stat-color: var(--primary-400)">
                <div class="stat-card-icon"><i data-lucide="boxes"></i></div>
                <div class="stat-card-value">${totalSku}</div>
                <div class="stat-card-label">Total SKU</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--success-400)">
                <div class="stat-card-icon" style="background:rgba(16,185,129,0.1)"><i data-lucide="archive"></i></div>
                <div class="stat-card-value">${totalStock.toLocaleString()}</div>
                <div class="stat-card-label">Total Stok</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--warning-400)">
                <div class="stat-card-icon" style="background:rgba(245,158,11,0.1)"><i data-lucide="alert-triangle"></i></div>
                <div class="stat-card-value">${lowStock}</div>
                <div class="stat-card-label">Stok Rendah</div>
            </div>
            <div class="stat-card" style="--stat-color: var(--danger-400)">
                <div class="stat-card-icon" style="background:rgba(239,68,68,0.1)"><i data-lucide="x-circle"></i></div>
                <div class="stat-card-value">${outOfStock}</div>
                <div class="stat-card-label">Stok Habis</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" onclick="switchInventoryTab(this, 'stock')">📋 Stock List</button>
            <button class="tab" onclick="switchInventoryTab(this, 'movement')">📊 Movement History</button>
            <button class="tab" onclick="switchInventoryTab(this, 'opname')">🛡️ Stock Opname</button>
        </div>

        <!-- Tab Content -->
        <div id="inventory-tab-content">
            ${renderStockListTab()}
        </div>
    `;
}

function switchInventoryTab(el, tab) {
    document.querySelectorAll('.tabs .tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    const content = document.getElementById('inventory-tab-content');
    if (tab === 'stock') content.innerHTML = renderStockListTab();
    else if (tab === 'movement') content.innerHTML = renderMovementTab();
    else if (tab === 'opname') content.innerHTML = renderOpnameTab();
    if (window.lucide) lucide.createIcons();
}

function renderStockListTab() {
    return `
        <div class="filter-bar">
            <div class="input-icon search-input">
                <i data-lucide="search"></i>
                <input type="text" class="form-control" placeholder="Cari SKU atau nama produk..." onkeyup="filterInventory(this.value)">
            </div>
            <select class="form-control" style="max-width:150px" onchange="filterInventoryCategory(this.value)">
                <option value="all">Semua Kategori</option>
                <option value="Kemeja">Kemeja</option>
                <option value="Celana">Celana</option>
                <option value="Jaket">Jaket</option>
                <option value="Kaos">Kaos</option>
                <option value="Hoodie">Hoodie</option>
                <option value="Polo">Polo</option>
                <option value="Blazer">Blazer</option>
            </select>
            <select class="form-control" style="max-width:150px" onchange="filterInventoryStatus(this.value)">
                <option value="all">Semua Status</option>
                <option value="ok">✅ Aman</option>
                <option value="low">⚠️ Rendah</option>
                <option value="out">🔴 Habis</option>
            </select>
        </div>
        <div class="card">
            <div class="table-container">
                <table id="inventory-table">
                    <thead>
                        <tr>
                            <th>SKU</th>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Safety Stock</th>
                            <th>Harga</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Store.products.map(p => {
        const status = p.stock === 0 ? 'out' : p.stock <= p.safetyStock ? 'low' : 'ok';
        const statusBadge = status === 'out' ? 'badge-danger' : status === 'low' ? 'badge-warning' : 'badge-success';
        const statusLabel = status === 'out' ? '🔴 Habis' : status === 'low' ? '⚠️ Rendah' : '✅ Aman';
        const pct = Math.min(100, (p.stock / (p.safetyStock * 3)) * 100);
        const barColor = status === 'out' ? 'var(--danger-400)' : status === 'low' ? 'var(--warning-400)' : 'var(--success-400)';
        return `<tr data-sku="${p.id}" data-category="${p.category}" data-status="${status}">
                                <td style="font-weight:600;color:var(--text-primary)">${p.id}</td>
                                <td>${p.name}</td>
                                <td><span class="badge badge-neutral">${p.category}</span></td>
                                <td>
                                    <div class="stock-bar">
                                        <span style="font-weight:700;color:${barColor}">${p.stock}</span>
                                        <div class="stock-bar-track"><div class="stock-bar-fill" style="width:${pct}%;background:${barColor}"></div></div>
                                    </div>
                                </td>
                                <td>${p.safetyStock}</td>
                                <td>${Store.formatCurrency(p.price)}</td>
                                <td style="font-size:var(--font-xs)">${p.location}</td>
                                <td><span class="badge ${statusBadge}">${statusLabel}</span></td>
                                <td><button class="btn-icon" title="Detail" onclick="showProductDetail('${p.id}')"><i data-lucide="eye"></i></button></td>
                            </tr>`;
    }).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderMovementTab() {
    return `
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i data-lucide="activity"></i> Riwayat Pergerakan Stok</div>
            </div>
            <div class="table-container">
                <table>
                    <thead><tr><th>Waktu</th><th>SKU</th><th>Produk</th><th>Tipe</th><th>Qty</th><th>Referensi</th><th>Metode</th></tr></thead>
                    <tbody>
                        ${Store.stockMovements.map(m => {
        const product = Store.getProduct(m.productId);
        return `<tr>
                                <td>${m.date}</td>
                                <td style="font-weight:600;color:var(--text-primary)">${m.productId}</td>
                                <td>${product ? product.name : '-'}</td>
                                <td><span class="badge ${m.type === 'in' ? 'badge-success' : 'badge-danger'}">${m.type === 'in' ? '📥 Masuk' : '📤 Keluar'}</span></td>
                                <td style="font-weight:700;color:${m.type === 'in' ? 'var(--success-400)' : 'var(--danger-400)'}">${m.type === 'in' ? '+' : '-'}${m.qty}</td>
                                <td>${m.ref}</td>
                                <td><span class="badge badge-neutral">${m.method}</span></td>
                            </tr>`;
    }).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function renderOpnameTab() {
    return `
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i data-lucide="clipboard-check"></i> Stock Opname</div>
                <button class="btn btn-primary btn-sm" onclick="showToast('info','Info','Fitur stock opname dimulai')"><i data-lucide="play"></i> Mulai Opname</button>
            </div>
            <p style="color:var(--text-secondary);margin-bottom:var(--space-4);font-size:var(--font-sm)">Cocokkan stok fisik dengan stok sistem. Pilih produk dan masukkan jumlah fisik.</p>
            <div class="table-container">
                <table>
                    <thead><tr><th>SKU</th><th>Produk</th><th>Stok Sistem</th><th>Stok Fisik</th><th>Selisih</th><th>Status</th></tr></thead>
                    <tbody>
                        ${Store.products.slice(0, 5).map(p => `
                            <tr>
                                <td style="font-weight:600;color:var(--text-primary)">${p.id}</td>
                                <td>${p.name}</td>
                                <td>${p.stock}</td>
                                <td><input type="number" class="form-control" style="width:80px;padding:4px 8px" value="${p.stock}" min="0"></td>
                                <td style="font-weight:700;color:var(--success-400)">0</td>
                                <td><span class="badge badge-success">✅ Cocok</span></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    `;
}

function filterInventory(query) {
    const rows = document.querySelectorAll('#inventory-table tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query.toLowerCase()) ? '' : 'none';
    });
}

function filterInventoryCategory(cat) {
    const rows = document.querySelectorAll('#inventory-table tbody tr');
    rows.forEach(row => {
        if (cat === 'all') { row.style.display = ''; return; }
        row.style.display = row.dataset.category === cat ? '' : 'none';
    });
}

function filterInventoryStatus(status) {
    const rows = document.querySelectorAll('#inventory-table tbody tr');
    rows.forEach(row => {
        if (status === 'all') { row.style.display = ''; return; }
        row.style.display = row.dataset.status === status ? '' : 'none';
    });
}

function showStockInModal() {
    showModal('📥 Stock In — Barang Masuk', `
        <div class="form-group">
            <label>Produk</label>
            <select class="form-control" id="stockin-product">
                ${Store.products.map(p => `<option value="${p.id}">${p.id} — ${p.name}</option>`).join('')}
            </select>
        </div>
        <div class="form-row">
            <div class="form-group"><label>Jumlah</label><input type="number" class="form-control" id="stockin-qty" min="1" value="1"></div>
            <div class="form-group"><label>Batch No.</label><input type="text" class="form-control" id="stockin-batch" placeholder="B-2026-XXX"></div>
        </div>
        <div class="form-group"><label>Referensi (PO/Manual)</label><input type="text" class="form-control" id="stockin-ref" placeholder="PO-XXX atau keterangan"></div>
    `, `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
        <button class="btn btn-success" onclick="processStockIn()"><i data-lucide="check"></i> Simpan</button>`);
}

function showStockOutModal() {
    showModal('📤 Stock Out — Barang Keluar', `
        <div class="form-group">
            <label>Produk</label>
            <select class="form-control" id="stockout-product">
                ${Store.products.filter(p => p.stock > 0).map(p => `<option value="${p.id}">${p.id} — ${p.name} (stok: ${p.stock})</option>`).join('')}
            </select>
        </div>
        <div class="form-group"><label>Jumlah</label><input type="number" class="form-control" id="stockout-qty" min="1" value="1"></div>
        <div class="form-group"><label>Referensi</label><input type="text" class="form-control" id="stockout-ref" placeholder="Order ID atau keterangan"></div>
    `, `<button class="btn btn-secondary" onclick="closeModal()">Batal</button>
        <button class="btn btn-danger" onclick="processStockOut()"><i data-lucide="check"></i> Simpan</button>`);
}

function processStockIn() {
    const productId = document.getElementById('stockin-product').value;
    const qty = parseInt(document.getElementById('stockin-qty').value);
    const ref = document.getElementById('stockin-ref').value || 'Manual';
    const product = Store.getProduct(productId);
    if (product && qty > 0) {
        product.stock += qty;
        Store.stockMovements.unshift({
            id: 'SM-' + Date.now(), date: new Date().toISOString().slice(0, 16).replace('T', ' '),
            productId, type: 'in', qty, ref, method: 'Manual'
        });
        closeModal();
        showToast('success', 'Stok Ditambahkan', `${qty} unit ${product.name} telah masuk`);
        renderInventory(document.getElementById('page-content'));
        setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50);
    }
}

function processStockOut() {
    const productId = document.getElementById('stockout-product').value;
    const qty = parseInt(document.getElementById('stockout-qty').value);
    const ref = document.getElementById('stockout-ref').value || 'Manual';
    const product = Store.getProduct(productId);
    if (product && qty > 0 && qty <= product.stock) {
        product.stock -= qty;
        Store.stockMovements.unshift({
            id: 'SM-' + Date.now(), date: new Date().toISOString().slice(0, 16).replace('T', ' '),
            productId, type: 'out', qty, ref, method: 'Manual'
        });
        closeModal();
        showToast('success', 'Stok Dikurangi', `${qty} unit ${product.name} telah keluar`);
        renderInventory(document.getElementById('page-content'));
        setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 50);
    } else {
        showToast('error', 'Error', 'Jumlah melebihi stok tersedia');
    }
}

function showProductDetail(id) {
    const p = Store.getProduct(id);
    if (!p) return;
    const status = p.stock === 0 ? 'Habis' : p.stock <= p.safetyStock ? 'Rendah' : 'Aman';
    showModal(`Detail Produk — ${p.id}`, `
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--space-4)">
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">Nama</label><p style="font-weight:600">${p.name}</p></div>
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">Kategori</label><p>${p.category}</p></div>
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">Stok Saat Ini</label><p style="font-weight:700;font-size:var(--font-xl)">${p.stock}</p></div>
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">Safety Stock</label><p>${p.safetyStock}</p></div>
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">Harga Jual</label><p style="font-weight:600">${Store.formatCurrency(p.price)}</p></div>
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">COGS</label><p>${Store.formatCurrency(p.cogs)}</p></div>
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">Batch</label><p>${p.batch || '-'}</p></div>
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">Lokasi</label><p>${p.location}</p></div>
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">Status</label><p style="font-weight:600">${status}</p></div>
            <div><label style="color:var(--text-tertiary);font-size:var(--font-xs)">Margin</label><p style="color:var(--success-400);font-weight:600">${((1 - p.cogs / p.price) * 100).toFixed(0)}%</p></div>
        </div>
    `, `<button class="btn btn-secondary" onclick="closeModal()">Tutup</button>`);
}

/* ===================================
   GUPALA ERP — Mock Data Store
   =================================== */

const Store = {
    // --- Auth State ---
    currentUser: null,

    users: {
        super_admin: { name: 'Admin Utama', email: 'admin@gupala.com', role: 'super_admin', roleName: 'Executive', department: 'all' },
        manager: { name: 'Budi Santoso', email: 'budi@gupala.com', role: 'manager', roleName: 'Manager', department: 'all' },
        staff_warehouse: { name: 'Agus Pramono', email: 'agus@gupala.com', role: 'staff_warehouse', roleName: 'Staff Gudang', department: 'warehouse' },
        staff_finance: { name: 'Siti Rahayu', email: 'siti@gupala.com', role: 'staff_finance', roleName: 'Staff Finance', department: 'finance' },
        staff_sales: { name: 'Dewi Lestari', email: 'dewi@gupala.com', role: 'staff_sales', roleName: 'Staff Sales', department: 'sales' },
        staff_hr: { name: 'Rina Wati', email: 'rina@gupala.com', role: 'staff_hr', roleName: 'Staff HR', department: 'hr' },
    },

    // --- Products / Inventory ---
    products: [
        { id: 'GUP-001', name: 'Kemeja Formal Putih L', category: 'Kemeja', stock: 150, safetyStock: 20, price: 250000, cogs: 125000, batch: 'B-2024-001', expiry: null, location: 'Rak A1' },
        { id: 'GUP-002', name: 'Celana Chino Navy M', category: 'Celana', stock: 45, safetyStock: 15, price: 320000, cogs: 160000, batch: 'B-2024-002', expiry: null, location: 'Rak A2' },
        { id: 'GUP-003', name: 'Jaket Bomber Black XL', category: 'Jaket', stock: 5, safetyStock: 10, price: 450000, cogs: 225000, batch: 'B-2024-003', expiry: null, location: 'Rak B1' },
        { id: 'GUP-004', name: 'Kaos Polos Hitam S', category: 'Kaos', stock: 0, safetyStock: 5, price: 95000, cogs: 45000, batch: 'B-2024-004', expiry: null, location: 'Rak B2' },
        { id: 'GUP-005', name: 'Kemeja Batik Modern M', category: 'Kemeja', stock: 88, safetyStock: 15, price: 380000, cogs: 190000, batch: 'B-2024-005', expiry: null, location: 'Rak A3' },
        { id: 'GUP-006', name: 'Celana Jeans Slim Fit 32', category: 'Celana', stock: 62, safetyStock: 20, price: 285000, cogs: 142000, batch: 'B-2024-006', expiry: null, location: 'Rak A4' },
        { id: 'GUP-007', name: 'Hoodie Basic Grey L', category: 'Hoodie', stock: 33, safetyStock: 10, price: 275000, cogs: 135000, batch: 'B-2024-007', expiry: null, location: 'Rak C1' },
        { id: 'GUP-008', name: 'Polo Shirt Navy XL', category: 'Polo', stock: 8, safetyStock: 10, price: 195000, cogs: 95000, batch: 'B-2024-008', expiry: null, location: 'Rak C2' },
        { id: 'GUP-009', name: 'Blazer Formal Navy L', category: 'Blazer', stock: 22, safetyStock: 5, price: 650000, cogs: 325000, batch: 'B-2024-009', expiry: null, location: 'Rak D1' },
        { id: 'GUP-010', name: 'Kemeja Flanel Merah L', category: 'Kemeja', stock: 41, safetyStock: 10, price: 220000, cogs: 110000, batch: 'B-2024-010', expiry: null, location: 'Rak A5' },
    ],

    stockMovements: [
        { id: 'SM-001', date: '2026-02-21 09:30', productId: 'GUP-001', type: 'out', qty: 1, ref: 'Order #TK-12345', method: 'Auto' },
        { id: 'SM-002', date: '2026-02-21 09:15', productId: 'GUP-002', type: 'in', qty: 50, ref: 'PO-001', method: 'Manual' },
        { id: 'SM-003', date: '2026-02-21 08:45', productId: 'GUP-003', type: 'out', qty: 2, ref: 'Order #SP-67890', method: 'Auto' },
        { id: 'SM-004', date: '2026-02-20 16:00', productId: 'GUP-005', type: 'in', qty: 30, ref: 'PO-002', method: 'Manual' },
        { id: 'SM-005', date: '2026-02-20 14:20', productId: 'GUP-007', type: 'out', qty: 3, ref: 'Order #TK-12350', method: 'Auto' },
    ],

    // --- Orders ---
    orders: [
        { id: 'ORD-2026-001', channel: 'TikTok', channelOrderId: '#TK-2026022101', customer: 'Budi Santoso', address: 'Jakarta Selatan', items: [{ productId: 'GUP-001', qty: 2, price: 250000 }, { productId: 'GUP-005', qty: 1, price: 380000 }], total: 880000, status: 'new', date: '2026-02-21 10:30', tracking: null },
        { id: 'ORD-2026-002', channel: 'Shopee', channelOrderId: '#SP-2026022102', customer: 'Siti Rahayu', address: 'Bandung', items: [{ productId: 'GUP-002', qty: 1, price: 320000 }], total: 320000, status: 'ready', date: '2026-02-21 10:15', tracking: null },
        { id: 'ORD-2026-003', channel: 'TikTok', channelOrderId: '#TK-2026022103', customer: 'Ahmad Wijaya', address: 'Surabaya', items: [{ productId: 'GUP-006', qty: 2, price: 285000 }, { productId: 'GUP-007', qty: 3, price: 275000 }], total: 1395000, status: 'shipped', date: '2026-02-21 09:45', tracking: 'JNE-12345678' },
        { id: 'ORD-2026-004', channel: 'Tokopedia', channelOrderId: '#TP-2026022104', customer: 'Diana Putri', address: 'Medan', items: [{ productId: 'GUP-009', qty: 1, price: 650000 }], total: 650000, status: 'completed', date: '2026-02-20 08:00', tracking: 'SiCepat-87654' },
        { id: 'ORD-2026-005', channel: 'Shopee', channelOrderId: '#SP-2026022105', customer: 'Rudi Hermawan', address: 'Semarang', items: [{ productId: 'GUP-010', qty: 1, price: 220000 }, { productId: 'GUP-001', qty: 1, price: 250000 }], total: 470000, status: 'processing', date: '2026-02-21 11:00', tracking: null },
        { id: 'ORD-2026-006', channel: 'TikTok', channelOrderId: '#TK-2026022106', customer: 'Lina Susanti', address: 'Yogyakarta', items: [{ productId: 'GUP-005', qty: 2, price: 380000 }], total: 760000, status: 'new', date: '2026-02-21 11:30', tracking: null },
        { id: 'ORD-2026-007', channel: 'Shopee', channelOrderId: '#SP-2026022107', customer: 'Eko Prasetyo', address: 'Malang', items: [{ productId: 'GUP-007', qty: 1, price: 275000 }], total: 275000, status: 'cancelled', date: '2026-02-19 14:00', tracking: null },
        { id: 'ORD-2026-008', channel: 'TikTok', channelOrderId: '#TK-2026022108', customer: 'Fitri Handayani', address: 'Balikpapan', items: [{ productId: 'GUP-006', qty: 1, price: 285000 }, { productId: 'GUP-002', qty: 1, price: 320000 }], total: 605000, status: 'ready', date: '2026-02-21 07:15', tracking: null },
    ],

    // --- Finance ---
    invoices: [
        { id: 'INV-2026-001', orderId: 'ORD-2026-004', customer: 'Diana Putri', amount: 650000, status: 'paid', issueDate: '2026-02-20', dueDate: '2026-03-20', paidDate: '2026-02-20' },
        { id: 'INV-2026-002', orderId: 'ORD-2026-003', customer: 'Ahmad Wijaya', amount: 1395000, status: 'sent', issueDate: '2026-02-21', dueDate: '2026-03-21', paidDate: null },
        { id: 'INV-2026-003', orderId: null, customer: 'PT Maju Jaya', amount: 5200000, status: 'overdue', issueDate: '2026-01-15', dueDate: '2026-02-15', paidDate: null },
        { id: 'INV-2026-004', orderId: null, customer: 'CV Berkah', amount: 3800000, status: 'draft', issueDate: '2026-02-21', dueDate: '2026-03-21', paidDate: null },
    ],

    expenses: [
        { id: 'EXP-001', date: '2026-02-21', category: 'Operasional', description: 'Listrik & Internet Kantor', amount: 2500000, receipt: true },
        { id: 'EXP-002', date: '2026-02-20', category: 'Logistik', description: 'Bensin Operasional Kurir', amount: 750000, receipt: true },
        { id: 'EXP-003', date: '2026-02-19', category: 'Marketing', description: 'Facebook Ads Campaign', amount: 3000000, receipt: true },
        { id: 'EXP-004', date: '2026-02-18', category: 'Office Supply', description: 'Kertas, Tinta Printer', amount: 450000, receipt: false },
        { id: 'EXP-005', date: '2026-02-17', category: 'Gaji', description: 'Payroll Februari 2026', amount: 45000000, receipt: true },
    ],

    cashBalance: 125800000,

    // --- Sales Pipeline ---
    pipeline: [
        { id: 'LEAD-001', company: 'PT Mitra Abadi', contact: 'Hendra', value: 15000000, stage: 'lead', notes: 'Tertarik seragam kantor', date: '2026-02-18' },
        { id: 'LEAD-002', company: 'CV Sukses Makmur', contact: 'Widi', value: 8500000, stage: 'negotiation', notes: 'Negosiasi harga bulk order', date: '2026-02-15' },
        { id: 'LEAD-003', company: 'PT Sentosa', contact: 'Ayu', value: 22000000, stage: 'proposal', notes: 'Proposal sent, waiting response', date: '2026-02-10' },
        { id: 'LEAD-004', company: 'Toko Jaya', contact: 'Deni', value: 3200000, stage: 'won', notes: 'Deal closed!', date: '2026-02-08' },
        { id: 'LEAD-005', company: 'PT Global', contact: 'Rosa', value: 12000000, stage: 'lead', notes: 'Cold call follow up', date: '2026-02-20' },
        { id: 'LEAD-006', company: 'UD Mandiri', contact: 'Tono', value: 5000000, stage: 'lost', notes: 'Budget terlalu tinggi', date: '2026-02-05' },
        { id: 'LEAD-007', company: 'CV Harmoni', contact: 'Lisa', value: 9500000, stage: 'negotiation', notes: 'Diskusi custom design', date: '2026-02-19' },
    ],

    customers: [
        { id: 'CUST-001', name: 'Budi Santoso', email: 'budi@email.com', phone: '081234567890', totalOrders: 12, totalSpent: 8500000, lastOrder: '2026-02-21' },
        { id: 'CUST-002', name: 'Siti Rahayu', email: 'siti@email.com', phone: '082345678901', totalOrders: 8, totalSpent: 4200000, lastOrder: '2026-02-21' },
        { id: 'CUST-003', name: 'Ahmad Wijaya', email: 'ahmad@email.com', phone: '083456789012', totalOrders: 5, totalSpent: 6800000, lastOrder: '2026-02-21' },
        { id: 'CUST-004', name: 'Diana Putri', email: 'diana@email.com', phone: '084567890123', totalOrders: 3, totalSpent: 2100000, lastOrder: '2026-02-20' },
        { id: 'CUST-005', name: 'Rudi Hermawan', email: 'rudi@email.com', phone: '085678901234', totalOrders: 15, totalSpent: 12500000, lastOrder: '2026-02-21' },
    ],

    // --- HRM ---
    employees: [
        { id: 'EMP-001', name: 'Admin Utama', department: 'IT', position: 'System Admin', salary: 12000000, status: 'active', joinDate: '2023-01-15', kpiScore: 92 },
        { id: 'EMP-002', name: 'Budi Santoso', department: 'Management', position: 'Operations Manager', salary: 15000000, status: 'active', joinDate: '2022-06-01', kpiScore: 88 },
        { id: 'EMP-003', name: 'Agus Pramono', department: 'Warehouse', position: 'Warehouse Staff', salary: 5500000, status: 'active', joinDate: '2024-03-10', kpiScore: 85 },
        { id: 'EMP-004', name: 'Siti Rahayu', department: 'Finance', position: 'Accountant', salary: 8000000, status: 'active', joinDate: '2023-08-20', kpiScore: 95 },
        { id: 'EMP-005', name: 'Dewi Lestari', department: 'Sales', position: 'Sales Executive', salary: 7000000, status: 'active', joinDate: '2024-01-05', kpiScore: 78 },
        { id: 'EMP-006', name: 'Rina Wati', department: 'HR', position: 'HR Officer', salary: 7500000, status: 'active', joinDate: '2023-11-12', kpiScore: 90 },
        { id: 'EMP-007', name: 'Joko Susilo', department: 'Warehouse', position: 'Warehouse Lead', salary: 7000000, status: 'active', joinDate: '2022-09-15', kpiScore: 82 },
        { id: 'EMP-008', name: 'Maya Sari', department: 'Sales', position: 'Sales Admin', salary: 5000000, status: 'on_leave', joinDate: '2025-02-01', kpiScore: 70 },
    ],

    attendance: [
        { empId: 'EMP-001', date: '2026-02-21', checkIn: '08:02', checkOut: '17:05', status: 'present' },
        { empId: 'EMP-002', date: '2026-02-21', checkIn: '07:55', checkOut: '17:30', status: 'present' },
        { empId: 'EMP-003', date: '2026-02-21', checkIn: '08:15', checkOut: '17:00', status: 'late' },
        { empId: 'EMP-004', date: '2026-02-21', checkIn: '08:00', checkOut: '17:10', status: 'present' },
        { empId: 'EMP-005', date: '2026-02-21', checkIn: '08:30', checkOut: null, status: 'late' },
        { empId: 'EMP-006', date: '2026-02-21', checkIn: '07:50', checkOut: '17:00', status: 'present' },
        { empId: 'EMP-007', date: '2026-02-21', checkIn: null, checkOut: null, status: 'absent' },
        { empId: 'EMP-008', date: '2026-02-21', checkIn: null, checkOut: null, status: 'on_leave' },
    ],

    // --- Procurement ---
    vendors: [
        { id: 'VND-001', name: 'PT Tekstil Jaya', contact: 'Haris', phone: '021-5551234', email: 'haris@tekstiljaya.com', rating: 4.5, totalPO: 24, address: 'Bandung' },
        { id: 'VND-002', name: 'CV Bahan Kain Indo', contact: 'Lina', phone: '021-5555678', email: 'lina@bahankaindo.com', rating: 4.2, totalPO: 18, address: 'Solo' },
        { id: 'VND-003', name: 'UD Garment Supply', contact: 'Roni', phone: '021-5559012', email: 'roni@garmentsupply.com', rating: 3.8, totalPO: 10, address: 'Jakarta' },
    ],

    purchaseOrders: [
        { id: 'PO-2026-001', vendorId: 'VND-001', items: [{ name: 'Kain Katun Premium', qty: 200, unit: 'meter', price: 45000 }], total: 9000000, status: 'received', orderDate: '2026-02-10', receivedDate: '2026-02-18' },
        { id: 'PO-2026-002', vendorId: 'VND-002', items: [{ name: 'Benang Jahit Industrial', qty: 50, unit: 'gulung', price: 25000 }, { name: 'Kancing Premium', qty: 500, unit: 'pcs', price: 1500 }], total: 2000000, status: 'partial', orderDate: '2026-02-15', receivedDate: null },
        { id: 'PO-2026-003', vendorId: 'VND-001', items: [{ name: 'Kain Denim Stretch', qty: 150, unit: 'meter', price: 65000 }], total: 9750000, status: 'submitted', orderDate: '2026-02-20', receivedDate: null },
        { id: 'PO-2026-004', vendorId: 'VND-003', items: [{ name: 'Label Woven Custom', qty: 1000, unit: 'pcs', price: 2000 }], total: 2000000, status: 'draft', orderDate: '2026-02-21', receivedDate: null },
    ],

    // --- Notifications ---
    notifications: [
        { id: 1, type: 'warning', title: 'Stok Rendah', message: 'Jaket Bomber Black XL tersisa 5 unit', time: '10 menit lalu', read: false },
        { id: 2, type: 'danger', title: 'Stok Habis', message: 'Kaos Polos Hitam S stok 0', time: '30 menit lalu', read: false },
        { id: 3, type: 'info', title: 'Order Baru', message: '2 order baru dari TikTok Shop', time: '1 jam lalu', read: false },
        { id: 4, type: 'warning', title: 'Invoice Overdue', message: 'INV-2026-003 sudah jatuh tempo', time: '2 jam lalu', read: false },
        { id: 5, type: 'success', title: 'PO Diterima', message: 'PO-2026-001 telah diterima penuh', time: '5 jam lalu', read: true },
    ],

    // --- Daily Sales Chart Data (Feb 2026) ---
    dailySales: [
        { date: '2026-02-01', amount: 2800000 },
        { date: '2026-02-02', amount: 3100000 },
        { date: '2026-02-03', amount: 4500000 },
        { date: '2026-02-04', amount: 3900000 },
        { date: '2026-02-05', amount: 5100000 },
        { date: '2026-02-06', amount: 4700000 },
        { date: '2026-02-07', amount: 6200000 },
        { date: '2026-02-08', amount: 3200000 },
        { date: '2026-02-09', amount: 4100000 },
        { date: '2026-02-10', amount: 3800000 },
        { date: '2026-02-11', amount: 5200000 },
        { date: '2026-02-12', amount: 4600000 },
        { date: '2026-02-13', amount: 5800000 },
        { date: '2026-02-14', amount: 7200000 },
        { date: '2026-02-15', amount: 6100000 },
        { date: '2026-02-16', amount: 4900000 },
        { date: '2026-02-17', amount: 5500000 },
        { date: '2026-02-18', amount: 6300000 },
        { date: '2026-02-19', amount: 7800000 },
        { date: '2026-02-20', amount: 8200000 },
        { date: '2026-02-21', amount: 4500000 },
    ],

    // --- Helper Functions ---
    formatCurrency(amount) {
        return 'Rp ' + amount.toLocaleString('id-ID');
    },

    formatCurrencyShort(amount) {
        if (amount >= 1000000000) return 'Rp ' + (amount / 1000000000).toFixed(1) + ' M';
        if (amount >= 1000000) return 'Rp ' + (amount / 1000000).toFixed(1) + ' Jt';
        if (amount >= 1000) return 'Rp ' + (amount / 1000).toFixed(0) + ' K';
        return 'Rp ' + amount;
    },

    getProduct(id) {
        return this.products.find(p => p.id === id);
    },

    getVendor(id) {
        return this.vendors.find(v => v.id === id);
    },

    getEmployee(id) {
        return this.employees.find(e => e.id === id);
    },

    getLowStockProducts() {
        return this.products.filter(p => p.stock <= p.safetyStock && p.stock > 0);
    },

    getOutOfStockProducts() {
        return this.products.filter(p => p.stock === 0);
    },

    getOrdersByStatus(status) {
        if (status === 'all') return this.orders;
        return this.orders.filter(o => o.status === status);
    },

    getOrderStatusLabel(status) {
        const labels = {
            new: '🆕 Baru',
            processing: '⏳ Diproses',
            ready: '📦 Siap Kirim',
            shipped: '🚚 Dikirim',
            completed: '✅ Selesai',
            cancelled: '❌ Dibatalkan'
        };
        return labels[status] || status;
    },

    getOrderStatusBadge(status) {
        const badges = {
            new: 'badge-info',
            processing: 'badge-warning',
            ready: 'badge-primary',
            shipped: 'badge-info',
            completed: 'badge-success',
            cancelled: 'badge-danger'
        };
        return badges[status] || 'badge-neutral';
    },

    getChannelIcon(channel) {
        const icons = {
            TikTok: '🎵',
            Shopee: '🛒',
            Tokopedia: '🟢'
        };
        return icons[channel] || '📦';
    },

    getChannelColor(channel) {
        const colors = {
            TikTok: '#000000',
            Shopee: '#EE4D2D',
            Tokopedia: '#42B549'
        };
        return colors[channel] || '#6366F1';
    },

    getInvoiceStatusBadge(status) {
        const badges = {
            draft: 'badge-neutral',
            sent: 'badge-info',
            paid: 'badge-success',
            overdue: 'badge-danger'
        };
        return badges[status] || 'badge-neutral';
    },

    getPOStatusBadge(status) {
        const badges = {
            draft: 'badge-neutral',
            submitted: 'badge-info',
            partial: 'badge-warning',
            received: 'badge-success'
        };
        return badges[status] || 'badge-neutral';
    },

    getTotalRevenue() {
        return this.orders.filter(o => o.status === 'completed').reduce((sum, o) => sum + o.total, 0);
    },

    getTotalExpenses() {
        return this.expenses.reduce((sum, e) => sum + e.amount, 0);
    },

    getPendingOrders() {
        return this.orders.filter(o => ['new', 'processing'].includes(o.status)).length;
    },

    getTodayOrders() {
        return this.orders.filter(o => o.date.startsWith('2026-02-21')).length;
    },

    getTodayRevenue() {
        return this.orders.filter(o => o.date.startsWith('2026-02-21')).reduce((sum, o) => sum + o.total, 0);
    },

    getUnreadNotifications() {
        return this.notifications.filter(n => !n.read).length;
    },

    getSalesByDateRange(startDate, endDate) {
        return this.dailySales.filter(d => d.date >= startDate && d.date <= endDate);
    },

    formatDateShort(isoDate) {
        const d = new Date(isoDate);
        return d.getDate() + ' ' + ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'][d.getMonth()];
    }
};

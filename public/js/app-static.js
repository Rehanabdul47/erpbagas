/* ===================================
   App Static — Shared Utilities
   =================================== */

Store.currentUser = Store.users.super_admin;

function toggleSidebar() { document.body.classList.toggle('sidebar-collapsed'); }
function toggleMobileSidebar() { document.getElementById('sidebar').classList.toggle('active'); }

function showToast(type, title, message) {
    const container = document.getElementById('toast-container');
    const icons = { success: 'check-circle', error: 'x-circle', warning: 'alert-triangle', info: 'info' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="toast-icon"><i data-lucide="${icons[type] || 'info'}"></i></div>
        <div class="toast-content"><div class="toast-title">${title}</div><div class="toast-message">${message}</div></div>
        <button class="toast-close" onclick="this.parentElement.remove()"><i data-lucide="x"></i></button>`;
    container.appendChild(toast);
    if (window.lucide) lucide.createIcons();
    setTimeout(() => toast.remove(), 4000);
}

function showModal(title, bodyHtml, footerHtml) {
    const overlay = document.getElementById('modal-overlay');
    document.getElementById('modal-header').innerHTML = `<h3>${title}</h3><button class="btn-icon" onclick="closeModal()"><i data-lucide="x"></i></button>`;
    document.getElementById('modal-body').innerHTML = bodyHtml;
    document.getElementById('modal-footer').innerHTML = footerHtml || '';
    overlay.classList.remove('hidden');
    if (window.lucide) lucide.createIcons();
}
function closeModal() { document.getElementById('modal-overlay').classList.add('hidden'); }

function drawBarChart(canvasId, data, options = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1;
    const rect = canvas.parentElement.getBoundingClientRect();
    const w = rect.width, h = options.height || 220;
    canvas.width = w * dpr; canvas.height = h * dpr;
    canvas.style.width = w + 'px'; canvas.style.height = h + 'px';
    ctx.scale(dpr, dpr);
    const padding = { top: 20, right: 20, bottom: 40, left: 60 };
    const chartW = w - padding.left - padding.right, chartH = h - padding.top - padding.bottom;
    const values = data.map(d => d.value);
    const maxVal = Math.max(...values) * 1.15;
    const barWidth = Math.min(32, (chartW / data.length) * 0.6);
    const gap = (chartW - barWidth * data.length) / data.length;
    ctx.strokeStyle = 'rgba(148,163,184,0.08)'; ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
        const y = padding.top + (chartH / 4) * i;
        ctx.beginPath(); ctx.moveTo(padding.left, y); ctx.lineTo(w - padding.right, y); ctx.stroke();
        ctx.fillStyle = '#64748B'; ctx.font = '10px Inter'; ctx.textAlign = 'right';
        ctx.fillText(Store.formatCurrencyShort(maxVal - (maxVal / 4) * i), padding.left - 8, y + 3);
    }
    data.forEach((d, i) => {
        const x = padding.left + gap / 2 + i * (barWidth + gap);
        const barH = (d.value / maxVal) * chartH, y = padding.top + chartH - barH;
        const grad = ctx.createLinearGradient(x, y, x, y + barH);
        grad.addColorStop(0, options.color || '#6366F1'); grad.addColorStop(1, options.colorEnd || '#4F46E5');
        ctx.fillStyle = grad; ctx.beginPath(); ctx.roundRect(x, y, barWidth, barH, [4, 4, 0, 0]); ctx.fill();
        ctx.fillStyle = '#64748B'; ctx.font = '10px Inter'; ctx.textAlign = 'center';
        ctx.fillText(d.label, x + barWidth / 2, h - padding.bottom + 16);
    });
}

function drawDonutChart(canvasId, segments, options = {}) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dpr = window.devicePixelRatio || 1, size = options.size || 180;
    canvas.width = size * dpr; canvas.height = size * dpr;
    canvas.style.width = size + 'px'; canvas.style.height = size + 'px';
    ctx.scale(dpr, dpr);
    const cx = size / 2, cy = size / 2, radius = size / 2 - 10, innerRadius = radius * 0.6;
    const total = segments.reduce((s, seg) => s + seg.value, 0);
    let startAngle = -Math.PI / 2;
    segments.forEach(seg => {
        const sliceAngle = (seg.value / total) * Math.PI * 2;
        ctx.beginPath(); ctx.arc(cx, cy, radius, startAngle, startAngle + sliceAngle);
        ctx.arc(cx, cy, innerRadius, startAngle + sliceAngle, startAngle, true);
        ctx.closePath(); ctx.fillStyle = seg.color; ctx.fill(); startAngle += sliceAngle;
    });
    if (options.centerText) {
        ctx.fillStyle = '#F1F5F9'; ctx.font = 'bold 16px Inter'; ctx.textAlign = 'center';
        ctx.fillText(options.centerText, cx, cy);
        if (options.centerSubText) { ctx.fillStyle = '#94A3B8'; ctx.font = '10px Inter'; ctx.fillText(options.centerSubText, cx, cy + 16); }
    }
}

function markAllRead() {
    Store.notifications.forEach(n => n.read = true);
    const badge = document.getElementById('notification-badge');
    if (badge) { badge.textContent = '0'; badge.style.display = 'none'; }
    showToast('info', 'Notifikasi', 'Semua notifikasi ditandai dibaca');
}

function initStaticApp() {
    const now = new Date();
    const dateEl = document.getElementById('header-date');
    if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    const badge = document.getElementById('notification-badge');
    if (badge) { const c = Store.getUnreadNotifications(); badge.textContent = c; if (c === 0) badge.style.display = 'none'; }
    const st = document.getElementById('sidebar-toggle'); if (st) st.addEventListener('click', toggleSidebar);
    const mb = document.getElementById('mobile-menu-btn'); if (mb) mb.addEventListener('click', toggleMobileSidebar);
    const nb = document.getElementById('notification-btn');
    if (nb) nb.addEventListener('click', () => document.getElementById('notification-dropdown').classList.toggle('hidden'));
    const mo = document.getElementById('modal-overlay');
    if (mo) mo.addEventListener('click', (e) => { if (e.target.id === 'modal-overlay') closeModal(); });
    if (window.lucide) lucide.createIcons();
}

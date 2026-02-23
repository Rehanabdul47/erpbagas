@extends('layouts.app')

@section('title', 'Point of Sale')

@section('content')
<div class="card" style="margin-bottom:20px;padding:8px">
    <div class="tabs" style="display:flex;gap:0;border:none;margin-bottom:0">
        <button class="tab-btn active" onclick="switchMainTab('kasir', this)"><i data-lucide="monitor" style="width:16px"></i> Kasir (POS)</button>
        <button class="tab-btn" onclick="switchMainTab('history', this)"><i data-lucide="history" style="width:16px"></i> Riwayat Transaksi</button>
    </div>
</div>

<div id="main-tab-kasir" class="main-tab-content">
<div class="pos-container" style="display:grid;grid-template-columns:1fr 380px;gap:20px;height:calc(100vh - 180px)">
    
    {{-- Left Section: Products --}}
    <div class="pos-main card" style="display:flex;flex-direction:column;overflow:hidden;margin-bottom:0">
        <div class="pos-header" style="padding:16px;border-bottom:1px solid var(--border-color);display:flex;gap:12px;align-items:center">
            <div style="position:relative;flex:1">
                <i data-lucide="search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;color:var(--text-tertiary)"></i>
                <input type="text" id="pos-search" class="form-control" placeholder="Cari nama produk atau scan barcode..." style="padding-left:36px" onkeyup="filterProducts()">
            </div>
            <select id="pos-category" class="form-control" style="width:180px" onchange="filterProducts()">
                <option value="">Semua Kategori</option>
                @foreach($products->pluck('category.name')->unique() as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        
        <div id="product-grid" style="flex:1;overflow-y:auto;padding:16px;display:grid;grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));gap:16px">
            @foreach($products as $p)
            <div class="product-item card" 
                 style="cursor:pointer;padding:12px;transition:all 0.2s;border:1px solid transparent"
                 data-id="{{ $p->id }}" 
                 data-name="{{ strtolower($p->name) }}" 
                 data-sku="{{ strtolower($p->sku) }}"
                 data-category="{{ $p->category->name }}"
                 data-price="{{ $p->price }}"
                 onclick="addToCart({{ json_encode($p) }})">
                <div style="aspect-ratio:1;background:var(--bg-tertiary);border-radius:var(--radius-md);overflow:hidden;display:flex;align-items:center;justify-content:center;margin-bottom:8px">
                    @if($p->image)
                        <img src="{{ Str::startsWith($p->image, 'http') ? $p->image : asset($p->image) }}" alt="{{ $p->name }}" style="width:100%;height:100%;object-fit:cover">
                    @else
                        <i data-lucide="package" style="width:32px;height:32px;color:var(--primary-300)"></i>
                    @endif
                </div>
                <div style="font-weight:600;font-size:14px;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $p->name }}</div>
                <div style="display:flex;justify-content:space-between;align-items:center">
                    <span style="color:var(--primary-400);font-weight:700;font-size:13px">Rp {{ number_format($p->price, 0, ',', '.') }}</span>
                    <span style="font-size:11px;color:var(--text-tertiary)">{{ $p->stock }} {{ $p->unit ?? 'pcs' }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Right Section: Cart --}}
    <div class="pos-sidebar card" style="display:flex;flex-direction:column;overflow:hidden;margin-bottom:0;position:relative">
        <div style="padding:16px;border-bottom:1px solid var(--border-color)">
            <h4 style="margin:0;display:flex;align-items:center;gap:8px"><i data-lucide="shopping-cart"></i> Detail Pesanan</h4>
        </div>
        
        <div style="padding:12px;display:flex;gap:8px">
            <select id="cart-customer" class="form-control" style="flex:1">
                @foreach($customers as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <button class="btn btn-ghost" onclick="document.getElementById('modal-customer').classList.remove('hidden')"><i data-lucide="user-plus"></i></button>
        </div>

        <div id="cart-items" style="flex:1;overflow-y:auto;padding:0 16px">
            {{-- Items will be injected here --}}
            <div id="empty-cart" style="height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--text-tertiary);opacity:0.6">
                <i data-lucide="shopping-bag" style="width:48px;height:48px;margin-bottom:12px"></i>
                <p>Keranjang masih kosong</p>
            </div>
        </div>

        <div class="cart-footer" style="padding:20px;background:var(--bg-surface);border-top:1px solid var(--border-color)">
            <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:14px;color:var(--text-secondary)">
                <span>Subtotal</span>
                <span id="cart-subtotal">Rp 0</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-bottom:16px;font-size:20px;font-weight:800;color:var(--text-primary)">
                <span>Total</span>
                <span id="cart-total">Rp 0</span>
            </div>
            <button class="btn btn-primary" style="width:100%;padding:14px;font-size:16px;font-weight:700" onclick="openCheckoutModal()">
                <i data-lucide="credit-card"></i> Bayar Sekarang
            </button>
        </div>
    </div>
</div>
</div>

{{-- Tab: History --}}
<div id="main-tab-history" class="main-tab-content" style="display:none">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
            <h3 style="margin:0">📜 Riwayat Transaksi Kasir</h3>
            <div class="badge badge-info">Terakhir 20 Transaksi</div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Waktu</th>
                        <th>Customer</th>
                        <th>Item Terjual</th>
                        <th>Total Amount</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posHistory as $sale)
                    <tr>
                        <td style="font-weight:700">#{{ $sale->invoice_no }}</td>
                        <td>{{ $sale->created_at->format('d M Y, H:i') }}</td>
                        <td>{{ $sale->customer->name }}</td>
                        <td>
                            <div style="font-size:12px;color:var(--text-secondary)">
                                {{ $sale->items->count() }} Item
                            </div>
                        </td>
                        <td style="font-weight:700">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge badge-neutral" style="font-size:10px">
                                {{ str_replace('POS Transaction via ', '', $sale->notes) }}
                            </span>
                        </td>
                        <td><span class="badge badge-success">Selesai</span></td>
                        <td>
                            <button class="btn btn-ghost btn-xs" title="Detail"><i data-lucide="eye" style="width:14px"></i></button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:40px;color:var(--text-tertiary)">
                            <i data-lucide="inbox" style="width:48px;height:48px;margin-bottom:12px;opacity:0.5"></i>
                            <p>Belum ada riwayat transaksi via POS</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal: Checkout --}}
<div id="modal-checkout" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal" style="max-width:450px">
        <div class="modal-header">
            <h3>💳 Selesaikan Pembayaran</h3>
            <button class="btn-icon" onclick="document.getElementById('modal-checkout').classList.add('hidden')"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <div style="text-align:center;margin-bottom:24px;padding:20px;background:var(--bg-tertiary);border-radius:var(--radius-lg)">
                <div style="font-size:14px;color:var(--text-tertiary);margin-bottom:4px">Total yang harus dibayar</div>
                <div id="checkout-total-display" style="font-size:32px;font-weight:800;color:var(--primary-500)">Rp 0</div>
            </div>
            
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                    <button class="btn btn-outline btn-payment active" onclick="setPaymentMethod('Tunai', this)">💵 Tunai</button>
                    <button class="btn btn-outline btn-payment" onclick="setPaymentMethod('Transfer', this)">📱 Transfer/QRIS</button>
                </div>
            </div>

            <div class="form-group" id="cash-input-group">
                <label>Diterima (Tunai)</label>
                <input type="number" id="cash-amount" class="form-control" placeholder="0" oninput="calculateChange()" style="font-size:20px;height:50px">
                <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:6px;margin-top:10px">
                    <button class="btn btn-ghost btn-xs" onclick="quickCash(10000)">10rb</button>
                    <button class="btn btn-ghost btn-xs" onclick="quickCash(20000)">20rb</button>
                    <button class="btn btn-ghost btn-xs" onclick="quickCash(50000)">50rb</button>
                    <button class="btn btn-ghost btn-xs" onclick="quickCash(100000)">100rb</button>
                </div>
            </div>

            <div id="change-group" style="padding:16px;background:rgba(34,197,94,0.1);border-radius:var(--radius-md);margin-top:20px;display:flex;justify-content:space-between;align-items:center">
                <span style="color:var(--success-400);font-weight:600">Kembalian</span>
                <span id="cash-change" style="font-size:20px;font-weight:800;color:var(--success-500)">Rp 0</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" style="width:100%;height:50px" onclick="processCheckout()">
                ⚡ Proses Transaksi
            </button>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div id="modal-success" class="modal-overlay hidden">
    <div class="modal" style="max-width:400px;text-align:center;padding:40px">
        <div style="width:80px;height:80px;background:var(--success-500);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;color:white">
            <i data-lucide="check" style="width:48px;height:48px"></i>
        </div>
        <h3 style="margin-bottom:8px">Transaksi Berhasil!</h3>
        <p style="color:var(--text-tertiary);margin-bottom:24px">Invoice <span id="success-invoice" style="font-weight:600;color:var(--text-primary)"></span> telah dicatat.</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <button class="btn btn-primary" onclick="window.location.reload()"><i data-lucide="print"></i> Cetak Struk</button>
            <button class="btn btn-ghost" onclick="window.location.reload()">Selesai</button>
        </div>
    </div>
</div>

{{-- Modal: Add Customer --}}
<div id="modal-customer" class="modal-overlay hidden" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="modal" style="max-width:400px">
        <div class="modal-header">
            <h3>👤 Tambah Customer Baru</h3>
            <button class="btn-icon" onclick="document.getElementById('modal-customer').classList.add('hidden')"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Nama Lengkap <span style="color:var(--danger-400)">*</span></label>
                <input type="text" id="cust-name" class="form-control" placeholder="Nama customer">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="cust-email" class="form-control" placeholder="example@email.com">
            </div>
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" id="cust-phone" class="form-control" placeholder="08xxxxxxxx">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="document.getElementById('modal-customer').classList.add('hidden')">Batal</button>
            <button class="btn btn-primary" onclick="saveCustomer()">Simpan Customer</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let cart = [];
let paymentMethod = 'Tunai';

function switchMainTab(tab, btn) {
    document.querySelectorAll('.main-tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.main-tab-content').previousElementSibling?.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    // Fixed selector for general use
    btn.parentElement.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    document.getElementById('main-tab-' + tab).style.display = 'block';
    btn.classList.add('active');
    if (window.lucide) lucide.createIcons();
}

function filterProducts() {
    const search = document.getElementById('pos-search').value.toLowerCase();
    const cat = document.getElementById('pos-category').value;
    
    document.querySelectorAll('.product-item').forEach(item => {
        const matchSearch = item.dataset.name.includes(search) || item.dataset.sku.includes(search);
        const matchCat = !cat || item.dataset.category === cat;
        item.style.display = (matchSearch && matchCat) ? 'block' : 'none';
    });
}

function addToCart(product) {
    const existing = cart.find(item => item.id === product.id);
    if (existing) {
        if (existing.quantity >= product.stock) {
            alert('Stok tidak mencukupi!');
            return;
        }
        existing.quantity++;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1,
            stock: product.stock
        });
    }
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    renderCart();
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (item) {
        const newQty = item.quantity + delta;
        if (newQty > 0 && newQty <= item.stock) {
            item.quantity = newQty;
            renderCart();
        } else if (newQty === 0) {
            removeFromCart(id);
        } else {
            alert('Stok tidak mencukupi!');
        }
    }
}

function renderCart() {
    const container = document.getElementById('cart-items');
    const emptyMsg = document.getElementById('empty-cart');
    
    if (cart.length === 0) {
        container.innerHTML = emptyMsg.outerHTML;
        updateTotals(0);
        return;
    }

    container.innerHTML = cart.map(item => `
        <div style="display:flex;align-items:center;padding:12px 0;border-bottom:1px solid var(--border-color)">
            <div style="flex:1">
                <div style="font-weight:600;font-size:14px;margin-bottom:4px">${item.name}</div>
                <div style="color:var(--text-tertiary);font-size:12px">Rp ${item.price.toLocaleString('id-ID')} / unit</div>
            </div>
            <div style="display:flex;align-items:center;gap:12px">
                <div style="display:flex;align-items:center;background:var(--bg-tertiary);border-radius:4px">
                    <button class="btn-icon" onclick="updateQty(${item.id}, -1)" style="padding:4px"><i data-lucide="minus" style="width:14px;height:14px"></i></button>
                    <span style="font-weight:700;min-width:20px;text-align:center">${item.quantity}</span>
                    <button class="btn-icon" onclick="updateQty(${item.id}, 1)" style="padding:4px"><i data-lucide="plus" style="width:14px;height:14px"></i></button>
                </div>
                <div style="font-weight:700;min-width:80px;text-align:right">Rp ${(item.price * item.quantity).toLocaleString('id-ID')}</div>
            </div>
        </div>
    `).join('');
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    updateTotals(total);
    if (window.lucide) lucide.createIcons();
}

function updateTotals(total) {
    document.getElementById('cart-subtotal').innerText = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('cart-total').innerText = 'Rp ' + total.toLocaleString('id-ID');
}

function openCheckoutModal() {
    if (cart.length === 0) return alert('Keranjang kosong!');
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    document.getElementById('checkout-total-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('modal-checkout').classList.remove('hidden');
    document.getElementById('cash-amount').focus();
    calculateChange();
}

function setPaymentMethod(method, btn) {
    paymentMethod = method;
    document.querySelectorAll('.btn-payment').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
    
    const cashGroup = document.getElementById('cash-input-group');
    const changeGroup = document.getElementById('change-group');
    
    if (method === 'Transfer') {
        cashGroup.style.opacity = '0.5';
        cashGroup.style.pointerEvents = 'none';
        changeGroup.style.display = 'none';
    } else {
        cashGroup.style.opacity = '1';
        cashGroup.style.pointerEvents = 'auto';
        changeGroup.style.display = 'flex';
    }
}

function quickCash(amount) {
    document.getElementById('cash-amount').value = amount;
    calculateChange();
}

function calculateChange() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const received = parseFloat(document.getElementById('cash-amount').value) || 0;
    const change = Math.max(0, received - total);
    document.getElementById('cash-change').innerText = 'Rp ' + change.toLocaleString('id-ID');
}

function processCheckout() {
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const received = parseFloat(document.getElementById('cash-amount').value) || 0;
    
    if (paymentMethod === 'Tunai' && received < total) {
        return alert('Jumlah bayar kurang!');
    }

    const data = {
        customer_id: document.getElementById('cart-customer').value,
        items: cart.map(item => ({ product_id: item.id, quantity: item.quantity })),
        payment_method: paymentMethod,
        amount_paid: paymentMethod === 'Tunai' ? received : total,
        _token: "{{ csrf_token() }}"
    };

    fetch("{{ route('pos.checkout') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            document.getElementById('modal-checkout').classList.add('hidden');
            document.getElementById('success-invoice').innerText = res.invoice_no;
            document.getElementById('modal-success').classList.remove('hidden');
            if (window.lucide) lucide.createIcons();
        } else {
            alert(res.message);
        }
    })
    .catch(err => alert('Terjadi kesalahan sistem'));
}
function saveCustomer() {
    const name = document.getElementById('cust-name').value;
    const email = document.getElementById('cust-email').value;
    const phone = document.getElementById('cust-phone').value;

    if (!name) return alert('Nama wajib diisi!');

    const data = {
        name: name,
        email: email,
        phone: phone,
        _token: "{{ csrf_token() }}"
    };

    fetch("{{ route('pos.customer.store') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            // Add to dropdown
            const select = document.getElementById('cart-customer');
            const option = new Option(res.customer.name, res.customer.id);
            select.add(option);
            select.value = res.customer.id;
            
            // Close modal
            document.getElementById('modal-customer').classList.add('hidden');
            
            // Clear inputs
            document.getElementById('cust-name').value = '';
            document.getElementById('cust-email').value = '';
            document.getElementById('cust-phone').value = '';
            
            alert('Customer berhasil ditambahkan!');
        } else {
            alert('Gagal menyimpan customer');
        }
    })
    .catch(err => alert('Terjadi kesalahan sistem'));
}
</script>

<style>
.btn-payment.active {
    background: var(--primary-500);
    color: white;
    border-color: var(--primary-500);
}
.product-item:hover {
    border-color: var(--primary-400) !important;
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
</style>
@endpush

# Analisis Struktur Data Ekspor Shopee

Berdasarkan data yang Anda berikan, berikut adalah analisis struktur data Shopee beserta pemetaannya untuk kebutuhan sinkronisasi ke database.

## Format Data
Formatnya identik dengan hasil parsing TikTok sebelumnya (JSON dengan baris sebagai key dan huruf kolom sebagai property).
- **Baris 1 (`"1"`):** Berisi **Header** (Nama Kolom).
- **Baris 2 dan seterusnya:** Berisi **Data Pesanan**.

---

## Pemetaan Kolom Krusial (Shopee)

Berikut adalah kolom-kolom penting yang perlu diperhatikan untuk proses import:

### 1. Informasi Pesanan (Tabel `orders`)
- **`A` (No. Pesanan):** ID unik pesanan Shopee. Digunakan sebagai kunci unik untuk menghindari duplikasi data saat sinkronisasi.
- **`B` (Status Pesanan):** Status (Selesai, Batal, Perlu Dikirim, dll).
- **`L` (Metode Pembayaran):** (Contoh: SPayLater, COD, ShopeePay).
- **`AM` (Total Pembayaran):** Total dana yang dibayar pembeli.
- **`J` (Waktu Pesanan Dibuat):** Waktu transaksi dimulai.
- **`AW` (Waktu Pesanan Selesai):** Waktu dana dilepaskan ke penjual.

### 2. Produk & Inventaris (Tabel `order_items` / Stok)
- **`O` (Nomor Referensi SKU):** **SANGAT PENTING**. Ini adalah **Seller SKU** Anda. Sama seperti TikTok, kolom ini digunakan untuk mencocokkan produk di sistem Shopee dengan produk di database ERP Anda untuk pemotongan stok.
- **`N` (Nama Produk) & `P` (Nama Variasi):** Detail produk.
- **`S` (Jumlah):** Kuantitas produk yang dibeli.
- **`Q` (Harga Awal) & `R` (Harga Setelah Diskon):** Detail harga per unit.

### 3. Logistik & Pengiriman
- **`E` (No. Resi):** Nomor pelacakan kurir.
- **`F` (Opsi Pengiriman):** Kurir yang digunakan (SPX Standard, J&T, dll).
- **`AR` (Nama Penerima) & `AS` (No. Telepon):** Data kontak pelanggan.
- **`AT` (Alamat Pengiriman):** Alamat lengkap tujuan.

### 4. Detail Potongan & Biaya
- **`W` (Diskon Dari Penjual):** Biaya promosi yang ditanggung toko Anda.
- **`AJ` (Ongkir Dibayar Pembeli):** Biaya kirim yang masuk.
- **`AK` (Estimasi Potongan Biaya Pengiriman):** Subsidi ongkir (biasanya dari Shopee).

---

## Perbedaan Utama dengan TikTok
1. **Nama Kolom SKU:** Di TikTok menggunakan "Seller SKU" (Kolom G), sedangkan di Shopee menggunakan **"Nomor Referensi SKU" (Kolom O)**.
2. **Format Waktu:** Pastikan untuk mem-parsing format tanggal Shopee (Contoh: `2026-04-01 07:32`) agar sesuai dengan format `DATETIME` di database (MySQL biasanya `Y-m-d H:i:s`).

## Rekomendasi Sinkronisasi
Sama seperti TikTok, Anda dapat menggunakan logika `updateOrCreate` pada tabel `orders` berdasarkan **Kolom A (No. Pesanan)**, kemudian melakukan *looping* untuk setiap baris produk guna memotong stok berdasarkan **Kolom O (Nomor Referensi SKU)** dan **Kolom S (Jumlah)**.

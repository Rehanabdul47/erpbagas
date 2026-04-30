# Analisis Struktur Data Ekspor TikTok Shop

Berdasarkan pengecekan isi file `Hasil parsing TIKTOK.txt`, berikut adalah analisis struktur data beserta pemetaannya untuk kebutuhan sinkronisasi ke dalam database aplikasi Anda (ERP/Inventory).

## Format Data Mentah
Data tersebut merupakan hasil konversi dari Excel ke format **JSON**. 
- **Key Utama:** Nomor baris (Row number) dari Excel (Contoh: `"1"`, `"2"`, `"3"`, dst).
- **Key Kolom:** Huruf abjad sesuai kolom Excel (Contoh: `"A"`, `"B"`, `"C"`, sampai `"BK"`).
- **Baris 1 (`"1"`):** Berisi **Header** atau nama kolom (contoh: `"A": "Order ID"`).
- **Baris 2 (`"2"`):** Berisi deskripsi atau keterangan dari masing-masing kolom.
- **Baris 3 dan seterusnya:** Merupakan **Data Pesanan** yang sesungguhnya.

> [!IMPORTANT]
> Saat membuat script import nanti, Anda **wajib melewati (skip) index "1" dan "2"** agar tidak masuk ke database. Looping data harus dimulai dari index "3".

---

## Pemetaan Kolom untuk Sinkronisasi Database

Struktur data ini pada dasarnya berbentuk *flat*, yang berarti satu baris mewakili **satu item pesanan (Order Item)**. Jika pelanggan membeli 3 produk berbeda dalam 1 pesanan, akan ada 3 baris dengan **Order ID yang sama**.

Berikut pemetaan (mapping) kolom-kolom krusial untuk dimasukkan ke database:

### 1. Data Transaksi Utama (Tabel `orders` atau `ecommerce_orders`)
Kolom ini menyimpan informasi global dari pesanan. Jika ada duplikasi `Order ID`, data ini hanya perlu disimpan sekali (gunakan fungsi seperti `updateOrCreate`).
- **`A` (Order ID):** ID Unik pesanan dari TikTok. (Primary Key Eksternal).
- **`B` (Order Status):** Status (Selesai, Dibatalkan, Menunggu Pembayaran, dll).
- **`AC` (Order Amount):** Total akhir yang dibayar oleh pelanggan.
- **`BC` (Payment Method):** Metode pembayaran (COD, Transfer, dll).
- **`AD` (Created Time):** Waktu pesanan dibuat.
- **`AE` (Paid Time):** Waktu pembayaran.
- **`AG` (Shipped Time):** Waktu dikirim.
- **`AH` (Delivered Time):** Waktu diterima.

### 2. Data Item Pesanan (Tabel `order_items` atau pemotongan Stok)
Bagian ini paling penting untuk manajemen **Inventory/Gudang**.
- **`F` (SKU ID):** ID SKU internal dari platform TikTok.
- **`G` (Seller SKU):** **KUNCI PENGHUBUNG (FOREIGN KEY)**. Ini adalah kode SKU yang Anda buat di sistem Anda. Anda akan menggunakan nilai ini untuk mencari `product_id` di database Laravel untuk memotong stok.
- **`H` (Product Name) & `I` (Variation):** Nama produk dan variasi/ukuran.
- **`J` (Quantity):** Jumlah barang yang dibeli (Qty untuk pemotongan stok).
- **`L` (SKU Unit Original Price):** Harga normal per pcs.
- **`O` (SKU Seller Discount):** Diskon tanggungan penjual (Penting untuk laporan laba/rugi).
- **`P` (SKU Subtotal After Discount):** Harga setelah diskon.

### 3. Data Pelanggan & Pengiriman (Tabel `customers` & `shipping`)
- **`AR` (Buyer Username):** Username akun pembeli.
- **`AS` (Recipient):** Nama penerima paket.
- **`AT` (Phone #):** Nomor telepon (seringkali disensor sebagian oleh TikTok).
- **`AN` (Tracking ID):** Nomor Resi Pengiriman (contoh: JX9258287901).
- **`AP` (Shipping Provider Name):** Jasa ekspedisi (J&T, SiCepat, dll).
- **`AW` - `BA` (Alamat):** Kombinasi Provinsi, Kota, Kecamatan, Desa, dan Alamat Detail.

### 4. Data Finansial Tambahan (Tabel `finance` atau `cash_book`)
Jika Anda mengintegrasikan dengan modul finance (jurnal otomatis).
- **`Q`, `R`, `S`, `T` (Ongkir):** Data seputar ongkos kirim (Asli, Diskon Seller, Diskon Platform).
- **`Y` & `Z` (Fee):** Biaya layanan dan penanganan, sangat penting untuk dicatat karena akan mengurangi total dana yang cair/settlement ke rekening Anda.

---

## Saran Implementasi Logika Import di Controller

Nantinya, Anda bisa membuat logika import seperti ini:

1. **Looping Data:** Loop isi data `json_decode()` mulai dari index ke-3.
2. **Cari Produk:** Cari berdasarkan `Seller SKU` (`$row['G']`) di tabel `products` internal. Jika tidak ada, catat di log *error* atau *skip*.
3. **Simpan Pesanan:** 
   Gunakan `EcommerceOrder::updateOrCreate(['order_id' => $row['A']], [...data_utama...])`
4. **Simpan Item Pesanan:**
   Simpan item detail dan hubungkan dengan tabel `products`.
5. **Potong Stok Otomatis:**
   Jika statusnya Valid/Dibayar/Dikirim, lakukan pengurangan stok di inventaris berdasarkan `$row['J']` (Quantity).
6. **Catatan Keuangan:**
   (Opsional) Sinkronisasi ke modul *Finance* atau *General Ledger* jika pesanan berstatus Selesai (`$row['B'] == 'Selesai'`).

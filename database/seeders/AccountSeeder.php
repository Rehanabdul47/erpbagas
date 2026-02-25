<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            // Assets (Aset)
            ['code' => '1001', 'name' => 'Kas', 'type' => 'asset', 'description' => 'Uang tunai'],
            ['code' => '1002', 'name' => 'Bank', 'type' => 'asset', 'description' => 'Rekening Bank'],
            ['code' => '1101', 'name' => 'Piutang Usaha', 'type' => 'asset', 'description' => 'Piutang pelanggan'],
            ['code' => '1201', 'name' => 'Persediaan Barang', 'type' => 'asset', 'description' => 'Stok produk'],
            ['code' => '1301', 'name' => 'Aset Tetap', 'type' => 'asset', 'description' => 'Peralatan, kendaraan, dll'],
            
            // Liabilities (Kewajiban)
            ['code' => '2001', 'name' => 'Hutang Usaha', 'type' => 'liability', 'description' => 'Hutang ke supplier'],
            ['code' => '2101', 'name' => 'Hutang Gaji', 'type' => 'liability', 'description' => 'Gaji karyawan yang belum dibayar'],
            ['code' => '2201', 'name' => 'Hutang Bank', 'type' => 'liability', 'description' => 'Pinjaman Bank'],
            
            // Equity (Ekuitas/Modal)
            ['code' => '3001', 'name' => 'Modal Pemilik', 'type' => 'equity', 'description' => 'Suntikan modal awal / tambahan'],
            ['code' => '3002', 'name' => 'Prive', 'type' => 'equity', 'description' => 'Pengambilan dana pribadi oleh pemilik'],
            ['code' => '3003', 'name' => 'Laba Ditahan', 'type' => 'equity', 'description' => 'Akumulasi sisa laba/rugi'],
            
            // Revenues (Pendapatan)
            ['code' => '4001', 'name' => 'Pendapatan Penjualan', 'type' => 'revenue', 'description' => 'Penjualan produk'],
            ['code' => '4002', 'name' => 'Pendapatan Jasa', 'type' => 'revenue', 'description' => 'Pendapatan jasa / servis'],
            ['code' => '4999', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue', 'description' => 'Pendapatan di luar usaha utama'],
            
            // Expenses (Biaya/Beban)
            ['code' => '5001', 'name' => 'Harga Pokok Penjualan (HPP)', 'type' => 'expense', 'description' => 'HPP Produk'],
            ['code' => '6001', 'name' => 'Biaya Gaji', 'type' => 'expense', 'description' => 'Gaji Karyawan'],
            ['code' => '6002', 'name' => 'Biaya Operasional', 'type' => 'expense', 'description' => 'Listrik, air, internet, ATK'],
            ['code' => '6003', 'name' => 'Biaya Pemasaran', 'type' => 'expense', 'description' => 'Iklan, promosi'],
            ['code' => '6004', 'name' => 'Biaya Sewa', 'type' => 'expense', 'description' => 'Sewa tempat / peralatan'],
            ['code' => '6005', 'name' => 'Biaya Penyusutan', 'type' => 'expense', 'description' => 'Penyusutan aset tetap'],
            ['code' => '6999', 'name' => 'Biaya Lain-lain', 'type' => 'expense', 'description' => 'Biaya lain-lain di luar operasional utama'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['code' => $account['code']], $account);
        }
    }
}

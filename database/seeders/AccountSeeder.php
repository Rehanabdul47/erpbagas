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
            // 1. Aset / Harta (1-xxx)
            // 1-100 Aset Lancar
            ['code' => '1-101', 'name' => 'Kas', 'type' => 'asset', 'description' => 'Cash'],
            ['code' => '1-102', 'name' => 'Bank', 'type' => 'asset', 'description' => 'Bank'],
            ['code' => '1-103', 'name' => 'Piutang Usaha', 'type' => 'asset', 'description' => 'Account Receivable'],
            ['code' => '1-104', 'name' => 'Persediaan Barang Dagang', 'type' => 'asset', 'description' => 'Merchandise Inventory'],
            ['code' => '1-105', 'name' => 'Perlengkapan', 'type' => 'asset', 'description' => 'Supplies'],
            ['code' => '1-106', 'name' => 'Sewa Dibayar Dimuka', 'type' => 'asset', 'description' => 'Prepaid Rent'],
            
            // 1-200 Aset Tetap
            ['code' => '1-201', 'name' => 'Tanah', 'type' => 'asset', 'description' => 'Land'],
            ['code' => '1-202', 'name' => 'Bangunan', 'type' => 'asset', 'description' => 'Building'],
            ['code' => '1-203', 'name' => 'Akumulasi Penyusutan Bangunan', 'type' => 'asset', 'description' => 'Accumulated Depreciation Building'],
            ['code' => '1-204', 'name' => 'Peralatan', 'type' => 'asset', 'description' => 'Equipment'],
            
            // 2. Liabilitas / Utang (2-xxx) 
            // 2-100 Utang Lancar
            ['code' => '2-101', 'name' => 'Utang Usaha/Dagang', 'type' => 'liability', 'description' => 'Account Payable'],
            ['code' => '2-102', 'name' => 'Utang Gaji', 'type' => 'liability', 'description' => 'Salaries Payable'],
            ['code' => '2-103', 'name' => 'Utang Pajak', 'type' => 'liability', 'description' => 'Tax Payable'],
            ['code' => '2-104', 'name' => 'Pendapatan Diterima Dimuka', 'type' => 'liability', 'description' => 'Unearned Revenue'],
            // 2-200 Utang Jangka Panjang
            ['code' => '2-201', 'name' => 'Utang Bank', 'type' => 'liability', 'description' => 'Bank Loan'],
            ['code' => '2-202', 'name' => 'Utang Obligasi', 'type' => 'liability', 'description' => 'Bond Payable'],

            // 3. Ekuitas / Modal (3-xxx) 
            ['code' => '3-101', 'name' => 'Modal Pemilik', 'type' => 'equity', 'description' => 'Owner\'s Capital'],
            ['code' => '3-102', 'name' => 'Prive', 'type' => 'equity', 'description' => 'Drawing'],
            ['code' => '3-103', 'name' => 'Laba Ditahan', 'type' => 'equity', 'description' => 'Retained Earnings'],
            
            // 4. Pendapatan (4-xxx)
            ['code' => '4-101', 'name' => 'Pendapatan Usaha/Penjualan', 'type' => 'revenue', 'description' => 'Sales Revenue'],
            ['code' => '4-102', 'name' => 'Pendapatan Jasa', 'type' => 'revenue', 'description' => 'Service Revenue'],
            ['code' => '4-103', 'name' => 'Retur Penjualan', 'type' => 'revenue', 'description' => 'Sales Return'],
            ['code' => '4-104', 'name' => 'Potongan Penjualan', 'type' => 'revenue', 'description' => 'Sales Discount'],
            
            // 5. Beban / Biaya (5-xxx) 
            ['code' => '5-101', 'name' => 'Harga Pokok Penjualan (HPP)', 'type' => 'expense', 'description' => 'COGS'],
            ['code' => '5-102', 'name' => 'Beban Gaji', 'type' => 'expense', 'description' => 'Salaries Expense'],
            ['code' => '5-103', 'name' => 'Beban Sewa', 'type' => 'expense', 'description' => 'Rent Expense'],
            ['code' => '5-104', 'name' => 'Beban Perlengkapan/Peralatan', 'type' => 'expense', 'description' => 'Supplies/Equipment Expense'],
            ['code' => '5-105', 'name' => 'Beban Penyusutan', 'type' => 'expense', 'description' => 'Depreciation Expense'],
            ['code' => '5-106', 'name' => 'Beban Listrik, Air & Telepon', 'type' => 'expense', 'description' => 'Electricity, Water & Phone Expense'],
            ['code' => '5-107', 'name' => 'Beban Iklan', 'type' => 'expense', 'description' => 'Advertising Expense'],
            ['code' => '5-108', 'name' => 'Beban Lain-Lain', 'type' => 'expense', 'description' => 'Other Expense'],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(['code' => $account['code']], $account);
        }
    }
}

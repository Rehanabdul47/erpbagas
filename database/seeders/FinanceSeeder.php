<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ═══ ASET (1-xxx) ═══
            ['code' => '1-000', 'name' => 'ASET', 'type' => 'asset', 'level' => 1, 'is_header' => true, 'normal_balance' => 'debit', 'parent_code' => null],

            // Aset Lancar
            ['code' => '1-100', 'name' => 'Aset Lancar', 'type' => 'asset', 'level' => 2, 'is_header' => true, 'normal_balance' => 'debit', 'parent_code' => '1-000'],
            ['code' => '1-101', 'name' => 'Kas & Setara Kas', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-100'],
            ['code' => '1-102', 'name' => 'Bank', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-100'],
            ['code' => '1-103', 'name' => 'Piutang Usaha', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'is_control' => true, 'normal_balance' => 'debit', 'parent_code' => '1-100'],
            ['code' => '1-104', 'name' => 'Persediaan Barang Dagang', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'is_control' => true, 'normal_balance' => 'debit', 'parent_code' => '1-100'],
            ['code' => '1-105', 'name' => 'Perlengkapan', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-100'],
            ['code' => '1-106', 'name' => 'Piutang Lain-Lain', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-100'],
            ['code' => '1-107', 'name' => 'Uang Muka', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-100'],
            ['code' => '1-108', 'name' => 'Biaya Dibayar Dimuka', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-100'],
            ['code' => '1-109', 'name' => 'PPN Masukan', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-100'],

            // Aset Tetap
            ['code' => '1-200', 'name' => 'Aset Tetap', 'type' => 'asset', 'level' => 2, 'is_header' => true, 'normal_balance' => 'debit', 'parent_code' => '1-000'],
            ['code' => '1-201', 'name' => 'Tanah', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-200'],
            ['code' => '1-202', 'name' => 'Bangunan', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-200'],
            ['code' => '1-203', 'name' => 'Akumulasi Penyusutan Bangunan', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '1-200'],
            ['code' => '1-204', 'name' => 'Peralatan', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-200'],
            ['code' => '1-205', 'name' => 'Akumulasi Penyusutan Peralatan', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '1-200'],
            ['code' => '1-206', 'name' => 'Kendaraan', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '1-200'],
            ['code' => '1-207', 'name' => 'Akumulasi Penyusutan Kendaraan', 'type' => 'asset', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '1-200'],

            // ═══ LIABILITAS (2-xxx) ═══
            ['code' => '2-000', 'name' => 'LIABILITAS', 'type' => 'liability', 'level' => 1, 'is_header' => true, 'normal_balance' => 'credit', 'parent_code' => null],

            ['code' => '2-100', 'name' => 'Liabilitas Jangka Pendek', 'type' => 'liability', 'level' => 2, 'is_header' => true, 'normal_balance' => 'credit', 'parent_code' => '2-000'],
            ['code' => '2-101', 'name' => 'Utang Usaha / Dagang', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'is_control' => true, 'normal_balance' => 'credit', 'parent_code' => '2-100'],
            ['code' => '2-102', 'name' => 'Utang Gaji', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '2-100'],
            ['code' => '2-103', 'name' => 'Utang Pajak', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '2-100'],
            ['code' => '2-104', 'name' => 'Pendapatan Diterima Dimuka', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '2-100'],
            ['code' => '2-105', 'name' => 'PPN Keluaran', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '2-100'],
            ['code' => '2-106', 'name' => 'Utang Lain-Lain', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '2-100'],

            ['code' => '2-200', 'name' => 'Liabilitas Jangka Panjang', 'type' => 'liability', 'level' => 2, 'is_header' => true, 'normal_balance' => 'credit', 'parent_code' => '2-000'],
            ['code' => '2-201', 'name' => 'Utang Bank', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '2-200'],
            ['code' => '2-202', 'name' => 'Utang Leasing', 'type' => 'liability', 'level' => 3, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '2-200'],

            // ═══ EKUITAS (3-xxx) ═══
            ['code' => '3-000', 'name' => 'EKUITAS', 'type' => 'equity', 'level' => 1, 'is_header' => true, 'normal_balance' => 'credit', 'parent_code' => null],
            ['code' => '3-101', 'name' => 'Modal Pemilik / Modal Disetor', 'type' => 'equity', 'level' => 2, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '3-000'],
            ['code' => '3-102', 'name' => 'Prive / Dividen', 'type' => 'equity', 'level' => 2, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '3-000'],
            ['code' => '3-103', 'name' => 'Laba Ditahan', 'type' => 'equity', 'level' => 2, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '3-000'],
            ['code' => '3-104', 'name' => 'Laba Tahun Berjalan', 'type' => 'equity', 'level' => 2, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '3-000'],

            // ═══ PENDAPATAN (4-xxx) ═══
            ['code' => '4-000', 'name' => 'PENDAPATAN', 'type' => 'revenue', 'level' => 1, 'is_header' => true, 'normal_balance' => 'credit', 'parent_code' => null],
            ['code' => '4-101', 'name' => 'Pendapatan Usaha / Penjualan', 'type' => 'revenue', 'level' => 2, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '4-000'],
            ['code' => '4-102', 'name' => 'Pendapatan Jasa', 'type' => 'revenue', 'level' => 2, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '4-000'],
            ['code' => '4-103', 'name' => 'Potongan Penjualan', 'type' => 'revenue', 'level' => 2, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '4-000'],
            ['code' => '4-104', 'name' => 'Retur Penjualan', 'type' => 'revenue', 'level' => 2, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '4-000'],
            ['code' => '4-105', 'name' => 'Pendapatan Lain-Lain', 'type' => 'revenue', 'level' => 2, 'is_header' => false, 'normal_balance' => 'credit', 'parent_code' => '4-000'],

            // ═══ BEBAN (5-xxx) ═══
            ['code' => '5-000', 'name' => 'BEBAN', 'type' => 'expense', 'level' => 1, 'is_header' => true, 'normal_balance' => 'debit', 'parent_code' => null],

            ['code' => '5-100', 'name' => 'Harga Pokok Penjualan', 'type' => 'expense', 'level' => 2, 'is_header' => true, 'normal_balance' => 'debit', 'parent_code' => '5-000'],
            ['code' => '5-101', 'name' => 'HPP - Bahan Baku', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-100'],
            ['code' => '5-102', 'name' => 'HPP - Tenaga Kerja Langsung', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-100'],
            ['code' => '5-103', 'name' => 'HPP - Overhead Pabrik', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-100'],

            ['code' => '5-200', 'name' => 'Beban Operasional', 'type' => 'expense', 'level' => 2, 'is_header' => true, 'normal_balance' => 'debit', 'parent_code' => '5-000'],
            ['code' => '5-201', 'name' => 'Beban Gaji & Tunjangan', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-202', 'name' => 'Beban Sewa', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-203', 'name' => 'Beban Perlengkapan', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-204', 'name' => 'Beban Penyusutan', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-205', 'name' => 'Beban Listrik, Air & Telepon', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-206', 'name' => 'Beban Iklan & Promosi', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-207', 'name' => 'Beban Transportasi', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-208', 'name' => 'Beban Administrasi & Umum', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-209', 'name' => 'Beban Pajak', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-210', 'name' => 'Beban Asuransi', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
            ['code' => '5-211', 'name' => 'Beban Lain-Lain', 'type' => 'expense', 'level' => 3, 'is_header' => false, 'normal_balance' => 'debit', 'parent_code' => '5-200'],
        ];

        // Build the accounts preserving parent relationships
        $codeToId = [];

        foreach ($accounts as $data) {
            $parentCode = $data['parent_code'] ?? null;
            unset($data['parent_code']);

            $data['parent_id'] = $parentCode ? ($codeToId[$parentCode] ?? null) : null;
            $data['is_control'] = $data['is_control'] ?? false;

            $account = Account::updateOrCreate(
                ['code' => $data['code']],
                $data
            );

            $codeToId[$data['code']] = $account->id;
        }
    }
}

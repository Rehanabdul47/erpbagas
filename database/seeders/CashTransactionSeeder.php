<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CashTransaction;
use App\Models\User;
use Carbon\Carbon;

class CashTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        $now = Carbon::now();
        
        $data = [
            // Kas Masuk
            [
                'type' => 'in',
                'date' => $now->copy()->subDays(5),
                'category' => 'Penjualan Tunai',
                'amount' => 1250000,
                'description' => 'Penjualan harian retail toko',
                'reference' => 'CSH-001',
                'user_id' => $user->id,
            ],
            [
                'type' => 'in',
                'date' => $now->copy()->subDays(2),
                'category' => 'Penerimaan Piutang',
                'amount' => 5000000,
                'description' => 'Pelunasan invoice #TK-998 oleh Toko Berkah',
                'reference' => 'INV-998',
                'user_id' => $user->id,
            ],
            
            // Kas Keluar
            [
                'type' => 'out',
                'date' => $now->copy()->subDays(10),
                'category' => 'Biaya Server',
                'amount' => 120000,
                'description' => 'Pembayaran bulanan hosting/VPS bulan Februari',
                'reference' => 'SRV-FEB',
                'user_id' => $user->id,
            ],
            [
                'type' => 'out',
                'date' => $now->copy()->subDays(7),
                'category' => 'Kasbon Karyawan',
                'amount' => 250000,
                'description' => 'Kasbon darurat staf gudang (Budi)',
                'reference' => 'KB-001',
                'user_id' => $user->id,
            ],
            [
                'type' => 'out',
                'date' => $now->copy()->subDays(4),
                'category' => 'Operasional',
                'amount' => 85000,
                'description' => 'Pembelian galon air dan keperluan dapur kantor',
                'reference' => null,
                'user_id' => $user->id,
            ],
            [
                'type' => 'out',
                'date' => $now->copy()->subDay(),
                'category' => 'Operasional',
                'amount' => 150000,
                'description' => 'Isi bensin kendaraan operasional (Motor Kurir)',
                'reference' => 'STR-991',
                'user_id' => $user->id,
            ],
        ];

        foreach ($data as $trx) {
            CashTransaction::create($trx);
        }
    }
}

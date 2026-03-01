<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::create([
            'name' => 'Toko Utama',
            'type' => 'main',
            'address' => 'Jl. Pusat Bisnis No. 1, Jakarta',
            'phone' => '021-1234567',
        ]);

        Branch::create([
            'name' => 'Cabang Bandung',
            'type' => 'branch',
            'address' => 'Jl. Braga No. 10, Bandung',
            'phone' => '022-7654321',
        ]);

        Branch::create([
            'name' => 'Cabang Surabaya',
            'type' => 'branch',
            'address' => 'Jl. Tunjungan No. 20, Surabaya',
            'phone' => '031-9876543',
        ]);

        Branch::create([
            'name' => 'Cabang Bali',
            'type' => 'branch',
            'address' => 'Jl. Sunset Road, Bali',
            'phone' => '0361-345678',
        ]);
        
        // Asign first branch to all users for now
        $mainBranch = Branch::where('type', 'main')->first();
        if ($mainBranch) {
            \App\Models\User::query()->update(['branch_id' => $mainBranch->id]);
        }
    }
}

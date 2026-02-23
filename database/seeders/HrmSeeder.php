<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class HrmSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'nip' => 'HRM-001',
                'name' => 'Ahmad Fauzi',
                'email' => 'fauzi@gupala.com',
                'phone' => '08123456789',
                'position' => 'Warehouse Manager',
                'department' => 'Logistik',
                'join_date' => '2023-01-15',
                'basic_salary' => 8500000,
                'status' => 'active',
            ],
            [
                'nip' => 'HRM-002',
                'name' => 'Siska Amelia',
                'email' => 'siska@gupala.com',
                'phone' => '08234567890',
                'position' => 'Senior Accountant',
                'department' => 'Finance',
                'join_date' => '2023-03-10',
                'basic_salary' => 7000000,
                'status' => 'active',
            ],
            [
                'nip' => 'HRM-003',
                'name' => 'Randi Kurnia',
                'email' => 'randi@gupala.com',
                'phone' => '08345678901',
                'position' => 'IT Support',
                'department' => 'IT',
                'join_date' => '2023-06-05',
                'basic_salary' => 5500000,
                'status' => 'active',
            ],
        ];

        foreach ($employees as $data) {
            $emp = Employee::create($data);
            
            // Generate some random attendance for the last 5 days
            for ($i = 0; $i < 5; $i++) {
                Attendance::create([
                    'employee_id' => $emp->id,
                    'date' => Carbon::today()->subDays($i),
                    'status' => 'present',
                    'clock_in' => '08:00:00',
                    'clock_out' => '17:00:00',
                    'note' => 'Normal workday',
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Permissions ---
        $permissions = [
            // Dashboard
            'view dashboard',
            // Inventory
            'view inventory', 'manage inventory', 'stock in', 'stock out', 'stock opname',
            // Production
            'manage production',
            // Orders
            'view orders', 'manage orders', 'advance orders',
            // Sales & CRM
            'view sales', 'manage leads', 'manage pipeline',
            // Finance
            'view finance', 'manage invoices', 'manage expenses', 'view reports',
            // Procurement
            'view procurement', 'manage po', 'manage vendors',
            // HRM
            'view hrm', 'manage employees', 'manage attendance', 'manage payroll',
            // Settings
            'view settings', 'manage settings',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // --- Roles ---
        $executive = Role::firstOrCreate(['name' => 'Executive']);
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $staffGudang = Role::firstOrCreate(['name' => 'Staff Gudang']);
        $staffFinance = Role::firstOrCreate(['name' => 'Staff Finance']);
        $staffSales = Role::firstOrCreate(['name' => 'Staff Sales']);
        $staffHR = Role::firstOrCreate(['name' => 'Staff HR']);
        $staffToko = Role::firstOrCreate(['name' => 'Staff Toko']);

        // Executive = all permissions
        $executive->syncPermissions(Permission::all());

        // Manager = all permissions
        $manager->syncPermissions(Permission::all());

        // Staff Gudang
        $staffGudang->syncPermissions([
            'view dashboard', 'view inventory', 'manage inventory',
            'stock in', 'stock out', 'stock opname',
            'manage production',
            'view orders', 'manage orders', 'advance orders',
            'view procurement', 'manage po', 'manage vendors',
        ]);

        // Staff Finance
        $staffFinance->syncPermissions([
            'view dashboard', 'view finance', 'manage invoices',
            'manage expenses', 'view reports',
        ]);

        // Staff Sales
        $staffSales->syncPermissions([
            'view dashboard', 'view orders', 'manage orders', 'advance orders',
            'view sales', 'manage leads', 'manage pipeline',
        ]);

        // Staff HR
        $staffHR->syncPermissions([
            'view dashboard', 'view hrm', 'manage employees',
            'manage attendance', 'manage payroll',
        ]);

        // Staff Toko
        $staffToko->syncPermissions([
            'view dashboard', 'view inventory',
            'view orders', 'manage orders', 'view sales',
        ]);

        // --- Users (matching GUPALA ERP Store.users) ---
        $users = [
            ['name' => 'Admin Utama', 'email' => 'admin@gupala.com', 'department' => 'all', 'role' => 'Executive'],
            ['name' => 'Budi Santoso', 'email' => 'budi@gupala.com', 'department' => 'all', 'role' => 'Manager'],
            ['name' => 'Agus Pramono', 'email' => 'agus@gupala.com', 'department' => 'warehouse', 'role' => 'Staff Gudang'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@gupala.com', 'department' => 'finance', 'role' => 'Staff Finance'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@gupala.com', 'department' => 'sales', 'role' => 'Staff Sales'],
            ['name' => 'Rina Wati', 'email' => 'rina@gupala.com', 'department' => 'hr', 'role' => 'Staff HR'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko@gupala.com', 'department' => 'store', 'role' => 'Staff Toko'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => bcrypt('password'),
                    'department' => $u['department'],
                ]
            );
            $user->syncRoles([$u['role']]);
        }
    }
}

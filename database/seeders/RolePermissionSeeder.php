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
            Permission::create(['name' => $perm]);
        }

        // --- Roles ---
        $executive = Role::create(['name' => 'Executive']);
        $manager = Role::create(['name' => 'Manager']);
        $staffGudang = Role::create(['name' => 'Staff Gudang']);
        $staffFinance = Role::create(['name' => 'Staff Finance']);
        $staffSales = Role::create(['name' => 'Staff Sales']);
        $staffHR = Role::create(['name' => 'Staff HR']);

        // Executive = all permissions
        $executive->givePermissionTo(Permission::all());

        // Manager = all permissions
        $manager->givePermissionTo(Permission::all());

        // Staff Gudang
        $staffGudang->givePermissionTo([
            'view dashboard', 'view inventory', 'manage inventory',
            'stock in', 'stock out', 'stock opname',
            'view orders', 'manage orders', 'advance orders',
            'view procurement', 'manage po', 'manage vendors',
        ]);

        // Staff Finance
        $staffFinance->givePermissionTo([
            'view dashboard', 'view finance', 'manage invoices',
            'manage expenses', 'view reports',
        ]);

        // Staff Sales
        $staffSales->givePermissionTo([
            'view dashboard', 'view orders', 'manage orders', 'advance orders',
            'view sales', 'manage leads', 'manage pipeline',
        ]);

        // Staff HR
        $staffHR->givePermissionTo([
            'view dashboard', 'view hrm', 'manage employees',
            'manage attendance', 'manage payroll',
        ]);

        // --- Users (matching GUPALA ERP Store.users) ---
        $users = [
            ['name' => 'Admin Utama', 'email' => 'admin@gupala.com', 'department' => 'all', 'role' => 'Executive'],
            ['name' => 'Budi Santoso', 'email' => 'budi@gupala.com', 'department' => 'all', 'role' => 'Manager'],
            ['name' => 'Agus Pramono', 'email' => 'agus@gupala.com', 'department' => 'warehouse', 'role' => 'Staff Gudang'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@gupala.com', 'department' => 'finance', 'role' => 'Staff Finance'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@gupala.com', 'department' => 'sales', 'role' => 'Staff Sales'],
            ['name' => 'Rina Wati', 'email' => 'rina@gupala.com', 'department' => 'hr', 'role' => 'Staff HR'],
        ];

        foreach ($users as $u) {
            $user = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => bcrypt('password'),
                'department' => $u['department'],
            ]);
            $user->assignRole($u['role']);
        }
    }
}

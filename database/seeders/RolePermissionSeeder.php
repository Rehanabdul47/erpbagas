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
        $staff = Role::firstOrCreate(['name' => 'Staff']);

        // Executive = all permissions
        $executive->syncPermissions(Permission::all());

        // Manager = all permissions
        $manager->syncPermissions(Permission::all());

        // Staff = limited permissions (Common Staff)
        $staff->syncPermissions([
            'view dashboard',
            'view inventory',
            'view orders',
            'manage orders',
            'view sales',
            'view hrm',
        ]);

        // --- Users ---
        $users = [
            ['name' => 'Admin Utama', 'email' => 'admin@gupala.com', 'department' => 'all', 'role' => 'Executive'],
            ['name' => 'Budi Santoso', 'email' => 'budi@gupala.com', 'department' => 'all', 'role' => 'Manager'],
            ['name' => 'Staff User', 'email' => 'staff@gupala.com', 'department' => 'all', 'role' => 'Staff'],
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

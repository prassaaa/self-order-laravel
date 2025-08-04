<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Menu management
            'manage-menus',
            'create-menu',
            'edit-menu',
            'delete-menu',
            'view-menu',

            // Category management
            'manage-categories',
            'create-category',
            'edit-category',
            'delete-category',
            'view-category',

            // Order management
            'manage-orders',
            'create-order',
            'edit-order',
            'view-order',
            'cancel-order',
            'update-order-status',

            // Payment processing
            'process-payments',
            'view-payments',
            'refund-payment',

            // Reports and analytics
            'view-reports',
            'view-analytics',
            'export-reports',

            // User management
            'manage-users',
            'create-user',
            'edit-user',
            'delete-user',
            'view-user',

            // System settings
            'manage-settings',
            'view-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Admin role
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        // Create Kasir role
        $kasirRole = Role::firstOrCreate(['name' => 'kasir']);
        $kasirPermissions = [
            'view-menu',
            'view-category',
            'manage-orders',
            'create-order',
            'edit-order',
            'view-order',
            'update-order-status',
            'process-payments',
            'view-payments',
        ];
        $kasirRole->syncPermissions($kasirPermissions);

        $this->command->info('Roles and permissions created successfully!');
    }
}

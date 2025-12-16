<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            'User',
            'Role',
            'Account',
            'Company',
            'Bank',
            'Client',
            'TransactionCategory',
            'Transaction',
            'InvoiceTemplate',
            'Invoice',
            'FileManager',
        ];

        $actions = ['list', 'view', 'create', 'edit', 'delete'];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $name = strtolower($action.' '.$model);
                Permission::firstOrCreate(['name' => $name]);
            }
        }

        // Create transaction type-specific permissions
        $transactionTypePermissions = [
            'create expense',
            'create income',
            'create transfer',
        ];

        foreach ($transactionTypePermissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Sync all permissions to super-admin role
        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $role->syncPermissions(Permission::all());

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}

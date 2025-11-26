<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

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
            'Transaction'
        ];

        $actions = ['list', 'view', 'create', 'edit', 'delete'];

        foreach ($models as $model) {
            foreach ($actions as $action) {
                $name = strtolower($action . ' ' . $model);
                Permission::firstOrCreate(['name' => $name]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user =   User::updateOrCreate(
            ['email' => 'super@gojocash.com'],
            [
                'name' => 'Super Admin',
                'email' => 'super@gojocash.com',
                'password' => bcrypt('!3X5)!_1a'),
            ]
        );
        $this->call([
            PermissionSeeder::class
        ]);
        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $user->assignRole('super-admin');
        $role->givePermissionTo(Permission::all());
    }
}

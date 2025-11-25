<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GenerateModelPermissions extends Command
{
    protected $signature = 'permissions:generate';
    protected $description = 'Generate CRUD permissions for all models';

    protected $actions = ['list', 'create', 'update', 'edit', 'delete'];

    public function handle()
    {
        $modelPath = app_path('Models');
        $files = File::files($modelPath);
        $this->warn("Generating...");
        foreach ($files as $file) {
            $modelName = pathinfo($file, PATHINFO_FILENAME);
            $model = strtolower($modelName);
            foreach ($this->actions as $action) {
                $permissionName = strtolower($action . ' ' . $model);
                Permission::firstOrCreate(['name' => $permissionName]);
            }
        }
        $role = Role::firstOrCreate(['name' => 'super-admin']);
        $role->givePermissionTo(Permission::all());

        $this->info("Generated Successfully!");
    }
}

<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    public function getRoleIndexData(?string $search, int $perPage = 15): array
    {
        $roles = $this->paginateRoles($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildRoleRows($roles),
            'roles' => $roles,
            'search' => $search ?? '',
            'model' => 'Role'
        ];
    }
    public function paginateRoles(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return Role::query()
            ->when(! empty($search), static function ($query) use ($search) {
                $query->where(static function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhereHas('company', static function ($clientQuery) use ($search) {
                            $clientQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
    public function getIndexHeaders(): array
    {
        return [
            '#',
            __('Name'),
            __('Created At'),
        ];
    }
    public function buildRoleRows(LengthAwarePaginator $roles): Collection
    {
        return collect($roles->items())->map(function (Role $role, int $index) use ($roles) {
            $position = ($roles->firstItem() ?? 1) + $index;

            return [
                'id' => $role->id,
                'name' => $role->name,
                'cells' => [
                    $position,
                    $role->name,
                    $role->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('roles.show', $role),
                    ],
                    'edit' => [
                        'url' => route('roles.edit', $role),
                    ],
                    'delete' => [
                        'url' => route('roles.destroy', $role),
                        'confirm' => __('Are you sure you want to delete :role?', ['role' => $role->name]),
                    ],
                ],
            ];
        });
    }
    public function prepareCreateFormData()
    {
        return Permission::all();
    }
    public function createRole(string $name, array $permissions): Role
    {
        $role = Role::create(['name' => Str::slug($name)]);
        $role->syncPermissions($permissions);

        return $role;
    }
    public function prepareEditFormData(Role $role): array
    {
        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ];
    }
    public function updateRole(Role $role, string $name, array $permissions): Role
    {
        $role->name = Str::slug($name);
        $role->save();
        $role->syncPermissions($permissions);

        return $role;
    }
    public function deleteRole(Role $role): void
    {
        $role->delete();
    }
}

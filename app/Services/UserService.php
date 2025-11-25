<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserService
{
    public function getUserIndexData(?string $search, int $perPage = 15): array
    {
        $users = $this->paginateUsers($search, $perPage);

        return [
            'headers' => $this->getIndexHeaders(),
            'rows' => $this->buildUserRows($users),
            'users' => $users,
            'search' => $search ?? '',
            'model' => 'User'
        ];
    }
    public function paginateUsers(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        return User::query()
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
            __('Email'),
            _('Role'),
            __('Created At'),
        ];
    }
    public function buildUserRows(LengthAwarePaginator $users): Collection
    {
        return collect($users->items())->map(function (User $user, int $index) use ($users) {
            $position = ($users->firstItem() ?? 1) + $index;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'cells' => [
                    $position,
                    $user->name,
                    $user->email ?? __('—'),
                    Str::title($user->roles->first()?->name ?? __('—')),
                    $user->created_at?->translatedFormat('M j, Y'),
                ],
                'actions' => [
                    'view' => [
                        'url' => route('users.show', $user),
                    ],
                    'edit' => [
                        'url' => route('users.edit', $user),
                    ],
                    'delete' => [
                        'url' => route('users.destroy', $user),
                        'confirm' => __('Are you sure you want to delete :user?', ['user' => $user->name]),
                    ],
                ],
            ];
        });
    }
    public function prepareCreateFormData(): array
    {
        return [
            'roles' => Role::orderBy('name')->pluck('name', 'id')->toArray()
        ];
    }
    public function createUser(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'role' => $data['role'],
        ]);

        $role = Role::findById($data['role']);
        $user->assignRole($role);

        return $user;
    }
    public function prepareEditFormData(string $id): array
    {
        $user = User::findOrFail($id);
        return array_merge(
            [
                'user' => $user,
                'userRole' => $user->roles->first()?->id ?? null
            ],
            $this->prepareCreateFormData()
        );
    }
    public function updateUser(User $user, array $data): User
    {
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->save();

        $role = Role::findById($data['role']);
        $user->syncRoles([$role]);

        return $user;
    }
    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}

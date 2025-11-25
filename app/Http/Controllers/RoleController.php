<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleStoreRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private RoleService $roleService) {}

    public function index(Request $request): View
    {
        $this->authorize('list role');
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.roles.index', $this->roleService->getRoleIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create role');
        $permissions = $this->roleService->prepareCreateFormData();
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());
        $this->authorize('create role');
        $validated = $request->validated();
        $this->roleService->createRole($validated['name'], $validated['permissions'] ?? []);
        return redirect()->route('roles.index')->with('success', __('Role created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('view role');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('edit role');
        $role = Role::findOrFail($id);
        return view('admin.roles.edit', $this->roleService->prepareEditFormData($role));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        dd($request->all());
        $this->authorize('edit role');
        $validated = $request->validated();
        $role = Role::findOrFail($id);
        $this->roleService->updateRole($role, $validated['name'], $validated['permissions'] ?? []);
        return redirect()->route('roles.index')->with('success', __('Role updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete role');
        $role = Role::findOrFail($id);
        if ($role->name === 'super-admin') {
            return redirect()->route('roles.index')->with('error', __('Cannot delete protected role super-admin.'));
        }
        $this->roleService->deleteRole($role);
        return redirect()->route('roles.index')->with('success', __('Role deleted successfully.'));
    }
}

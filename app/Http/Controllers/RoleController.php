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
        $this->authorize('viewAny', Role::class);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.roles.index', $this->roleService->getRoleIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', Role::class);
        $permissions = $this->roleService->prepareCreateFormData();

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleStoreRequest $request)
    {
        $this->authorize('create', Role::class);
        $validated = $request->validated();
        $permissions = $validated['permissions'] ?? [];
        $this->roleService->createRole($validated['name'], $permissions);

        return redirect()->route('roles.index')->with('success', __('Role created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('view', $role);
        // TODO: Return view when show view is implemented
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);
        return view('admin.roles.edit', $this->roleService->prepareEditFormData($role));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, string $id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('update', $role);
        $validated = $request->validated();
        $permissions = $validated['permissions'] ?? [];
        $this->roleService->updateRole($role, $validated['name'], $permissions);
        return redirect()->route('roles.index')->with('success', __('Role updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);
        $this->authorize('delete', $role);
        $this->roleService->deleteRole($role);
        return redirect()->route('roles.index')->with('success', __('Role deleted successfully.'));
    }
}

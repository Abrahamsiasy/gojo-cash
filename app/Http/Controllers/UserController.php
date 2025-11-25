<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private UserService $userService) {}

    public function index(Request $request): View
    {
        $this->authorize('list user');
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.users.index', $this->userService->getUserIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create user');
        return view('admin.users.create', $this->userService->prepareCreateFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        $this->authorize('create user');
        $validated = $request->validated();
        $this->userService->createUser($validated);
        return redirect()->route('users.index')->with('success', __('User created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->authorize('view user');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->authorize('edit user');
        return view('admin.users.edit', $this->userService->prepareEditFormData($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit user');
        $user = User::findOrFail($id);
        $data = $request->only(['name', 'email', 'role']);
        $this->userService->updateUser($user, $data);
        return redirect()->route('users.index')->with('success', __('User updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete user');
        $user = User::findOrFail($id);
        if ($user->hasRole('super-admin')) {
            return redirect()->route('users.index')->with('error', __('Cannot delete protected user super-admin.'));
        }
        $this->userService->deleteUser($user);
        return redirect()->route('users.index')->with('success', __('User deleted successfully.'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
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
        $this->authorize('viewAny', User::class);
        $search = $request->string('search');
        $searchValue = $search->isNotEmpty() ? $search->toString() : null;

        return view('admin.users.index', $this->userService->getUserIndexData($searchValue));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', $this->userService->prepareCreateFormData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        $this->authorize('create', User::class);
        $validated = $request->validated();
        $this->userService->createUser($validated);

        return redirect()->route('users.index')->with('success', __('User created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);
        // TODO: Return view when show view is implemented
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        return view('admin.users.edit', $this->userService->prepareEditFormData($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        $validated = $request->validated();
        $this->userService->updateUser($user, $validated);

        return redirect()->route('users.index')->with('success', __('User updated successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        $this->userService->deleteUser($user);

        return redirect()->route('users.index')->with('success', __('User deleted successfully.'));
    }
}

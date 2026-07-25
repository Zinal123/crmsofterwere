<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use App\Services\Admin\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private UserService $service, private RoleService $roleService)
    {
    }

    public function index()
    {
        return view('admin.users', [
            'users' => $this->service->listAllWithRoles(),
            'roles' => $this->roleService->listRolesWithPermissions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|string|exists:roles,name',
        ]);

        $this->service->createUser($request->only(['name', 'email', 'password', 'role']));

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
            'is_active' => 'required|boolean',
        ]);

        try {
            $this->service->updateRoleAndStatus($id, $request->input('role'), (bool) $request->input('is_active'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.users.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
        ]);

        try {
            $this->service->updateProfile($id, $request->input('name'), $request->input('email'), $request->input('password'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.users.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.users.index')->with('success', 'User details updated.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private RoleService $service)
    {
    }

    public function index()
    {
        return view('admin.roles', [
            'roles' => $this->service->listRolesWithPermissions(),
            'permissions' => $this->service->allPermissionNames(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $this->service->createRole($request->input('name'));

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255']);

        try {
            $this->service->renameRole($id, $request->input('name'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.roles.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role renamed.');
    }

    public function togglePermission(Request $request, $roleId)
    {
        try {
            $granted = $this->service->togglePermission($roleId, $request->input('permission'));
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['granted' => $granted]);
    }

    public function destroy($id)
    {
        try {
            $this->service->deleteRole($id);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.roles.index')->with('error', $e->getMessage());
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}

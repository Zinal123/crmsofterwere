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

    public function togglePermission(Request $request, $roleId)
    {
        $granted = $this->service->togglePermission($roleId, $request->input('permission'));

        return response()->json(['granted' => $granted]);
    }

    public function destroy($id)
    {
        $this->service->deleteRole($id);

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }
}

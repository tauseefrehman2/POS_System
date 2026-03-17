<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use jeremykenedy\LaravelRoles\Models\Permission;
use jeremykenedy\LaravelRoles\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::all();

        return response()->json($permissions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:permissions',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return response()->json($permission, 201);
    }

    public function show(Permission $permission)
    {
        return response()->json($permission);
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string',
            'slug' => 'required|string|unique:permissions,slug,'.$permission->id,
        ]);

        $permission->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);

        return response()->json($permission);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json(['message' => 'Permission deleted']);
    }

    public function assignToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $user = User::find($request->user_id);
        $permission = Permission::find($request->permission_id);
        $user->attachPermission($permission);
        // dd($permission);

        return response()->json(['message' => 'Permission assigned to user']);
    }

    public function assignToRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role = Role::find($request->role_id);
        $permission = Permission::find($request->permission_id);

        $role->attachPermission($permission);

        return response()->json(['message' => 'Permission assigned to role']);
    }
}

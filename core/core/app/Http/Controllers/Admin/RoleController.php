<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Roles List
    public function index()
    {
        $data['page_title'] = 'Roles';
        $data['pageTitle'] = $data['page_title'];
        $data['roles'] = Role::with('permissions')->paginate(25);
        $data['emptyMessage'] = 'No roles found';
        return view('admin.roles.index', $data);
    }

    // Create Role Form
    public function create()
    {
        $data['page_title'] = 'Create Role';
        $data['pageTitle'] = $data['page_title'];
        $data['permissions'] = Permission::all();
        return view('admin.roles.create', $data);
    }

    // Store Role
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'nullable|array'
        ]);

        $role = Role::create(['name' => $request->name, 'guard_name' => 'admin']);

        if ($request->permissions) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    // Edit Role Form
    public function edit(Role $role)
    {
        $data['page_title'] = 'Edit Role';
        $data['pageTitle'] = $data['page_title'];
        $data['role'] = $role;
        $data['permissions'] = Permission::all();
        return view('admin.roles.edit', $data);
    }

    // Update Role
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array'
        ]);

        $role->update(['name' => $request->name, 'guard_name' => 'admin']);

        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    // Delete Role
    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    // Show Role Details
    public function show(Role $role)
    {
        $data['page_title'] = 'Role Details';
        $data['pageTitle'] = $data['page_title'];
        $data['role'] = $role;
        return view('admin.roles.details', $data);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $data['page_title'] = 'Permissions';
        $data['pageTitle'] = $data['page_title']; // ✅ Blade এর জন্য ঠিক
        $data['permissions'] = Permission::paginate(25);
        $data['emptyMessage'] = 'No permissions found';
        return view('admin.permissions.index', $data);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:permissions,name']);
        Permission::create(['name' => $request->name, 'guard_name' => 'admin' ]);
        return back()->with('success', 'Permission created successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return back()->with('success', 'Permission deleted successfully.');
    }
}

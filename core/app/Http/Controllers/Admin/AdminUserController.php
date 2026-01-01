<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index()
    {
        $data['page_title'] = 'Admins';
        $data['pageTitle'] = $data['page_title'];
        $data['admins'] = Admin::with('roles')->paginate(25);
        $data['emptyMessage'] = 'No admins found';
        return view('admin.adminuser.index', $data);
    }

    public function create()
    {
        $data['page_title'] = 'Create Admin';
        $data['pageTitle'] = $data['page_title'];
        $data['roles'] = Role::all(); // add roles
        return view('admin.adminuser.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:admins',
            'email' => 'required|email|unique:admins',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|exists:roles,name'
        ]);

        $admin = new Admin();
        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->email = $request->email;
        $admin->password = Hash::make($request->password);
        $admin->save();

        // Assign Role
        if ($request->role) {
            $admin->assignRole($request->role);
        }

        return redirect()->route('admin.admins.index')->with('success', 'Admin created successfully.');
    }

    public function edit(Admin $admin)
    {
        $data['page_title'] = 'Edit Admin';
        $data['pageTitle'] = $data['page_title'];
        $data['admin'] = $admin;
        $data['roles'] = Role::all();
        return view('admin.adminuser.edit', $data);
    }

    public function update(Request $request, Admin $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:admins,username,' . $admin->id,
            'email' => 'required|email|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'nullable|exists:roles,name'
        ]);

        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->email = $request->email;

        if ($request->password) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        // Sync Role
        if ($request->role) {
            $admin->syncRoles([$request->role]);
        } else {
            $admin->roles()->detach();
        }

        return redirect()->route('admin.admins.index')->with('success', 'Admin updated successfully.');
    }

    public function destroy(Admin $admin)
    {
        $admin->delete();
        return redirect()->route('admin.admins.index')->with('success', 'Admin deleted successfully.');
    }

    public function show(Admin $admin)
    {
        $data['page_title'] = 'Admin Details';
        $data['pageTitle'] = $data['page_title'];
        $data['admin'] = $admin;
        return view('admin.adminuser.details', $data);
    }
}

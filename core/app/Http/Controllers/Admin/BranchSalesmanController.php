<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class BranchSalesmanController extends Controller
{
    public function index()
    {
        $this->ensureSalesmanRole();
        $branchId = auth()->guard('admin')->user()->branch_id;
        $salesmen = Admin::role('branch_salesman')
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->paginate(20);
        $pageTitle = 'Branch Salesmen';

        return view('admin.branch_salesmen.index', compact('salesmen', 'pageTitle'));
    }

    public function create()
    {
        $pageTitle = 'Create Salesman';

        return view('admin.branch_salesmen.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $this->ensureSalesmanRole();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:admins,username',
            'email' => 'required|email|max:255|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $admin = new Admin();
        $admin->name = $data['name'];
        $admin->username = $data['username'];
        $admin->email = $data['email'];
        $admin->password = Hash::make($data['password']);
        $admin->branch_id = auth()->guard('admin')->user()->branch_id;
        $admin->save();

        $admin->assignRole('branch_salesman');

        return redirect()->route('admin.branch-salesmen.index')->with('success', 'Salesman created for your branch.');
    }

    public function destroy(Admin $salesman)
    {
        abort_unless(
            $salesman->branch_id === auth()->guard('admin')->user()->branch_id
                && $salesman->hasRole('branch_salesman'),
            404
        );

        $salesman->delete();

        return redirect()->route('admin.branch-salesmen.index')->with('success', 'Salesman removed from your branch.');
    }

    private function ensureSalesmanRole(): void
    {
        Role::firstOrCreate(['name' => 'branch_salesman', 'guard_name' => 'admin']);
    }
}
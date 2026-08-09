<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerExport;


class CustomerController extends Controller
{
    //

    public function index(Request $request)
    {
        $query = Customer::query();

        // simple search/filter
        if ($term = $request->query('q')) {
            $query->where(fn($q) => $q
                ->where('name', 'like', "%$term%")
                ->orWhere('company', 'like', "%$term%")
                ->orWhere('contact_number', 'like', "%$term%"));
        }
        $pageTitle = 'Customer Database';

        $customers = $query->latest()->paginate(7)->withQueryString();

        return view('admin.customers.index', compact('customers', 'term', 'pageTitle'));
    }

    public function edit(Customer $customer)
    {

        $pageTitle = 'Customer Information Edit';
        $bd        = getBangladeshLocationData();
        $postcodes = json_decode(
            file_get_contents(resource_path('data/bd-postcodes.json')),
            true
        )['postcodes'];

        return view('admin.customers.edit', compact('customer', 'bd', 'postcodes','pageTitle'));
    }

    public function show(Customer $customer)
    {
        $pageTitle = 'Customer Information';
        $bd = getBangladeshLocationData();
        $divName = collect($bd['divisions'])->pluck('name', 'id');
        $disName = collect($bd['districts'])->pluck('name', 'id');
        $upaName = collect($bd['upazilas'])->pluck('name', 'id');
        return view('admin.customers.show', compact('customer', 'divName', 'disName', 'upaName', 'pageTitle'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string',
            'company'        => 'nullable|string',
            'contact_number' => 'required|string',
            'email'          => 'nullable|email',
            'division_id'    => 'required|integer',
            'district_id'    => 'required|integer',
            // 'thana_id'       => 'required',
            'area_name' => 'required|string',
            'postcode'       => 'nullable|string',
            'remarks'        => 'nullable|string',
        ]);

        Customer::create($data);
        return back()->with('success', 'Customer added.');
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'           => 'required|string',
            'company'        => 'nullable|string',
            'contact_number' => 'required|string',
            'email'          => 'nullable|email',
            'division_id'    => 'required|integer',
            'district_id'    => 'required|integer',
            // 'thana_id'       => 'required',
            'area_name' => 'required|string',
            'postcode'       => 'nullable|string',
            'remarks'        => 'nullable|string',
        ]);

        $customer->update($data);

        return redirect()->route('admin.customers.index')
                     ->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return back()->with('success', 'Customer removed.');
    }

    public function export()
    {
        return Excel::download(new CustomerExport, 'customers.xlsx');
    }
}

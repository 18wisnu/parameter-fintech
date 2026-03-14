<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\NewCustomerNotification; 
use App\Models\User; 
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::latest();

        // 1. Search
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_id', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 2. Filter Status
        if ($request->status) {
            if ($request->status == 'isolated') {
                $query->where('is_isolated', 1);
            } elseif ($request->status == 'active') {
                $query->where('is_isolated', 0);
            }
        }

        $customers = $query->paginate(15)->withQueryString();
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        $staff = User::whereIn('role', ['teknisi', 'kolektor'])->get();
        return view('admin.customers.create', compact('staff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            // customer_id boleh kosong (nullable), tapi kalau ada harus unik
            'customer_id' => 'nullable|unique:customers,customer_id', 
        ]);

        $data = $request->all();
        $customer = Customer::create($data);

        $admins = User::whereIn('role', ['admin', 'owner'])->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewCustomerNotification($customer));
        }

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan');
    }

    // --- FUNGSI BARU UNTUK EDIT (Mengatasi Error Merah) ---
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $staff = User::whereIn('role', ['teknisi', 'kolektor'])->get();
        return view('admin.customers.edit', compact('customer', 'staff'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'type' => 'required',
            'customer_id' => 'nullable|unique:customers,customer_id,' . $id, // Abaikan ID diri sendiri saat cek unik
        ]);

        $customer = Customer::findOrFail($id);
        $customer->update($request->all());

        return redirect()->route('customers.index')->with('success', 'Data pelanggan berhasil diupdate');
    }
    // -----------------------------------------------------

    public function destroy($id)
    {
        Customer::destroy($id);
        return redirect()->route('customers.index')->with('success', 'Pelanggan dihapus');
    }

    // Export & Import
    public function export() { return Excel::download(new CustomersExport, 'data-pelanggan.xlsx'); }
    public function import(Request $request) 
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new CustomersImport, $request->file('file'));
        return redirect()->back()->with('success', 'Import Berhasil');
    }
}
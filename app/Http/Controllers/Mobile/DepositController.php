<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Customer; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    // 1. Tampilkan Form dengan Daftar Pelanggan
    public function create()
    {
        // Ambil semua data pelanggan, urutkan nama abjad
        $customers = Customer::orderBy('name', 'asc')->get();
        
        return view('mobile.deposits.create', compact('customers'));
    }

    // 2. Proses Simpan
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'customer_id' => 'required', // Wajib pilih pelanggan
            'notes' => 'nullable|string', // Catatan tambahan (opsional)
            'proof_image' => 'nullable|image|max:2048',
        ]);

        // Upload Foto
        $path = null;
        if ($request->hasFile('proof_image')) {
            $path = $request->file('proof_image')->store('deposits', 'public');
        }

        // Cari Nama Pelanggan berdasarkan ID yang dipilih
        $customer = Customer::find($request->customer_id);
        $customerName = $customer ? $customer->name : 'Tanpa Nama';

        // Gabungkan Nama + Catatan untuk kolom Description
        // Contoh hasil: "Yudi Voucher (Bayar Bulan Maret)"
        $description = $customerName;
        if($request->notes) {
            $description .= ' (' . $request->notes . ')';
        }

        Deposit::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'description' => $description, // <--- Ini yang kita ubah otomatis
            'proof_image' => $path,
            'status' => 'pending',
        ]);

        return redirect()->route('mobile.home')->with('success', 'Setoran berhasil dikirim!');
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Menampilkan Daftar Pegawai
    public function index()
    {
        $users = User::latest()->get();
        return view('admin.users.index', compact('users'));
    }

    // Menambah Pegawai Baru
    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:owner,admin,teknisi,kolektor',
            'base_salary' => 'nullable|numeric|min:0',
        ]);

        $currentBusinessId = auth()->user()->current_business_id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            'base_salary' => $request->base_salary ?? 0,
            'current_business_id' => $currentBusinessId,
        ]);

        // Hubungkan ke unit bisnis aktif di tabel pivot bila tabel ada
        if ($currentBusinessId && \Illuminate\Support\Facades\Schema::hasTable('business_user')) {
            $user->businesses()->syncWithoutDetaching([
                $currentBusinessId => ['role' => $request->role]
            ]);
        }

        return redirect()->back()->with('success', "Pegawai '{$user->name}' berhasil ditambahkan!");
    }

    // Update Data Pegawai (Nama, Email, Gaji, Password Opsional)
    public function update(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role'        => 'required|in:owner,admin,teknisi,kolektor',
            'base_salary' => 'nullable|numeric|min:0',
            'password'    => 'nullable|string|min:6',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->base_salary = $request->base_salary ?? 0;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->save();

        // Update role di pivot table untuk bisnis aktif jika tabel ada
        $currentBusinessId = auth()->user()->current_business_id;
        if ($currentBusinessId && \Illuminate\Support\Facades\Schema::hasTable('business_user')) {
            $user->businesses()->syncWithoutDetaching([
                $currentBusinessId => ['role' => $request->role]
            ]);
        }

        return redirect()->back()->with('success', "Data pegawai '{$user->name}' berhasil diperbarui!");
    }

    // Update Jabatan (Role) Pegawai secara cepat
    public function updateRole(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        $request->validate([
            'role' => 'required|in:owner,admin,teknisi,kolektor' 
        ]);

        $user->role = $request->role;
        $user->save();

        // Update pivot role juga untuk bisnis yang aktif
        $currentBusinessId = auth()->user()->current_business_id;
        if ($currentBusinessId && \Illuminate\Support\Facades\Schema::hasTable('business_user')) {
            $user->businesses()->syncWithoutDetaching([
                $currentBusinessId => ['role' => $request->role]
            ]);
        }

        return redirect()->back()->with('success', 'Jabatan berhasil diubah!');
    }

    // Hapus Pegawai dengan proteksi integritas data finansial
    public function destroy(\App\Models\User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        // Cek riwayat setoran (deposits)
        $hasDeposits = \Illuminate\Support\Facades\Schema::hasTable('deposits') 
            && \App\Models\Deposit::where('user_id', $user->id)->exists();

        // Cek riwayat gaji (salaries)
        $hasSalaries = \Illuminate\Support\Facades\Schema::hasTable('salaries') 
            && \App\Models\Salary::where('user_id', $user->id)->exists();

        // Cek riwayat transaksi (transactions)
        $hasTransactions = \Illuminate\Support\Facades\Schema::hasTable('transactions') 
            && \App\Models\Transaction::where('user_id', $user->id)->exists();

        if ($hasDeposits || $hasSalaries || $hasTransactions) {
            return redirect()->back()->with('error', "Pegawai '{$user->name}' tidak dapat dihapus karena memiliki riwayat transaksi/keuangan (setoran/gaji). Silakan nonaktifkan atau ubah jabatannya.");
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->back()->with('success', "Pegawai '{$userName}' berhasil dihapus!");
    }
}
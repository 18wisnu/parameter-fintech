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

    // Update Jabatan (Role) Pegawai
    public function updateRole(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        // Cek apakah yang nyuruh ini Owner?
        if (auth()->user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Hanya Owner yang bisa ganti jabatan!');
        }

        // Validasi input
        $request->validate([
            'role' => 'required|in:owner,admin,teknisi,staff'
        ]);

        // Simpan perubahan
        $user->update(['role' => $request->role]);

        return redirect()->back()->with('success', 'Jabatan berhasil diubah!');
    }
}
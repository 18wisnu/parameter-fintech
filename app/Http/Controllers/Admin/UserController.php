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
        // ... (kodingan atasnya biarkan) ...

        $request->validate([
            // KEMBALIKAN JADI KOLEKTOR
            'role' => 'required|in:owner,admin,teknisi,kolektor' 
        ]);

        $user->role = $request->role; // Jalur paksa update
        $user->save();

        return redirect()->back()->with('success', 'Jabatan berhasil diubah!');
    }
}
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
    public function updateRole(Request $request, User $user)
    {
        // Hanya Owner yang boleh ganti jabatan orang lain
        if (auth()->user()->role !== 'owner') {
            return redirect()->back()->with('error', 'Cuma Owner yang bisa ganti jabatan!');
        }

        $user->update([
            'role' => $request->role
        ]);

        return redirect()->back()->with('success', 'Jabatan ' . $user->name . ' berhasil diupdate!');
    }
}
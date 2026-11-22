<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Pendaftaran = mendaftarkan tenant. Yang mendaftarkan tenant menjadi owner tenant itu.
 * - Daftar Baru   : buat akun baru + tenant pertamanya.
 * - Tambah Tenant : akun (email) sudah ada, tambahkan tenant baru ke akun tersebut.
 */
class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /** Daftar akun baru sekaligus tenant pertamanya. */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'business_name' => ['required', 'string', 'max:255'],
        ], [
            // Satu email = satu akun untuk semua tenant. Jangan buat akun kedua.
            'email.unique' => 'Email ini sudah punya akun. Pilih "Tambah Tenant" untuk menambahkan unit bisnis baru ke akun ini.',
        ]);

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'owner',
            ]);

            Business::registerFor($user, $request->business_name);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))
            ->with('success', 'Akun & unit bisnis berhasil dibuat. Silakan atur konfigurasi awal.');
    }

    /** Akun sudah ada: verifikasi email + password, lalu daftarkan tenant baru untuk akun itu. */
    public function storeTenant(Request $request): RedirectResponse
    {
        $request->merge(['email' => strtolower(trim((string) $request->email))]);

        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'business_name' => ['required', 'string', 'max:255'],
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ])->redirectTo(route('register', ['mode' => 'tenant']));
        }

        $request->session()->regenerate();

        $business = Business::registerFor(Auth::user(), $request->business_name);

        return redirect(route('dashboard', absolute: false))
            ->with('success', "Unit bisnis '{$business->name}' berhasil ditambahkan. Anda adalah pemiliknya.");
    }
}

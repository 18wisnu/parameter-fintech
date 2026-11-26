<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    // 1. Arahkan user ke halaman login Google
    public function redirect()
    {
        // Selalu tampilkan pemilih akun Google, supaya bisa ganti akun setelah logout
        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    // 2. Terima balikan data dari Google
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Cek apakah email ini ada di database kita?
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Update data Google terbaru
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);

                Auth::login($user);
                
                \App\Models\ActivityLog::create([
                    'user_id' => $user->id,
                    'activity' => 'Login via Google',
                    'ip_address' => request()->ip(),
                ]);

                // Panggil "Satpam" untuk mengarahkan halaman berdasarkan role
                return $this->redirectByRole($user);

            }

            if (blank($googleUser->email)) {
                return redirect('/login')->with('error', 'Akun Google Anda tidak memberikan alamat email. Silakan daftar dengan email & kata sandi.');
            }

            // Email belum terdaftar: buat akun baru, lalu arahkan untuk membuat usaha (masa coba gratis)
            $user = User::create([
                'name' => $googleUser->name ?: strstr($googleUser->email, '@', true),
                'email' => strtolower($googleUser->email),
                'password' => Hash::make(Str::random(40)), // masuk lewat Google; kata sandi bisa diatur nanti
                'role' => 'owner',
                'google_id' => $googleUser->id,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save(); // email sudah diverifikasi Google

            event(new Registered($user));
            Auth::login($user);

            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'activity' => 'Daftar via Google',
                'ip_address' => request()->ip(),
            ]);

            return redirect()->route('admin.business.index')
                ->with('success', 'Akun Anda berhasil dibuat. Buat usaha pertama Anda untuk mulai masa coba gratis. Kalau Anda pegawai, minta pemilik usaha menambahkan email ini di menu Pegawai.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Login Google gagal: ' . $e->getMessage(), ['exception' => get_class($e)]);

            // Pengguna menekan "Batal" di halaman Google
            if (request()->filled('error')) {
                return redirect('/login')->with('error', 'Login dengan Google dibatalkan.');
            }

            return redirect('/login')->with('error', 'Gagal login dengan Google. Silakan coba lagi.');
        }
    }

    /**
     * Logika Satpam (Redirect berdasarkan Role)
     */
    protected function redirectByRole($user)
    {
        // Role mengikuti tenant aktif (users.role disinkronkan dari pivot)
        if (!$user->ensureCurrentBusiness() && \App\Models\Business::count() > 0) {
            return redirect()->route('admin.business.index');
        }

        // Jalur Utama (Owner & Admin)
        if (in_array($user->role, ['owner', 'admin'])) {
            return redirect()->intended('/dashboard');
        } 
        
        // Jalur Lapangan (Teknisi & Kolektor)
        if (in_array($user->role, ['teknisi', 'kolektor'])) {
            return redirect()->intended('/mobile/home');
        }
        
        // Default jika role tidak dikenal
        return redirect('/');
    }
}
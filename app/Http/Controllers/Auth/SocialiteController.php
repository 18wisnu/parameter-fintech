<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    // 1. Arahkan user ke halaman login Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // 2. Terima balikan data dari Google
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Cek apakah email ini ada di database kita?
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // Jika ada, update ID Google & Avatar, lalu Login
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);

                Auth::login($user);

                // Arahkan sesuai Role (Logika Satpam)
                return $this->redirectByRole($user);
            } else {
                // Jika email tidak dikenal, tolak!
                return redirect('/login')->with('error', 'Email Anda tidak terdaftar di sistem Parameter Fintech.');
            }
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Gagal login dengan Google.');
        }
    }

    // Fungsi Logika Satpam (Redirect)
    protected function redirectByRole($user)
    {
        if (in_array($user->role, ['owner', 'admin'])) {
            return redirect()->intended('/dashboard');
        } elseif (in_array($user->role, ['teknisi', 'kolektor'])) {
            return redirect()->intended('/mobile/home');
        }
        
        return redirect('/');
    }
}
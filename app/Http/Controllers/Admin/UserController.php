<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Manajemen pegawai per tenant.
 * Satu email = satu akun; role & gaji pokok disimpan per tenant di pivot business_user.
 */
class UserController extends Controller
{
    private function currentBusiness(): Business
    {
        return Business::findOrFail(auth()->user()->current_business_id);
    }

    /** Tolak akses jika pegawai bukan anggota tenant yang aktif. */
    private function authorizeMember(User $user): void
    {
        if (!$user->belongsToBusiness(auth()->user()->current_business_id)) {
            abort(403, 'Pegawai ini tidak terdaftar di unit bisnis yang aktif.');
        }
    }

    private function isTenantOwner(User $user): bool
    {
        return (int) $this->currentBusiness()->owner_id === (int) $user->id;
    }

    private function roleRule(): string
    {
        return 'required|in:' . implode(',', User::ASSIGNABLE_ROLES);
    }

    // Menampilkan Daftar Pegawai (hanya anggota tenant aktif, dengan role & gaji di tenant ini)
    public function index()
    {
        $business = $this->currentBusiness();

        $users = User::membersOf($business->id)
            ->orderByRaw("CASE WHEN users.id = ? THEN 0 ELSE 1 END", [$business->owner_id])
            ->latest('users.created_at')
            ->get();

        $ownerId = (int) $business->owner_id;
        $assignableRoles = User::ASSIGNABLE_ROLES;

        return view('admin.users.index', compact('users', 'ownerId', 'assignableRoles'));
    }

    // Menambah Pegawai Baru / menghubungkan email yang sudah terdaftar ke tenant ini
    public function store(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->email))]);

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255',
            'role'        => $this->roleRule(),
            'base_salary' => 'nullable|numeric|min:0',
        ]);

        $businessId = $this->currentBusiness()->id;
        $pivot = ['role' => $request->role, 'base_salary' => $request->base_salary ?? 0];

        $existing = User::where('email', $request->email)->first();

        if ($existing) {
            if ($existing->belongsToBusiness($businessId)) {
                return redirect()->back()->with('error', "Email {$request->email} sudah terdaftar sebagai pegawai di unit bisnis ini.");
            }

            // Akun sudah ada (di tenant lain / daftar sendiri): cukup hubungkan.
            // Nama & password tidak diubah supaya akun di tenant lain tidak terganggu.
            $existing->businesses()->attach($businessId, $pivot);

            if (!$existing->current_business_id) {
                $existing->switchBusiness($businessId);
            }

            return redirect()->back()->with('success', "Akun '{$existing->name}' ({$existing->email}) sudah terdaftar, dan sekarang terhubung ke unit bisnis ini. Mereka bisa login dengan password yang sudah ada.");
        }

        $request->validate([
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name'                => $request->name,
            'email'               => $request->email,
            'password'            => Hash::make($request->password),
            'role'                => $request->role,
            'current_business_id' => $businessId,
        ]);

        $user->businesses()->attach($businessId, $pivot);

        return redirect()->back()->with('success', "Pegawai '{$user->name}' berhasil ditambahkan!");
    }

    // Update Data Pegawai (Nama, Email, Password Opsional) + jabatan & gaji di tenant ini
    public function update(Request $request, User $user)
    {
        $this->authorizeMember($user);
        $isOwner = $this->isTenantOwner($user);

        $request->merge(['email' => strtolower(trim((string) $request->email))]);

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'        => $isOwner ? 'nullable' : $this->roleRule(),
            'base_salary' => 'nullable|numeric|min:0',
            'password'    => 'nullable|string|min:6',
        ]);

        // Akun dipakai di semua tenant. Jika akun juga terdaftar di tenant lain, nama/email/password
        // hanya boleh diganti oleh pemilik akun itu sendiri (supaya tenant lain tidak bisa mengambil alih akun).
        $sharedElsewhere = $user->businesses()->where('businesses.id', '!=', $this->currentBusiness()->id)->exists();
        $canEditAccount = !$sharedElsewhere || auth()->id() === $user->id;

        if ($canEditAccount) {
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
        }

        $this->updateMembership($user, $isOwner ? null : $request->role, $request->base_salary ?? 0);

        $message = "Data pegawai '{$user->name}' berhasil diperbarui!";
        if (!$canEditAccount) {
            $message .= ' (Nama, email & password tidak diubah karena akun ini juga dipakai di unit bisnis lain.)';
        }

        return redirect()->back()->with('success', $message);
    }

    // Update Jabatan (Role) Pegawai secara cepat
    public function updateRole(Request $request, User $user)
    {
        $this->authorizeMember($user);

        if ($this->isTenantOwner($user)) {
            return redirect()->back()->with('error', 'Jabatan pemilik (yang mendaftarkan unit bisnis) tidak bisa diubah.');
        }

        $request->validate(['role' => $this->roleRule()]);

        $this->updateMembership($user, $request->role);

        return redirect()->back()->with('success', 'Jabatan berhasil diubah!');
    }

    /**
     * Simpan role/gaji di pivot tenant aktif. users.role hanya ikut berubah
     * jika tenant ini memang sedang aktif bagi user tersebut.
     */
    private function updateMembership(User $user, ?string $role, $baseSalary = null): void
    {
        $businessId = $this->currentBusiness()->id;

        $data = [];
        if ($role !== null) {
            $data['role'] = $role;
        }
        if ($baseSalary !== null) {
            $data['base_salary'] = $baseSalary;
        }
        if ($data) {
            $user->businesses()->updateExistingPivot($businessId, $data);
        }

        if ($role !== null && (int) $user->current_business_id === (int) $businessId) {
            $user->role = $role;
            $user->save();
        }
    }

    // Keluarkan pegawai dari tenant ini (akun dihapus total hanya jika tidak dipakai tenant lain)
    public function destroy(User $user)
    {
        $this->authorizeMember($user);

        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri!');
        }

        if ($this->isTenantOwner($user)) {
            return redirect()->back()->with('error', 'Pemilik unit bisnis tidak bisa dihapus dari unit bisnisnya.');
        }

        $businessId = $this->currentBusiness()->id;

        // Model keuangan memakai scope tenant, jadi pengecekan ini hanya untuk tenant aktif
        $hasDeposits = Schema::hasTable('deposits')
            && \App\Models\Deposit::where('user_id', $user->id)->exists();

        $hasSalaries = Schema::hasTable('salaries')
            && \App\Models\Salary::where('user_id', $user->id)->exists();

        $hasTransactions = Schema::hasTable('transactions')
            && \App\Models\Transaction::where('user_id', $user->id)->exists();

        if ($hasDeposits || $hasSalaries || $hasTransactions) {
            return redirect()->back()->with('error', "Pegawai '{$user->name}' tidak dapat dihapus karena memiliki riwayat transaksi/keuangan (setoran/gaji). Silakan nonaktifkan atau ubah jabatannya.");
        }

        $userName = $user->name;

        $user->businesses()->detach($businessId);

        // Masih anggota tenant lain, atau pemilik tenant lain: akun tetap ada
        if ($user->businesses()->exists() || $user->ownedBusinesses()->exists()) {
            if ((int) $user->current_business_id === (int) $businessId) {
                $user->current_business_id = null;
                $user->save();
                $user->ensureCurrentBusiness();
            }

            return redirect()->back()->with('success', "Pegawai '{$userName}' dikeluarkan dari unit bisnis ini. Akunnya tetap aktif di unit bisnis lain.");
        }

        $user->delete();

        return redirect()->back()->with('success', "Pegawai '{$userName}' berhasil dihapus!");
    }
}

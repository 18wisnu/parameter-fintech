<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Deposit;

// Diagnostic + Auto-Fix route (TEMPORARY)
Route::get('/diag-fix', function() {
    $users = \Illuminate\Support\Facades\DB::table('users')->select('id','name','email','role')->get();
    $businesses = \Illuminate\Support\Facades\DB::table('businesses')->select('id','name','owner_id')->get();
    $pivots = \Illuminate\Support\Facades\DB::table('business_user')->get();

    // Auto-fix: link all business owners to their businesses via pivot
    $fixed = [];
    foreach ($businesses as $b) {
        if ($b->owner_id) {
            $exists = \Illuminate\Support\Facades\DB::table('business_user')
                ->where('business_id', $b->id)
                ->where('user_id', $b->owner_id)
                ->exists();
            if (!$exists) {
                \Illuminate\Support\Facades\DB::table('business_user')->insert([
                    'business_id' => $b->id,
                    'user_id'     => $b->owner_id,
                    'role'        => 'owner',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
                $fixed[] = "Linked business {$b->id} ({$b->name}) → user {$b->owner_id}";
            }
        }
    }

    return response()->json([
        'users'     => $users,
        'businesses'=> $businesses,
        'pivots'    => $pivots,
        'fixed'     => $fixed,
    ]);
});

// Transfer all businesses to admin@admin.com (user ID 1)
Route::get('/fix-owner', function() {
    $adminId = 1; // admin@admin.com
    $fixed = [];

    $businesses = \Illuminate\Support\Facades\DB::table('businesses')->get();
    foreach ($businesses as $b) {
        // Update owner_id to admin
        \Illuminate\Support\Facades\DB::table('businesses')
            ->where('id', $b->id)
            ->update(['owner_id' => $adminId]);

        // Remove old pivot entries for this business
        \Illuminate\Support\Facades\DB::table('business_user')
            ->where('business_id', $b->id)
            ->delete();

        // Insert correct pivot for admin
        \Illuminate\Support\Facades\DB::table('business_user')->insert([
            'business_id' => $b->id,
            'user_id'     => $adminId,
            'role'        => 'owner',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Reset current_business_id for all users to avoid stale references
        \Illuminate\Support\Facades\DB::table('users')
            ->where('current_business_id', $b->id)
            ->where('id', '!=', $adminId)
            ->update(['current_business_id' => null]);

        $fixed[] = "Business '{$b->name}' (ID:{$b->id}) transferred to admin (ID:{$adminId})";
    }

    return response()->json(['status' => 'OK', 'actions' => $fixed]);
});

// SETUP: Create default business for admin and link ALL legacy data
Route::get('/setup-bisnis', function() {
    $adminId = 1;
    $log = [];

    // 1. Ambil pengaturan lama (nama usaha dari profit_sharing_settings jika ada)
    $namaUsaha = \Illuminate\Support\Facades\DB::table('profit_sharing_settings')
        ->where('key', 'business_name')
        ->value('value');
    if (empty($namaUsaha)) $namaUsaha = 'Usaha Saya';

    // 2. Cek apakah bisnis untuk admin sudah ada
    $existingBusiness = \Illuminate\Support\Facades\DB::table('businesses')
        ->where('owner_id', $adminId)
        ->first();

    if ($existingBusiness) {
        $businessId = $existingBusiness->id;
        $log[] = "Bisnis sudah ada: '{$existingBusiness->name}' (ID:{$businessId})";
    } else {
        // 3. Buat bisnis baru berdasarkan nama dari settings
        $businessId = \Illuminate\Support\Facades\DB::table('businesses')->insertGetId([
            'name'             => $namaUsaha,
            'owner_id'         => $adminId,
            'theme_color'      => '#0369a1',
            'enabled_features' => json_encode([
                'profit_sharing'   => true,
                'reserve_fund'     => true,
                'salary_management'=> true,
                'voucher_analysis' => true,
                'customer_data'    => true,
                'invoices'         => true,
                'transactions'     => true,
                'staff_data'       => true,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $log[] = "Bisnis baru dibuat: '{$namaUsaha}' (ID:{$businessId})";
    }

    // 4. Link admin ke bisnis via pivot (jika belum)
    $pivotExists = \Illuminate\Support\Facades\DB::table('business_user')
        ->where('business_id', $businessId)
        ->where('user_id', $adminId)
        ->exists();
    if (!$pivotExists) {
        \Illuminate\Support\Facades\DB::table('business_user')->insert([
            'business_id' => $businessId,
            'user_id'     => $adminId,
            'role'        => 'owner',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
        $log[] = "Pivot owner dibuat.";
    }

    // 5. Set current_business_id untuk admin
    \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $adminId)
        ->update(['current_business_id' => $businessId]);
    $log[] = "current_business_id admin diset ke {$businessId}.";

    // 6. Hubungkan semua data lama (business_id = null) ke bisnis ini
    $tables = ['customers', 'transactions', 'deposits', 'profit_distributions',
               'salaries', 'incomes', 'expenses', 'profit_sharing_stakeholders'];

    foreach ($tables as $table) {
        try {
            // Cek apakah kolom business_id ada
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
            if (in_array('business_id', $columns)) {
                $affected = \Illuminate\Support\Facades\DB::table($table)
                    ->whereNull('business_id')
                    ->update(['business_id' => $businessId]);
                $log[] = "Tabel '{$table}': {$affected} baris diupdate.";
            } else {
                $log[] = "Tabel '{$table}': kolom business_id belum ada, dilewati.";
            }
        } catch (\Exception $e) {
            $log[] = "Tabel '{$table}': ERROR - " . $e->getMessage();
        }
    }

    return response()->json(['status' => 'SELESAI', 'log' => $log]);
});


// Controller Imports
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Mobile\DepositController;
use App\Http\Controllers\Mobile\SalaryController as MobileSalaryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\TransactionController; 
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\SalaryController as AdminSalaryController;

/*
|--------------------------------------------------------------------------
| 1. RUTE PUBLIK (Bisa diakses siapa saja)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google', [SocialiteController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| 2. RUTE MOBILE (Khusus Teknisi/Kolektor/Staff)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('mobile')->group(function () {
    Route::get('/home', function () {
        $user = Auth::user();
        
        $totalHariIni = Deposit::where('user_id', $user->id)
            ->whereDate('created_at', now()->today())
            ->sum('amount');

        $riwayatHariIni = Deposit::where('user_id', $user->id)
            ->whereDate('created_at', now()->today())
            ->latest()
            ->get();

        return view('mobile.home', compact('totalHariIni', 'riwayatHariIni'));
    })->name('mobile.home');

    Route::get('/deposits', [DepositController::class, 'index'])->name('mobile.deposits.index');
    Route::get('/deposits/create', [DepositController::class, 'create'])->name('mobile.deposits.create');
    Route::post('/deposits', [DepositController::class, 'store'])->name('mobile.deposits.store');
    Route::get('/salary', [MobileSalaryController::class, 'show'])->name('mobile.salary.show');
});

/*
|--------------------------------------------------------------------------
| 3. RUTE ADMIN / OWNER (Wajib Login & Verified)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- DASHBOARD UTAMA ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/deposit/{id}/approve', [DashboardController::class, 'approveDeposit'])->name('admin.deposit.approve');

    // --- MANAJEMEN PEGAWAI & ROLE (PERBAIKAN UTAMA DI SINI) ---
    Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
    // Ini Rute Ganti Role yang Benar (Method PUT):
    Route::put('/admin/users/{user}/update-role', [UserController::class, 'updateRole'])->name('admin.users.updateRole');
    
    // --- MANAJEMEN GAJI (Payroll) ---
    Route::get('/salaries', [AdminSalaryController::class, 'index'])->name('admin.salaries.index');
    Route::post('/salaries/generate', [AdminSalaryController::class, 'generate'])->name('admin.salaries.generate');
    Route::post('/salaries/{id}/pay', [AdminSalaryController::class, 'pay'])->name('admin.salaries.pay');
    Route::patch('/salaries/user/{userId}/update-base', [AdminSalaryController::class, 'updateBaseSalary'])->name('admin.salaries.update-base');

    // --- KEUANGAN (Pemasukan & Pengeluaran) ---
    Route::get('/incomes/create', [IncomeController::class, 'create'])->name('incomes.create');
    Route::post('/incomes', [IncomeController::class, 'store'])->name('incomes.store');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');

    // --- PELANGGAN (Customers) ---
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
    Route::resource('customers', CustomerController::class);

    // --- TAGIHAN (Invoice) ---
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::post('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
    Route::post('invoices/{id}/paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.paid');
    Route::post('invoices/check-isolir', [InvoiceController::class, 'checkIsolir'])->name('invoices.checkIsolir');

    // --- LAPORAN & DANA CADANGAN ---
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    
    Route::get('reports/reserve', [ReportController::class, 'reserve'])->name('reports.reserve');
    Route::post('reports/reserve', [ReportController::class, 'storeUsage'])->name('reports.reserve.store');
    Route::get('reports/reserve/pdf', [ReportController::class, 'exportReservePdf'])->name('reports.reserve.pdf');
    Route::post('/reports/reserve/inject', [ReportController::class, 'storeInjection'])->name('reports.reserve.inject');

    Route::get('reports/history', [ReportController::class, 'history'])->name('reports.history');
    Route::get('reports/history/pdf', [ReportController::class, 'exportHistoryPdf'])->name('reports.history.pdf');
    Route::get('reports/vouchers', [ReportController::class, 'vouchers'])->name('reports.vouchers');
    
    // Hapus Transaksi
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // --- SISTEM & UPDATE ---
    Route::get('/system', [\App\Http\Controllers\Admin\SystemController::class, 'index'])->name('admin.system.index');
    Route::post('/system/update', [\App\Http\Controllers\Admin\SystemController::class, 'updateVersion'])->name('admin.system.update');
    
    // --- PENGATURAN BAGI HASIL ---
    Route::get('/profit-sharing/settings', [\App\Http\Controllers\Admin\ProfitSharingController::class, 'index'])->name('admin.profit_sharing.index');
    Route::post('/profit-sharing/settings/update', [\App\Http\Controllers\Admin\ProfitSharingController::class, 'updateSettings'])->name('admin.profit_sharing.update_settings');
    Route::post('/profit-sharing/stakeholders', [\App\Http\Controllers\Admin\ProfitSharingController::class, 'storeStakeholder'])->name('admin.profit_sharing.stakeholder.store');
    Route::delete('/profit-sharing/stakeholders/{id}', [\App\Http\Controllers\Admin\ProfitSharingController::class, 'destroyStakeholder'])->name('admin.profit_sharing.stakeholder.destroy');

    // --- MULTI TENANCY / BISNIS ---
    Route::get('/business/select', [\App\Http\Controllers\Admin\BusinessController::class, 'index'])->name('admin.business.index');
    Route::post('/business/create', [\App\Http\Controllers\Admin\BusinessController::class, 'store'])->name('admin.business.store');
    Route::post('/business/{business}/select', [\App\Http\Controllers\Admin\BusinessController::class, 'select'])->name('admin.business.select');

    Route::get('/logs', [\App\Http\Controllers\Admin\LogController::class, 'index'])->name('admin.logs.index');

    // --- PROFILE ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- NOTIFIKASI ---
    Route::get('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    })->name('notifications.readAll');

});

require __DIR__.'/auth.php';
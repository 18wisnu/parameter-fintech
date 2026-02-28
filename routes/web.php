<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Deposit; 

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
    
    // Hapus Transaksi
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

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
<?php

use App\Http\Controllers\Mobile\DepositController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use App\Models\Deposit; 
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\InvoiceController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google', [SocialiteController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'callback']);

// Rute Mobile (Khusus Teknisi/Kolektor) - Kita buat placeholder dulu
Route::middleware(['auth'])->prefix('mobile')->group(function () {
    
    // Halaman Home Mobile (YANG BARU)
    Route::get('/home', function () {
        $user = Auth::user();
        
        // 1. Hitung Total Setoran HARI INI milik user yang login
        $totalHariIni = Deposit::where('user_id', $user->id)
            ->whereDate('created_at', now()->today())
            ->sum('amount');

        // 2. Ambil Daftar Riwayat HARI INI (Urutkan dari yang terbaru)
        $riwayatHariIni = Deposit::where('user_id', $user->id)
            ->whereDate('created_at', now()->today())
            ->latest()
            ->get();

        return view('mobile.home', compact('totalHariIni', 'riwayatHariIni'));
    })->name('mobile.home');
});

// Rute Pemasukan (Income)
Route::get('/incomes/create', [IncomeController::class, 'create'])->name('incomes.create');
Route::post('/incomes', [IncomeController::class, 'store'])->name('incomes.store');

// Rute Pengeluaran
Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

Route::middleware(['auth'])->prefix('mobile')->group(function () {
    // Rute Setoran Baru
    Route::get('/deposits/create', [DepositController::class, 'create'])->name('mobile.deposits.create');
    Route::post('/deposits', [DepositController::class, 'store'])->name('mobile.deposits.store');

    // Rute Slip Gaji Mobile
    Route::get('/salary', [\App\Http\Controllers\Mobile\SalaryController::class, 'show'])->name('mobile.salary.show');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard Utama (Admin/Owner)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Action Approve Setoran
    Route::post('/deposit/{id}/approve', [DashboardController::class, 'approveDeposit'])->name('admin.deposit.approve');

    // MANAJEMEN GAJI (Payroll)
    Route::get('/salaries', [\App\Http\Controllers\Admin\SalaryController::class, 'index'])->name('admin.salaries.index');
    Route::post('/salaries/generate', [\App\Http\Controllers\Admin\SalaryController::class, 'generate'])->name('admin.salaries.generate');
    Route::post('/salaries/{id}/pay', [\App\Http\Controllers\Admin\SalaryController::class, 'pay'])->name('admin.salaries.pay');
    Route::patch('/salaries/user/{userId}/update-base', [\App\Http\Controllers\Admin\SalaryController::class, 'updateBaseSalary'])->name('admin.salaries.update-base');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // ... route customers lainnya ...
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::post('customers/import', [CustomerController::class, 'import'])->name('customers.import');
    // Rute Pelanggan (Resource otomatis buat index, create, store, destroy)
    Route::resource('customers', CustomerController::class);

    // DANA CADANGAN
    Route::get('reports/reserve', [ReportController::class, 'reserve'])->name('reports.reserve');
    Route::post('reports/reserve', [ReportController::class, 'storeUsage'])->name('reports.reserve.store');
    Route::get('reports/reserve/pdf', [ReportController::class, 'exportReservePdf'])->name('reports.reserve.pdf');

    // HISTORY TRANSAKSI
    Route::get('reports/history', [ReportController::class, 'history'])->name('reports.history');
    Route::get('reports/history/pdf', [ReportController::class, 'exportHistoryPdf'])->name('reports.history.pdf');

    // --- FITUR NOTIFIKASI ---
    Route::get('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    })->name('notifications.readAll');

});

// MODULE INVOICE
Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
Route::post('invoices/generate', [InvoiceController::class, 'generate'])->name('invoices.generate');
Route::post('invoices/{id}/paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.paid');
Route::post('invoices/check-isolir', [InvoiceController::class, 'checkIsolir'])->name('invoices.checkIsolir');


require __DIR__.'/auth.php';

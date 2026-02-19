<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        $month = Carbon::now()->format('Y-m');
        
        // Cari gaji bulan ini
        $salary = Salary::where('user_id', $user->id)
            ->where('month', $month)
            ->first();

        // Riwayat gaji 6 bulan terakhir
        $history = Salary::where('user_id', $user->id)
            ->where('month', '<', $month)
            ->latest('month')
            ->limit(6)
            ->get();

        return view('mobile.salaries.show', compact('salary', 'history', 'month'));
    }
}

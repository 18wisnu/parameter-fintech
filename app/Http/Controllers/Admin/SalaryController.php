<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Salary;
use App\Models\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalaryController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', ['teknisi', 'kolektor'])->get();
        $salaries = Salary::with('user')->latest()->paginate(10);
        
        return view('admin.salaries.index', compact('users', 'salaries'));
    }

    public function updateBaseSalary(Request $request, $userId)
    {
        $request->validate([
            'base_salary' => 'required|numeric|min:0',
        ]);

        $user = User::findOrFail($userId);
        $user->update(['base_salary' => $request->base_salary]);

        return redirect()->back()->with('success', 'Gaji pokok berhasil diupdate untuk ' . $user->name);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $month = $request->month;
        $users = User::whereIn('role', ['teknisi', 'kolektor'])->get();
        $bonusPerActivation = 50000; // Contoh: 50rb per aktivasi PPPoE

        foreach ($users as $user) {
            // Hitung Aktivasi PPPoE bulan ini
            $activationsCount = Customer::where('activated_by_id', $user->id)
                ->where('type', 'pppoe')
                ->whereMonth('created_at', Carbon::parse($month)->month)
                ->whereYear('created_at', Carbon::parse($month)->year)
                ->count();

            $activationBonus = $activationsCount * $bonusPerActivation;
            $totalAmount = $user->base_salary + $activationBonus;

            Salary::updateOrCreate(
                ['user_id' => $user->id, 'month' => $month],
                [
                    'base_salary' => $user->base_salary,
                    'activation_bonus' => $activationBonus,
                    'total_amount' => $totalAmount,
                    'status' => 'unpaid',
                ]
            );
        }

        return redirect()->back()->with('success', 'Salary generated for ' . Carbon::parse($month)->format('F Y'));
    }

    public function pay($id)
    {
        $salary = Salary::findOrFail($id);
        $salary->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Gaji berhasil ditandai sebagai TERBAYAR');
    }
}

<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

$output = "";
$voucherAccount = ChartOfAccount::where('code', '4002')->first();
if (!$voucherAccount) {
    $output .= "Voucher account not found\n";
} else {
    $topResellers = Transaction::where('account_id', $voucherAccount->id)
        ->select('customer_id', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as total_count'))
        ->groupBy('customer_id')
        ->orderBy('total_amount', 'desc')
        ->get();

    $output .= "Top Resellers for Vouchers:\n";
    foreach ($topResellers as $reseller) {
        $name = $reseller->customer_id ? Customer::find($reseller->customer_id)->name : 'Unknown';
        $output .= "- $name: Rp " . number_format($reseller->total_amount, 0, ',', '.') . " ($reseller->total_count transactions)\n";
    }

    $trend = Transaction::where('account_id', $voucherAccount->id)
        ->select(DB::raw("DATE_FORMAT(date, '%Y-%m') as month"), DB::raw('SUM(amount) as total_amount'))
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->get();

    $output .= "\nVoucher Trend:\n";
    foreach ($trend as $t) {
        $output .= "- $t->month: Rp " . number_format($t->total_amount, 0, ',', '.') . "\n";
    }
}

file_put_contents('voucher_stats.txt', $output);

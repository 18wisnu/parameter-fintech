<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$isolatedCustomers = App\Models\Customer::where('is_isolated', 1)->get();
$data = [];
foreach ($isolatedCustomers as $c) {
    $inv = App\Models\Invoice::where('customer_id', $c->id)->where('status', 'unpaid')->first();
    $data[] = [
        'customer_name' => $c->name,
        'customer_id_val' => $c->customer_id,
        'has_unpaid_invoice' => $inv ? 'YES' : 'NO',
        'invoice_number' => $inv->invoice_number ?? 'N/A'
    ];
}
file_put_contents('isolated_debug.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Done\n";

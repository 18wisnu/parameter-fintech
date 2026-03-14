<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$invoices = App\Models\Invoice::with('customer')->get();
$data = [];
foreach ($invoices as $i) {
    $data[] = [
        'id' => $i->id,
        'number' => $i->invoice_number,
        'customer' => $i->customer->name ?? 'NULL',
        'status' => $i->status,
        'isolated' => $i->customer->is_isolated ?? 'N/A'
    ];
}
file_put_contents('invoices_debug.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Done\n";

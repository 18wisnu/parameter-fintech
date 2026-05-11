<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProfitSharingSetting;

$data = ProfitSharingSetting::where('key', 'enabled_features')->first();
if ($data) {
    file_put_contents('db_debug.txt', "Key: " . $data->key . "\nValue: " . $data->value . "\nDecoded: " . print_r(json_decode($data->value, true), true));
} else {
    file_put_contents('db_debug.txt', "No enabled_features key found in DB.");
}

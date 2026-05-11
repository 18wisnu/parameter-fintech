<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$settings = DB::table('profit_sharing_settings')->get();
echo "SETTINGS COUNT: " . count($settings) . "\n";
foreach($settings as $s) {
    echo "KEY: {$s->key}, VALUE: {$s->value}\n";
}

$businesses = DB::table('businesses')->get();
echo "BUSINESSES COUNT: " . count($businesses) . "\n";
foreach($businesses as $b) {
    echo "ID: {$b->id}, NAME: {$b->name}, OWNER: {$b->owner_id}\n";
}

$users = DB::table('users')->get();
echo "USERS COUNT: " . count($users) . "\n";
foreach($users as $u) {
    echo "ID: {$u->id}, NAME: {$u->name}, ROLE: {$u->role}\n";
}

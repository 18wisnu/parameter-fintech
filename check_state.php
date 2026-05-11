<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== USERS ===\n";
foreach(DB::table('users')->get() as $u) {
    echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role}\n";
}

echo "\n=== BUSINESSES ===\n";
foreach(DB::table('businesses')->get() as $b) {
    echo "ID: {$b->id} | Name: {$b->name} | Owner ID: {$b->owner_id}\n";
}

echo "\n=== PIVOT business_user ===\n";
foreach(DB::table('business_user')->get() as $p) {
    echo "Bus ID: {$p->business_id} | User ID: {$p->user_id} | Role: {$p->role}\n";
}

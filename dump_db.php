<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

file_put_contents('db_dump.txt', print_r(DB::table('businesses')->get()->toArray(), true));
file_put_contents('db_dump_users.txt', print_r(DB::table('users')->get()->toArray(), true));
file_put_contents('db_dump_pivot.txt', print_r(DB::table('business_user')->get()->toArray(), true));
echo "DONE";

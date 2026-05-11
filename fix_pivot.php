<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Business;
use Illuminate\Support\Facades\DB;

echo "Checking for missing pivot entries...\n";

$businesses = Business::all();
foreach ($businesses as $business) {
    if ($business->owner_id) {
        $exists = DB::table('business_user')
            ->where('business_id', $business->id)
            ->where('user_id', $business->owner_id)
            ->exists();
            
        if (!$exists) {
            DB::table('business_user')->insert([
                'business_id' => $business->id,
                'user_id' => $business->owner_id,
                'role' => 'owner',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "Linked business {$business->id} ({$business->name}) to owner {$business->owner_id}.\n";
        }
    }
}

echo "Done.\n";

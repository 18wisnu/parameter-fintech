<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

$log = "";

try {
    $log .= "Checking businesses table...\n";
    if (!Schema::hasTable('businesses')) {
        DB::statement("CREATE TABLE businesses (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            theme_color VARCHAR(255) DEFAULT '#0369a1',
            enabled_features JSON NULL,
            reserve_percentage DECIMAL(5,2) DEFAULT 10.00,
            owner_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        $log .= "Created businesses table.\n";
    }

    $log .= "Checking business_user table...\n";
    if (!Schema::hasTable('business_user')) {
        DB::statement("CREATE TABLE business_user (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            business_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            role VARCHAR(255) DEFAULT 'member',
            created_at TIMESTAMP NULL,
            updated_at TIMESTAMP NULL,
            FOREIGN KEY (business_id) REFERENCES businesses(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        $log .= "Created business_user table.\n";
    }

    $log .= "Checking current_business_id in users...\n";
    if (!Schema::hasColumn('users', 'current_business_id')) {
        DB::statement("ALTER TABLE users ADD current_business_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE users ADD FOREIGN KEY (current_business_id) REFERENCES businesses(id) ON DELETE SET NULL");
        $log .= "Added current_business_id to users.\n";
    }

    $log .= "Success!\n";
} catch (\Exception $e) {
    $log .= "ERROR: " . $e->getMessage() . "\n";
}

file_put_contents('migration_result.txt', $log);

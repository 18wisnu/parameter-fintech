<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

set_time_limit(0);

echo "Starting manual force migration...\n";

try {
    // 1. Businesses
    if (!Schema::hasTable('businesses')) {
        echo "Creating businesses...\n";
        DB::statement("CREATE TABLE businesses (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, theme_color VARCHAR(255) DEFAULT '#0369a1', enabled_features JSON NULL, reserve_percentage DECIMAL(5,2) DEFAULT 10.00, owner_id BIGINT UNSIGNED NOT NULL, created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)");
        echo "Done.\n";
    }

    // 2. business_user
    if (!Schema::hasTable('business_user')) {
        echo "Creating business_user...\n";
        DB::statement("CREATE TABLE business_user (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, business_id BIGINT UNSIGNED NOT NULL, user_id BIGINT UNSIGNED NOT NULL, role VARCHAR(255) DEFAULT 'member', created_at TIMESTAMP NULL, updated_at TIMESTAMP NULL)");
        echo "Done.\n";
    }

    // 3. users table
    if (!Schema::hasColumn('users', 'current_business_id')) {
        echo "Updating users table...\n";
        DB::statement("ALTER TABLE users ADD current_business_id BIGINT UNSIGNED NULL");
        echo "Done.\n";
    }

    // 4. Other tables
    $tables = [
        'profit_sharing_stakeholders', 'customers', 'transactions', 
        'deposits', 'profit_distributions', 'salaries', 'incomes', 'expenses'
    ];

    foreach ($tables as $t) {
        if (!Schema::hasColumn($t, 'business_id')) {
            echo "Updating $t...\n";
            DB::statement("ALTER TABLE $t ADD business_id BIGINT UNSIGNED NULL");
            echo "Done.\n";
        }
    }

    echo "FINISH! All tables updated.\n";
} catch (\Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
}

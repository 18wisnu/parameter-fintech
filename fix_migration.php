<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

try {
    echo "Starting manual migration...\n";
    
    if (!Schema::hasTable('businesses')) {
        echo "Creating businesses table...\n";
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('theme_color')->default('#0369a1');
            $table->json('enabled_features')->nullable();
            $table->decimal('reserve_percentage', 5, 2)->default(10.00);
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
        echo "Created businesses table.\n";
    }

    if (!Schema::hasTable('business_user')) {
        echo "Creating business_user table...\n";
        Schema::create('business_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member');
            $table->timestamps();
        });
        echo "Created business_user table.\n";
    }

    if (!Schema::hasColumn('users', 'current_business_id')) {
        echo "Adding current_business_id to users...\n";
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_business_id')->nullable()->constrained('businesses')->onDelete('set null');
        });
        echo "Updated users table.\n";
    }

    echo "Migration completed successfully!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

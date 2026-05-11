<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Create businesses table
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('theme_color')->default('#0369a1');
            $table->json('enabled_features')->nullable();
            $table->decimal('reserve_percentage', 5, 2)->default(10.00);
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 2. Create business_user pivot table
        Schema::create('business_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // owner, admin, staff
            $table->timestamps();
        });

        // 3. Add current_business_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('current_business_id')->nullable()->constrained('businesses')->onDelete('set null');
        });

        // 4. Update existing settings table to link to businesses (Optional, or just use business table columns)
        // To avoid breaking existing code too much, let's add business_id to profit_sharing_settings
        Schema::table('profit_sharing_settings', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->constrained()->onDelete('cascade');
            $table->dropUnique('profit_sharing_settings_key_unique');
            $table->unique(['key', 'business_id']);
        });

        // 5. Add business_id to other main tables
        $tables = [
            'profit_sharing_stakeholders', 'customers', 'transactions', 
            'deposits', 'profit_distributions', 'salaries', 'incomes', 'expenses'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('business_id')->nullable()->constrained()->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_business_id');
        });

        Schema::dropIfExists('business_user');
        Schema::dropIfExists('businesses');
        
        // Reverse other changes if needed, but usually down() is for full rollback
    }
};

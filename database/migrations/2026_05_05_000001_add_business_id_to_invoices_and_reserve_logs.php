<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'business_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('business_id')->nullable()->constrained('businesses')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('reserve_fund_logs') && !Schema::hasColumn('reserve_fund_logs', 'business_id')) {
            Schema::table('reserve_fund_logs', function (Blueprint $table) {
                $table->foreignId('business_id')->nullable()->constrained('businesses')->onDelete('cascade');
            });
        }

        // Cari ID bisnis utama (default bisnis)
        $defaultBusiness = DB::table('businesses')->where('id', 2)->first() 
            ?: DB::table('businesses')->first();
            
        $defaultBusinessId = $defaultBusiness ? $defaultBusiness->id : null;

        if ($defaultBusinessId) {
            // Sinkronkan invoices dari customers jika ada
            try {
                DB::statement("UPDATE invoices i JOIN customers c ON i.customer_id = c.id SET i.business_id = c.business_id WHERE i.business_id IS NULL AND c.business_id IS NOT NULL");
            } catch (\Exception $e) {}

            // Assign data yang masih NULL ke default business
            $tables = [
                'customers', 'invoices', 'transactions', 'deposits', 
                'salaries', 'incomes', 'expenses', 'reserve_fund_logs',
                'profit_distributions', 'profit_sharing_stakeholders'
            ];

            foreach ($tables as $t) {
                if (Schema::hasTable($t) && Schema::hasColumn($t, 'business_id')) {
                    DB::table($t)->whereNull('business_id')->update(['business_id' => $defaultBusinessId]);
                }
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'business_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropConstrainedForeignId('business_id');
            });
        }

        if (Schema::hasTable('reserve_fund_logs') && Schema::hasColumn('reserve_fund_logs', 'business_id')) {
            Schema::table('reserve_fund_logs', function (Blueprint $table) {
                $table->dropConstrainedForeignId('business_id');
            });
        }
    }
};

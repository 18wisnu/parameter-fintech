<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('base_salary', 15, 2)->default(0)->after('is_active');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('activated_by_id')->nullable()->after('is_isolated')->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['activated_by_id']);
            $table->dropColumn('activated_by_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('base_salary');
        });
    }
};

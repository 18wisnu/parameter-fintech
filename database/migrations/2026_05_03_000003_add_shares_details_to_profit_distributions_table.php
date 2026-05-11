<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('profit_distributions', function (Blueprint $table) {
            $table->json('shares_details')->nullable()->after('share_group_b');
        });
    }

    public function down()
    {
        Schema::table('profit_distributions', function (Blueprint $table) {
            $table->dropColumn('shares_details');
        });
    }
};

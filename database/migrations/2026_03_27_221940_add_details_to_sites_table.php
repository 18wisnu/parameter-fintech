<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // Kita tambahkan kolom-kolom yang diminta oleh form UI
            $table->string('business_name')->nullable()->after('name');
            $table->text('location')->nullable()->after('business_name');
            $table->string('pon_power')->nullable()->after('ip_address');
            $table->string('latitude')->nullable()->after('pon_power');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['business_name', 'location', 'pon_power', 'latitude', 'longitude']);
        });
    }
};
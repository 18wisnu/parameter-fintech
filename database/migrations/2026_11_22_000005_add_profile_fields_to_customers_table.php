<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profil pelanggan mengikuti struktur data server jaringan:
 * email, koordinat, catatan, reseller/outlet, dan ringkasan layanan utama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->text('notes')->nullable()->after('longitude');
            $table->string('entity_name')->nullable()->after('notes');      // reseller / outlet pemilik
            $table->string('product_name')->nullable()->after('package_fee'); // nama paket
            $table->string('bandwidth')->nullable()->after('product_name');
            $table->string('billing_type', 20)->nullable()->after('bandwidth');  // prepaid / postpaid
            $table->string('billing_cycle', 20)->nullable()->after('billing_type'); // monthly, quarterly, ...
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['email', 'latitude', 'longitude', 'notes', 'entity_name', 'product_name', 'bandwidth', 'billing_type', 'billing_cycle']);
        });
    }
};

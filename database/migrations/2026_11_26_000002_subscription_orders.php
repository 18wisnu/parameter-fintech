<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pembayaran langganan menjadi "pesanan" (checkout): punya nomor order, metode bayar
 * & batas waktu bayar. Siap untuk payment gateway (kolom gateway_*).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('reference')->nullable()->unique()->after('id');
            $table->string('method')->default('manual_transfer')->after('amount');
            $table->string('gateway_ref')->nullable()->after('method');
            $table->timestamp('expires_at')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropUnique(['reference']);
            $table->dropColumn(['reference', 'method', 'gateway_ref', 'expires_at', 'paid_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Status perpanjangan layanan di server RADIUS (Mixradius) setelah tagihan lunas.
 * renewal_status: null (tidak perlu), pending, success, failed
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('renewal_status')->nullable()->after('status');
            $table->text('renewal_error')->nullable()->after('renewal_status');
            $table->unsignedTinyInteger('renewal_attempts')->default(0)->after('renewal_error');
            $table->date('renewal_expiry_before')->nullable()->after('renewal_attempts');
            $table->date('renewal_new_expiry')->nullable()->after('renewal_expiry_before');
            $table->string('renewal_remote_invoice')->nullable()->after('renewal_new_expiry');
            $table->string('renewal_payment_method')->nullable()->after('renewal_remote_invoice');
            $table->string('renewal_note')->nullable()->after('renewal_payment_method');
            $table->timestamp('renewed_at')->nullable()->after('renewal_note');

            $table->index('renewal_status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['renewal_status']);
            $table->dropColumn([
                'renewal_status', 'renewal_error', 'renewal_attempts', 'renewal_expiry_before',
                'renewal_new_expiry', 'renewal_remote_invoice', 'renewal_payment_method',
                'renewal_note', 'renewed_at',
            ]);
        });
    }
};

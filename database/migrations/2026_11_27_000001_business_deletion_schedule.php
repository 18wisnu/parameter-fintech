<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Hapus usaha dengan masa tunggu: usaha dikunci, lalu dihapus permanen setelah tanggal ini. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->timestamp('deletion_scheduled_at')->nullable()->after('trial_ends_at');
            $table->foreignId('deletion_requested_by')->nullable()->after('deletion_scheduled_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deletion_requested_by');
            $table->dropColumn('deletion_scheduled_at');
        });
    }
};

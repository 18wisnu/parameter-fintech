<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kontrol keuangan:
 *  - cash_closings   : tutup kas harian per kolektor (uang fisik vs tercatat)
 *  - period_closings : tutup buku bulanan (transaksi bulan itu dikunci)
 *  - activity_logs   : per tenant + jejak perubahan data (audit)
 *  - customers       : kolektor penanggung jawab, diskon bulanan, biaya pasang, tanggal mulai (prorata)
 *  - invoices        : rincian tagihan (paket, prorata, diskon, biaya pasang)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();      // kolektor
            $table->date('date');
            $table->unsignedInteger('deposit_count')->default(0);
            $table->decimal('expected_amount', 15, 2)->default(0);              // total setoran tercatat
            $table->decimal('actual_amount', 15, 2)->default(0);                // uang fisik diterima
            $table->decimal('difference', 15, 2)->default(0);                   // fisik − tercatat
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['business_id', 'user_id', 'date']);
        });

        Schema::create('period_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->date('period');                                             // tanggal 1 bulan yang ditutup
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('expense', 15, 2)->default(0);
            $table->decimal('net', 15, 2)->default(0);
            $table->decimal('cash_balance', 15, 2)->default(0);
            $table->json('snapshot')->nullable();                               // rincian per akun saat ditutup
            $table->text('notes')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['business_id', 'period']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->string('event', 20)->nullable()->after('activity');         // created / updated / deleted
            $table->index(['business_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('collector_id')->nullable()->after('activated_by_id')->constrained('users')->nullOnDelete();
            $table->decimal('discount', 15, 2)->default(0)->after('package_fee');          // potongan per bulan
            $table->decimal('installation_fee', 15, 2)->default(0)->after('discount');     // biaya pasang (sekali)
            $table->boolean('installation_billed')->default(false)->after('installation_fee');
            $table->date('start_date')->nullable()->after('installation_billed');          // mulai berlangganan
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->json('details')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn('details'));

        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collector_id');
            $table->dropColumn(['discount', 'installation_fee', 'installation_billed', 'start_date']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'created_at']);
            $table->dropIndex(['model_type', 'model_id']);
            $table->dropConstrainedForeignId('business_id');
            $table->dropColumn('event');
        });

        Schema::dropIfExists('period_closings');
        Schema::dropIfExists('cash_closings');
    }
};

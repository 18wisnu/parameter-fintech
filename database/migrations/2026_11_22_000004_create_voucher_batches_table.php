<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Arsip batch voucher yang digenerate dari aplikasi ini.
 * Password voucher hanya dikembalikan server SEKALI saat generate, jadi disimpan
 * (terenkripsi) di sini agar batch bisa dicetak ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('batch_code')->index();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->unsignedInteger('quantity');
            $table->string('prefix')->nullable();
            $table->string('login_method')->nullable();
            $table->string('restriction_name')->nullable();
            $table->longText('vouchers'); // encrypted JSON [{code, password}]
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_batches');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Undangan bergabung ke usaha untuk email yang SUDAH punya akun.
 * Akun baru tergabung setelah pemilik email menekan "Terima".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('role', 20);
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending'); // pending | accepted | declined | cancelled
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'status']);
            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_invitations');
    }
};

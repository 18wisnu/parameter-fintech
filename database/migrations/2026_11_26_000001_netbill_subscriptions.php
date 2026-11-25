<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * NetBill SaaS: paket langganan, langganan per usaha (tenant), pembayaran langganan,
 * pengaturan platform & penanda pengelola platform (superadmin).
 *
 * Usaha yang sudah ada sebelum migrasi ini tidak diberi tanggal berakhir (tanpa batas),
 * jadi tidak ada yang tiba-tiba terkunci.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);           // per bulan
            $table->unsignedInteger('max_customers')->nullable();  // null = tanpa batas
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('owner_id')->constrained('plans')->nullOnDelete();
            $table->date('trial_ends_at')->nullable()->after('plan_id');
            $table->date('subscription_ends_at')->nullable()->after('trial_ends_at');
            $table->timestamp('suspended_at')->nullable()->after('subscription_ends_at');
            $table->string('suspend_reason')->nullable()->after('suspended_at');
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->unsignedSmallInteger('months');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending | confirmed | rejected
            $table->string('proof_image')->nullable();
            $table->string('notes')->nullable();
            $table->string('admin_notes')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_superadmin')->default(false)->after('role');
        });

        $now = now();
        DB::table('plans')->insert([
            ['name' => 'Starter', 'price' => 50000, 'max_customers' => 100, 'description' => 'Untuk RT/RW Net yang baru mulai', 'sort' => 1, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pro', 'price' => 100000, 'max_customers' => 500, 'description' => 'Untuk usaha yang sedang tumbuh', 'sort' => 2, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bisnis', 'price' => 200000, 'max_customers' => null, 'description' => 'Pelanggan tanpa batas', 'sort' => 3, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_superadmin'));
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('subscription_payments');
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn(['trial_ends_at', 'subscription_ends_at', 'suspended_at', 'suspend_reason']);
        });
        Schema::dropIfExists('plans');
    }
};

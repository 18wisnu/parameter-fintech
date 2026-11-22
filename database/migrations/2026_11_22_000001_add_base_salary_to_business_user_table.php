<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gaji pokok disimpan per tenant (satu akun bisa punya gaji berbeda di tiap tenant).
 * users.base_salary dibiarkan sebagai data lama dan tidak dipakai lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_user', function (Blueprint $table) {
            $table->decimal('base_salary', 15, 2)->default(0)->after('role');
        });

        // Salin nilai lama ke setiap tenant tempat user terdaftar
        if (Schema::hasColumn('users', 'base_salary')) {
            DB::table('users')->where('base_salary', '>', 0)->orderBy('id')
                ->each(function ($user) {
                    DB::table('business_user')
                        ->where('user_id', $user->id)
                        ->update(['base_salary' => $user->base_salary]);
                });
        }

        // Cegah satu user terdaftar dua kali di tenant yang sama
        $duplicates = DB::table('business_user')
            ->select('business_id', 'user_id', DB::raw('MIN(id) as keep_id'))
            ->groupBy('business_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
        foreach ($duplicates as $dup) {
            DB::table('business_user')
                ->where('business_id', $dup->business_id)
                ->where('user_id', $dup->user_id)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }

        Schema::table('business_user', function (Blueprint $table) {
            $table->unique(['business_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('business_user', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'user_id']);
            $table->dropColumn('base_salary');
        });
    }
};

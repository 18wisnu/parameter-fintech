<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Warna tema bawaan ikut warna merek NetBill (biru-teal).
 * Hanya usaha yang masih memakai warna bawaan lama yang diganti; warna pilihan sendiri dibiarkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('theme_color')->default('#117e88')->change();
        });
        DB::table('businesses')->whereNull('theme_color')->orWhereIn(DB::raw('LOWER(theme_color)'), ['#0369a1', ''])
            ->update(['theme_color' => '#117e88']);
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('theme_color')->default('#0369a1')->change();
        });
        DB::table('businesses')->where('theme_color', '#117e88')->update(['theme_color' => '#0369a1']);
    }
};

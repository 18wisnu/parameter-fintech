<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('transaction_date');
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->string('category')->nullable(); // Misal: Operasional, Alat, dll
            $table->timestamps();
        });
    }
};

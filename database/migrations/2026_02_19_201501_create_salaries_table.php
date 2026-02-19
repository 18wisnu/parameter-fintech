<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('month'); // Format: YYYY-MM
            $table->decimal('base_salary', 15, 2);
            $table->decimal('activation_bonus', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('salaries');
    }
};

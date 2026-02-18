<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            
            // Siapa yang setor? (Teknisi/Kolektor)
            $table->foreignId('user_id')->constrained('users');
            
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable(); // Misal: "Setoran Voucher Yudi Cell"
            $table->string('proof_image')->nullable(); // Foto Bukti Transfer/Uang Tunai
            
            // Status Approval
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Siapa yang terima? (Admin/Owner)
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deposits');
    }
};

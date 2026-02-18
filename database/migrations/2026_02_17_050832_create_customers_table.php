<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama Pelanggan / Reseller
            $table->string('code')->nullable()->unique(); // Kode Unik (misal: CUST-001)
            
            // Tipe Pelanggan
            $table->enum('type', ['pppoe', 'reseller', 'hotspot', 'general'])
                ->default('pppoe');
                
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            
            // ID dari Mixradius (Persiapan Integrasi API)
            $table->string('mixradius_id')->nullable();
            
            $table->enum('status', ['active', 'inactive', 'isolated'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
};

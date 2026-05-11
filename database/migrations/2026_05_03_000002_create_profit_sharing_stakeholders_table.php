<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('profit_sharing_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('percentage', 5, 2);
            $table->timestamps();
        });

        // Default stakeholders as per current hardcoded logic
        DB::table('profit_sharing_stakeholders')->insert([
            [
                'name' => 'Junaidi & Eka',
                'percentage' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bagus',
                'percentage' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('profit_sharing_stakeholders');
    }
};

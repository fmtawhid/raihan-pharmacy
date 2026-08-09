<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void
    {
        Schema::create('purchasers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone', 50)->nullable();
            $t->timestamps();
        });

        DB::table('purchasers')->insert([
            'id'   => 1,
            'name' => 'SELF / MANUFACTURER',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('purchasers');
    }
};

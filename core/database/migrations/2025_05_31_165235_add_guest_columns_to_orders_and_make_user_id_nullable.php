<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // 1️⃣ make user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // 2️⃣ add guest columns
            $table->string('guest_name')->nullable();
            $table->string('guest_phone')->nullable();
            $table->text('guest_address')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['guest_name', 'guest_phone', 'guest_address']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};

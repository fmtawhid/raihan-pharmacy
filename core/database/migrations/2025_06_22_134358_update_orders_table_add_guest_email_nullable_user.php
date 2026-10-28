<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateOrdersTableAddGuestEmailNullableUser extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add new guest_email column
            $table->string('guest_email')->nullable()->after('guest_name');

            // Make user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Drop guest_email column
            $table->dropColumn('guest_email');

            // Revert user_id to NOT NULL (you can change behavior here if needed)
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
}

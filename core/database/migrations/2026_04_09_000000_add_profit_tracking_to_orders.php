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
        // Add profit tracking columns to order_details table
        Schema::table('order_details', function (Blueprint $table) {
            $table->double('purchase_price')->nullable()->default(0)->after('price');
            $table->unsignedBigInteger('batch_id')->nullable()->after('purchase_price');
            $table->double('profit')->nullable()->after('batch_id');
        });

        // Add cost and profit tracking to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->double('cost_amount')->nullable()->default(0)->after('total_amount');
            $table->double('profit')->nullable()->default(0)->after('cost_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'batch_id', 'profit']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cost_amount', 'profit']);
        });
    }
};

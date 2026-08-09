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
        Schema::table('products', function (Blueprint $t) {
            $t->string('barcode_path')->nullable()->after('sku');
        });

        Schema::table('product_variants', function (Blueprint $t) {
            $t->string('barcode_path')->nullable()->after('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products_and_variants', function (Blueprint $table) {
            //
        });
    }
};

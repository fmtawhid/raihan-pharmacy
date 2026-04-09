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
        Schema::table('product_variants', function (Blueprint $table) {
            // Add purchase_price to track the cost of variant
            if (!Schema::hasColumn('product_variants', 'purchase_price')) {
                $table->decimal('purchase_price', 12, 2)->nullable()->default(0)->after('regular_price')->comment('Purchase price per unit for variant');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            if (Schema::hasColumn('product_variants', 'purchase_price')) {
                $table->dropColumn('purchase_price');
            }
        });
    }
};

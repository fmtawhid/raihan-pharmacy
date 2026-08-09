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
        Schema::create('product_batches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->foreignId('variant_id')->nullable()
              ->constrained('product_variants')->cascadeOnDelete();
            $t->string('batch_no', 40);
            $t->foreignId('purchaser_id')->default(1)
              ->constrained('purchasers');
            $t->decimal('purchase_price', 28, 8)->nullable();
            $t->unsignedInteger('qty_received')->default(0);
            $t->unsignedInteger('qty_sold')->default(0);
            $t->date('purchased_at')->nullable();
            $t->timestamps();

            $t->unique(['product_id', 'variant_id', 'batch_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};

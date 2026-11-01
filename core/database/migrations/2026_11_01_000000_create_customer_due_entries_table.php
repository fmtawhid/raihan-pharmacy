<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_due_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->decimal('amount', 18, 2);
            $table->decimal('balance', 18, 2);
            $table->string('description', 255);
            $table->timestamps();

            $table->index(['user_id', 'balance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_due_entries');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_follow_up_summaries', function (Blueprint $t) {
            $t->id();
            $t->string('month', 7);           // "2025-06"
            $t->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('contacted_total')->default(0);
            $t->unsignedInteger('potential_total')->default(0);
            $t->timestamps();

            $t->unique(['month', 'admin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_follow_up_summaries');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('recipient_number', 32);
            $table->text('message');
            $table->string('status', 16)->index();
            $table->string('source', 32)->default('sms_panel')->index();
            $table->text('response_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};

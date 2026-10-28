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
        Schema::table('users', function (Blueprint $table) {
            $table->string('division_id')->nullable()->after('state');
            $table->string('district_id')->nullable()->after('division_id');
            $table->string('area_name')->nullable()->after('district_id');
            $table->string('postcode')->nullable()->after('area_name');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('division_id')->nullable()->after('guest_address');
            $table->string('district_id')->nullable()->after('division_id');
            $table->string('area_name')->nullable()->after('district_id');
            $table->string('postcode')->nullable()->after('area_name');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchasers', function (Blueprint $t) {
            // make both columns NOT NULL with defaults
            $t->timestamp('created_at')
              ->default(DB::raw('CURRENT_TIMESTAMP'))
              ->change();

            $t->timestamp('updated_at')
              ->default(DB::raw('CURRENT_TIMESTAMP'))
              ->useCurrentOnUpdate()
              ->change();
        });

        // patch any existing NULL rows
        DB::table('purchasers')
          ->whereNull('created_at')
          ->orWhereNull('updated_at')
          ->update([
              'created_at' => now(),
              'updated_at' => now(),
          ]);
    }

    public function down(): void
    {
        Schema::table('purchasers', function (Blueprint $t) {
            // back to nullable / no defaults
            $t->timestamp('created_at')->nullable()->default(null)->change();
            $t->timestamp('updated_at')->nullable()->default(null)->change();
        });
    }
};

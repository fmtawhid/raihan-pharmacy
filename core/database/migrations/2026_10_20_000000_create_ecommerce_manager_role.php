<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        $roleExists = DB::table('roles')
            ->where('guard_name', 'admin')
            ->whereIn('name', ['ecommerce_manager', 'Ecommerce Manager'])
            ->exists();
        if ($roleExists) {
            return;
        }

        $now = now();
        DB::table('roles')->updateOrInsert(
            ['name' => 'ecommerce_manager', 'guard_name' => 'admin'],
            ['created_at' => $now, 'updated_at' => $now]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')
                ->where('name', 'ecommerce_manager')
                ->where('guard_name', 'admin')
                ->delete();
        }
    }
};

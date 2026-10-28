<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 32)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('branches')->insert([
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $mainBranchId = DB::table('branches')->where('code', 'MAIN')->value('id');

        foreach (['admins', 'users', 'customers', 'purchasers', 'product_batches', 'orders', 'deposits', 'stock_logs'] as $tableName) {
            if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'branch_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->unsignedBigInteger('branch_id')->nullable()->index($tableName . '_branch_id_index');
            });
        }

        foreach (['customers', 'purchasers', 'product_batches', 'stock_logs'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'branch_id')) {
                DB::table($tableName)->whereNull('branch_id')->update(['branch_id' => $mainBranchId]);
            }
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'order_number')) {
            DB::table('orders')
                ->where('order_number', 'like', 'POS-%')
                ->whereNull('branch_id')
                ->update(['branch_id' => $mainBranchId]);
        }

        if (Schema::hasTable('users') && Schema::hasTable('orders') && Schema::hasColumn('orders', 'order_number')) {
            $posCustomerIds = DB::table('orders')
                ->where('order_number', 'like', 'POS-%')
                ->whereNotNull('user_id')
                ->select('user_id');
            DB::table('users')->whereIn('id', $posCustomerIds)->whereNull('branch_id')
                ->update(['branch_id' => $mainBranchId]);
        }

        if (Schema::hasTable('deposits') && Schema::hasTable('orders')) {
            DB::table('deposits')->join('orders', 'deposits.order_id', '=', 'orders.id')
                ->whereNull('deposits.branch_id')
                ->whereNotNull('orders.branch_id')
                ->update(['deposits.branch_id' => DB::raw('orders.branch_id')]);
        }

        if (Schema::hasTable('product_batches')) {
            Schema::table('product_batches', function (Blueprint $table) {
                $table->dropUnique('product_batches_product_id_variant_id_batch_no_unique');
                $table->unique(['product_id', 'variant_id', 'branch_id', 'batch_no']);
            });
        }

        if (Schema::hasTable('roles') && !DB::table('roles')->where('name', 'branch_manager')->where('guard_name', 'admin')->exists()) {
            DB::table('roles')->insert([
                'name' => 'branch_manager',
                'guard_name' => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('product_batches')) {
            Schema::table('product_batches', function (Blueprint $table) {
                $table->dropUnique('product_batches_product_id_variant_id_branch_id_batch_no_unique');
                $table->unique(['product_id', 'variant_id', 'batch_no']);
            });
        }

        foreach (['admins', 'users', 'customers', 'purchasers', 'product_batches', 'orders', 'deposits', 'stock_logs'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropIndex($tableName . '_branch_id_index');
                    $table->dropColumn('branch_id');
                });
            }
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'branch_manager')->where('guard_name', 'admin')->delete();
        }
        Schema::dropIfExists('branches');
    }
};
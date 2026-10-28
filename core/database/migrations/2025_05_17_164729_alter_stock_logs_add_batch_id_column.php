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
        Schema::table('stock_logs', function (Blueprint $t) {
            // step A – add nullable FK column
            $t->foreignId('batch_ref')
              ->nullable()
              ->after('product_variant_id');
        });

        /** step B – lift distinct old strings into real batch rows */
        $oldLogs = DB::table('stock_logs')
            ->whereNotNull('batch_id')          // old varchar column
            ->where('batch_id','!=','')
            ->get(['id','product_id','product_variant_id','batch_id']);

        $cache = [];   // key = product|variant|batch_no -> batch PK

        foreach ($oldLogs as $log) {
            $key = $log->product_id.'|'.$log->product_variant_id.'|'.$log->batch_id;

            if (!isset($cache[$key])) {
                $batchPK = DB::table('product_batches')->insertGetId([
                    'product_id'   => $log->product_id,
                    'variant_id'   => $log->product_variant_id ?: null,
                    'batch_no'     => $log->batch_id,
                    'purchaser_id' => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                $cache[$key] = $batchPK;
            }

            DB::table('stock_logs')
              ->where('id', $log->id)
              ->update(['batch_ref' => $cache[$key]]);
        }

        /** step C – drop old column & rename new one */
        Schema::table('stock_logs', function (Blueprint $t) {
            $t->dropColumn('batch_id');          // old varchar
            $t->renameColumn('batch_ref','batch_id');
            $t->foreign('batch_id')
              ->references('id')->on('product_batches');
        });
    }

    public function down(): void
    {
        Schema::table('stock_logs', function (Blueprint $t) {
            $t->dropForeign(['batch_id']);
            $t->string('batch_old', 30)->nullable();
            $t->renameColumn('batch_id','batch_ref');
            $t->dropColumn('batch_ref');
            $t->renameColumn('batch_old','batch_id');
        });
    }
};

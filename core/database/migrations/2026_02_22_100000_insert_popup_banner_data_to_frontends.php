<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('frontends')->where('data_keys', 'popup_banner.data')->first();

        if (!$exists) {
            DB::table('frontends')->insert([
                'data_keys'  => 'popup_banner.data',
                'data_values' => json_encode([
                    'status'      => 0,
                    'title'       => 'Welcome to Our Store!',
                    'description' => 'Check out our latest products and offers.',
                    'image'       => '',
                    'btn_text'    => '',
                    'btn_url'     => '',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('frontends')->where('data_keys', 'popup_banner.data')->delete();
    }
};

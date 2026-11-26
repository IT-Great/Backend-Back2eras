<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'source')) {
                $table->string('source', 20)->default('regular')->after('brand_slug');
            }
        });

        // Buat composite unique baru lebih dulu agar FK user_id tetap punya index pendukung di MySQL
        $indexes = collect(DB::select('SHOW INDEX FROM vouchers'))->pluck('Key_name')->unique()->all();

        if (!in_array('vouchers_user_brand_source_unique', $indexes, true)) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->unique(['user_id', 'brand_slug', 'source'], 'vouchers_user_brand_source_unique');
            });
        }

        if (in_array('vouchers_user_id_brand_slug_unique', $indexes, true)) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropUnique('vouchers_user_id_brand_slug_unique');
            });
        }

        // Tandai voucher lama milik akun role = 'talkshow' sebagai source = 'talkshow'
        DB::table('vouchers')
            ->whereIn('user_id', function ($q) {
                $q->select('id')->from('users')->where('role', 'talkshow');
            })
            ->update(['source' => 'talkshow']);
    }

    public function down(): void
    {
        $indexes = collect(DB::select('SHOW INDEX FROM vouchers'))->pluck('Key_name')->unique()->all();

        if (!in_array('vouchers_user_id_brand_slug_unique', $indexes, true)) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->unique(['user_id', 'brand_slug'], 'vouchers_user_id_brand_slug_unique');
            });
        }

        if (in_array('vouchers_user_brand_source_unique', $indexes, true)) {
            Schema::table('vouchers', function (Blueprint $table) {
                $table->dropUnique('vouchers_user_brand_source_unique');
            });
        }

        Schema::table('vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('vouchers', 'source')) {
                $table->dropColumn('source');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talkshow_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('talkshow_registrations', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('talkshow_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('talkshow_registrations', 'approved_rank')) {
                $table->unsignedTinyInteger('approved_rank')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('talkshow_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('talkshow_registrations', 'approved_rank')) {
                $table->dropColumn('approved_rank');
            }
            if (Schema::hasColumn('talkshow_registrations', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });
    }
};

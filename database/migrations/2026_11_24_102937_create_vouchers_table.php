<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('brand_slug'); // back2eras, solher, gycora, glamoire, wdp, kecilung, malaijia
            $table->string('brand_name');
            $table->string('code')->unique(); // Kode unik berbeda tiap user
            $table->boolean('is_used')->default(false); // Sekali pakai
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            // 1 user hanya bisa klaim 1 kali per brand
            $table->unique(['user_id', 'brand_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};

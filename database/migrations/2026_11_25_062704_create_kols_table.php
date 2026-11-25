<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('address');
            $table->string('icon'); // Niche / Kategori / Persona KOL
            $table->string('email');
            $table->string('whatsapp');
            $table->string('social_media_link', 500)->nullable(); // Opsional; syarat klaim voucher
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kols');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talkshow_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talkshow_id')->constrained('talkshows')->cascadeOnDelete();
            $table->string('name'); // Nama lengkap pendaftar
            $table->string('email'); // Email pendaftar
            $table->string('phone'); // Nomor WhatsApp / HP
            $table->string('institution')->nullable(); // Instansi / Komunitas (opsional)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talkshow_registrations');
    }
};

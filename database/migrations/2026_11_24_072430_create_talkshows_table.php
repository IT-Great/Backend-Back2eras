<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talkshows', function (Blueprint $table) {
            $table->id();
            $table->string('poster'); // Path file gambar poster
            $table->string('speaker_name'); // Nama narasumber
            $table->string('icon'); // Icon / Jabatan narasumber
            $table->string('theme'); // Tema talkshow
            $table->dateTime('schedule'); // Waktu pelaksanaan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talkshows');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_harga', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komoditas_id')->constrained('komoditas')->cascadeOnDelete();
            $table->date('tanggal');
            $table->decimal('harga', 12, 2);
            $table->string('sumber', 120)->nullable();
            $table->timestamps();

            $table->unique(['komoditas_id', 'tanggal']); // dasar upsert, cegah duplikat
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_harga');
    }
};
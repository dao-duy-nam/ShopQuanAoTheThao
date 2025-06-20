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
        Schema::create('gia_tri_thuoc_tinhs', function (Blueprint $table) {
            $table->id();
            $table->string('gia_tri'); // Ví dụ: Đỏ, Xanh, M, L
            $table->foreignId('thuoc_tinh_id')->constrained('thuoc_tinhs')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gia_tri_thuoc_tinhs');
    }
};

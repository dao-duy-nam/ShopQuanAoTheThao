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
        Schema::create('chi_tiet_don_hangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('don_hang_id')->constrained('don_hangs')->cascadeOnDelete();
            $table->foreignId('san_pham_id')->constrained('san_phams')->cascadeOnDelete();
            $table->foreignId('bien_the_id')->constrained('bien_thes')->cascadeOnDelete();
            $table->string('mau_sac')->nullable();       // ví dụ: đỏ, đen, xanh
            $table->string('kich_thuoc')->nullable();    // ví dụ: S, M, L
            $table->integer('so_luong');
            $table->decimal('don_gia', 10, 2);
            $table->decimal('tong_tien', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chi_tiet_don_hangs');
    }
};

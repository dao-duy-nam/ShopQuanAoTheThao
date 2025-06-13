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
        Schema::create('bien_thes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('san_pham_id')->constrained('san_phams')->cascadeOnDelete();
            $table->foreignId('kich_co_id')->constrained('kich_cos');
            $table->foreignId('mau_sac_id')->constrained('mau_sacs');
            $table->integer('so_luong');
            $table->json('hinh_anh')->nullable();
            $table->integer('so_luong_da_ban')->default(0);
            $table->decimal('gia', 10, 2);
            $table->decimal('gia_khuyen_mai', 10, 2)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bien_thes');
    }
};

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
        Schema::create('ma_giam_gia_nguoi_dung', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ma_giam_gia_id')->constrained('ma_giam_gias')->onDelete('cascade');
            $table->foreignId('nguoi_dung_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('so_lan_da_dung')->default(0);
            $table->timestamps();

            $table->unique(['ma_giam_gia_id', 'nguoi_dung_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ma_giam_gia_nguoi_dung');
    }
};

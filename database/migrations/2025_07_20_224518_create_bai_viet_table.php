<?php

// database/migrations/2025_07_20_000000_create_bai_viet_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bai_viet', function (Blueprint $table) {
            $table->id();
            $table->string('tieu_de');
            $table->longText('mo_ta_ngan')->nullable();       
            $table->longText('noi_dung')->nullable();     
            $table->string('anh_dai_dien')->nullable();   
            $table->enum('trang_thai', ['an', 'hien'])->default('hien');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bai_viet');
    }
};

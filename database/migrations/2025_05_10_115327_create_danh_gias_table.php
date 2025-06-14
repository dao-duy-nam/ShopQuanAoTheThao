<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('danh_gias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('san_pham_id')->nullable()->constrained('san_phams')->onDelete('cascade');
            $table->foreignId('bien_the_id')->nullable()->constrained('bien_thes')->onDelete('cascade');
            $table->text('noi_dung');
            $table->unsignedTinyInteger('so_sao');
            $table->string('hinh_anh')->nullable();
             $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danh_gias');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

public function up(): void
{
    Schema::table('san_phams', function (Blueprint $table) {
        $table->dropColumn(['gia', 'gia_khuyen_mai']);
        
    });
}

public function down(): void
{
    Schema::table('san_phams', function (Blueprint $table) {
        $table->decimal('gia', 12, 2)->nullable();
        $table->decimal('gia_khuyen_mai', 12, 2)->nullable();
        
    });
}

};

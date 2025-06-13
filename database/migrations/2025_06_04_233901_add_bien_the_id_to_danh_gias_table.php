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
        Schema::table('danh_gias', function (Blueprint $table) {
            $table->foreignId('bien_the_id')
                ->nullable()
                ->constrained('bien_thes')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('danh_gias', function (Blueprint $table) {
            $table->dropForeign(['bien_the_id']);
            $table->dropColumn('bien_the_id');
        });
    }
};

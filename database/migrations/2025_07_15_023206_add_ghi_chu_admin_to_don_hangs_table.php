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
    Schema::table('don_hangs', function (Blueprint $table) {
        $table->text('ghi_chu_admin')->nullable()->after('payment_link');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('don_hangs', function (Blueprint $table) {
        $table->dropColumn('ghi_chu_admin');
    });
}

};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiaChiColumnsToDonHangsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->string('thanh_pho')->nullable()->after('dia_chi'); // hoặc sau trường nào tùy bạn
            $table->string('huyen')->nullable()->after('thanh_pho');
            $table->string('xa')->nullable()->after('huyen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->dropColumn(['thanh_pho', 'huyen', 'xa']);
        });
    }
}

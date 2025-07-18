<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{            
    public function up(): void
{
    Schema::table('don_hangs', function (Blueprint $table) {
        $table->string('dia_chi_day_du')->nullable()->after('dia_chi');
        $table->string('phi_ship')->nullable()->default(0)->after('so_tien_thanh_toan');

    });
}

public function down(): void
{
  Schema::table('don_hangs', function (Blueprint $table) {
        $table->dropColumn('phi_ship');
    });
}

};

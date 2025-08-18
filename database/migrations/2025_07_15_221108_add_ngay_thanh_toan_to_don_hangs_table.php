<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNgayThanhToanToDonHangsTable extends Migration
{
    public function up()
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->timestamp('ngay_thanh_toan')->nullable()->after('trang_thai_thanh_toan');
        });
    }

    public function down()
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->dropColumn('ngay_thanh_toan');
        });
    }
}

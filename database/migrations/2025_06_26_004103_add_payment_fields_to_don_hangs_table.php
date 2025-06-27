<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            
            $table->unsignedBigInteger('so_tien_thanh_toan')
                  ->after('trang_thai_thanh_toan');

            
            $table->string('mo_ta')
                  ->nullable()
                  ->after('so_tien_thanh_toan');

            
            $table->text('payment_link')
                  ->nullable()
                  ->after('mo_ta');

            
            $table->dateTime('expires_at')
                  ->nullable()
                  ->after('payment_link');
        });
    }

    public function down(): void
    {
        Schema::table('don_hangs', function (Blueprint $table) {
            $table->dropColumn([
                'so_tien_thanh_toan',
                'mo_ta',
                'payment_link',
                'expires_at',
            ]);
        });
    }
};

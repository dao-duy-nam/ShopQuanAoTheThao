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
        Schema::create('contact', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('reply_content')->nullable();
            $table->string('subject'); // tiêu đề
            $table->text('message'); // nội dung liên hệ
            $table->string('attachment')->nullable(); // tệp đính kèm
            $table->enum('type', ['gop_y', 'khieu_nai', 'hop_tac', 'ho_tro'])->default('ho_tro'); // loại phản hồi
            $table->enum('status', ['chua_xu_ly', 'dang_xu_ly','da_tra_loi'])->default('chua_xu_ly'); // trạng thái xử lý
            $table->timestamp('replied_at')->nullable(); // thời gian phản hồi (nếu có)
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact');
    }
};

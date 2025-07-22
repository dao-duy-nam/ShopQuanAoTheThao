<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tin_nhans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nguoi_gui_id')->constrained('users')->onDelete('cascade');     
            $table->foreignId('nguoi_nhan_id')->constrained('users')->onDelete('cascade');     
            $table->text('noi_dung')->nullable();               
            $table->string('tep_dinh_kem')->nullable();         
            $table->timestamp('da_doc_luc')->nullable();        
            $table->timestamps();                               
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tin_nhans');
    }
};

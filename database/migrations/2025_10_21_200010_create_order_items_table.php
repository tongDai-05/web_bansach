<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            // ID sách, không nên dùng constrained() vì sách có thể bị xóa
            $table->unsignedBigInteger('book_id'); 
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2); // Giá bán tại thời điểm đặt hàng
            
            // Lưu thông tin sách (tránh thay đổi giá trị nếu sách gốc bị sửa)
            $table->string('book_title'); 
            $table->string('book_author');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};

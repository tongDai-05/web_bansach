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
    public function up(): void
{
    Schema::create('books', function (Blueprint $table) {
        $table->id();
        $table->string('title');             // Tên sách
        $table->string('author');            // Tác giả
        $table->text('description')->nullable(); // Mô tả
        $table->decimal('price', 10, 2);     // Giá sách
        $table->integer('quantity')->default(0); // Số lượng
        $table->string('cover_image')->nullable(); // Ảnh bìa
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
        Schema::dropIfExists('books');
    }
};

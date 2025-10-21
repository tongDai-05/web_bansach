<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'book_id',
        'quantity',
        'unit_price',
        'book_title',
        'book_author',
    ];

    // Mối quan hệ: Một chi tiết đơn hàng thuộc về một Đơn hàng
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    // Không cần liên kết trực tiếp với Model Book vì đã lưu các thuộc tính sách (title, author) độc lập.
}
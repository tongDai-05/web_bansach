<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'book_id',
        'quantity',
        'price',
    ];

    // Mối quan hệ: Một mặt hàng giỏ thuộc về một Giỏ hàng
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    // Mối quan hệ: Một mặt hàng giỏ thuộc về một Sách
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
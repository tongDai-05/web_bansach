<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'author',
        'price',
        'description',
        'quantity',
        'cover_image',
    ];

    // Mối quan hệ: Một cuốn sách có thể nằm trong nhiều CartItem
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    // Mối quan hệ: Một cuốn sách có thể nằm trong nhiều OrderItem
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}

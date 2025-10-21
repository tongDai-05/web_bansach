<?php

namespace App\Models;

// Import các Models mới
use App\Models\Cart;
use App\Models\Order;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    // ----------------------------------------------------------------------
    // THÊM MỐI QUAN HỆ (RELATIONSHIPS)
    // ----------------------------------------------------------------------

    /**
     * Lấy các giỏ hàng (Cart) của người dùng này.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Lấy các đơn hàng (Order) của người dùng này.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
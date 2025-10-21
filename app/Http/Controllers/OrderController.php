<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Lấy giỏ hàng hiện tại (chỉ hỗ trợ đã đăng nhập trong ví dụ này).
     */
    protected function getCartForCheckout()
    {
        $cart = Auth::check() 
            ? Auth::user()->carts()->first()
            : Cart::where('session_id', session()->getId())->first();
        
        return $cart;
    }

    /**
     * Hiển thị trang thanh toán (checkout form).
     */
    public function checkout()
    {
        $cart = $this->getCartForCheckout();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống. Vui lòng thêm sách trước khi thanh toán.');
        }

        $cartItems = $cart->items()->with('book')->get();
        
        // Kiểm tra tồn kho trước khi thanh toán
        foreach ($cartItems as $item) {
            if ($item->quantity > $item->book->quantity) {
                return redirect()->route('cart.index')->with('error', 'Sách "' . $item->book->title . '" không đủ số lượng trong kho. Vui lòng cập nhật giỏ hàng.');
            }
        }

        $total = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Thiết lập thông tin mặc định nếu người dùng đã đăng nhập
        $userData = Auth::check() ? [
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ] : [
            'name' => old('customer_name'),
            'email' => old('customer_email'),
        ];


        return view('cart.checkout', compact('cartItems', 'total', 'userData'));
    }

    /**
     * Xử lý tạo Đơn hàng.
     */
    public function processOrder(Request $request)
    {
        // 1. Validate thông tin khách hàng
        $validated = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:500',
        ]);

        $cart = $this->getCartForCheckout();

        if (!$cart || $cart->items->isEmpty()) {
             return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đã bị thay đổi. Vui lòng kiểm tra lại.');
        }
        
        $cartItems = $cart->items()->with('book')->get();

        // Transaction đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();

        try {
            $totalPrice = 0;

            // 2. Tạo đơn hàng (Order)
            $order = Order::create(array_merge($validated, [
                'user_id' => Auth::id(),
                'total_price' => 0, // Sẽ update sau
                'status' => 'pending',
            ]));

            // 3. Tạo chi tiết đơn hàng (Order Items) và Cập nhật tồn kho
            foreach ($cartItems as $item) {
                $book = Book::find($item->book_id);
                
                // Kiểm tra lại tồn kho lần cuối
                if ($item->quantity > $book->quantity) {
                    DB::rollBack();
                    return redirect()->route('cart.index')->with('error', 'Sách "' . $book->title . '" không đủ số lượng trong kho.');
                }

                // Giảm tồn kho sách
                $book->decrement('quantity', $item->quantity);
                
                // Tạo OrderItem
                $order->items()->create([
                    'book_id' => $book->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->price,
                    'book_title' => $book->title,
                    'book_author' => $book->author,
                ]);

                $totalPrice += $item->price * $item->quantity;
            }

            // 4. Cập nhật tổng tiền và xóa giỏ hàng
            $order->update(['total_price' => $totalPrice]);
            $cart->delete(); // Xóa giỏ hàng sau khi tạo đơn hàng

            DB::commit();

            return redirect()->route('order.success', $order->id)->with('success', 'Đặt hàng thành công! Mã đơn hàng của bạn là #' . $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra trong quá trình xử lý đơn hàng. Vui lòng thử lại.');
        }
    }
    
    /**
     * Trang hiển thị đơn hàng thành công.
     */
    public function orderSuccess(Order $order)
    {
        return view('cart.order-success', compact('order'));
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Lấy giỏ hàng hiện tại của người dùng (hoặc tạo mới).
     */
    protected function getOrCreateCart()
    {
        // 1. Kiểm tra nếu người dùng đã đăng nhập
        if (Auth::check()) {
            // Liên kết giỏ hàng cũ (session) nếu có, sau đó trả về giỏ hàng đã đăng nhập
            $sessionCart = Cart::where('session_id', session()->getId())->first();
            if ($sessionCart) {
                $sessionCart->user_id = Auth::id();
                $sessionCart->session_id = null;
                $sessionCart->save();
            }
            return Auth::user()->carts()->firstOrCreate([]);
        }
        
        // 2. Nếu là khách vãng lai, sử dụng session ID
        $sessionId = session()->getId();
        return Cart::firstOrCreate(['session_id' => $sessionId], ['session_id' => $sessionId]);
    }

    /**
     * Hiển thị Giỏ hàng.
     */
    public function index()
    {
        $cart = $this->getOrCreateCart();
        $cartItems = $cart->items()->with('book')->get();

        return view('cart.index', compact('cartItems'));
    }

    /**
     * Thêm sản phẩm vào giỏ hàng.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $book = Book::find($validated['book_id']);

        // Kiểm tra tồn kho trước khi thêm
        if ($validated['quantity'] > $book->quantity) {
            return redirect()->back()->with('error', 'Sách "' . $book->title . '" chỉ còn ' . $book->quantity . ' cuốn trong kho.');
        }

        $cart = $this->getOrCreateCart();
        $cartItem = $cart->items()->where('book_id', $book->id)->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $validated['quantity'];
             // Kiểm tra tồn kho lần nữa (sau khi cộng dồn)
            if ($newQuantity > $book->quantity) {
                 return redirect()->back()->with('error', 'Tổng số lượng yêu cầu (' . $newQuantity . ') vượt quá tồn kho.');
            }
            $cartItem->update(['quantity' => $newQuantity]);
            $message = 'Đã cập nhật số lượng sách "' . $book->title . '" trong giỏ hàng!';
        } else {
            $cart->items()->create([
                'book_id' => $book->id,
                'quantity' => $validated['quantity'],
                'price' => $book->price, // Ghi lại giá tại thời điểm thêm
            ]);
            $message = 'Đã thêm sách "' . $book->title . '" vào giỏ hàng!';
        }

        return redirect()->route('cart.index')->with('success', $message);
    }
    
    /**
     * Cập nhật số lượng mặt hàng trong giỏ.
     * $item được Resolve là Model CartItem nhờ Route Model Binding.
     */
    public function update(Request $request, CartItem $item)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        
        $book = $item->book;
        
        // Kiểm tra tồn kho
        if ($validated['quantity'] > $book->quantity) {
             return redirect()->route('cart.index')->with('error', 'Số lượng sách "' . $book->title . '" vượt quá tồn kho cho phép.');
        }

        $item->update(['quantity' => $validated['quantity']]);

        return redirect()->route('cart.index')->with('success', 'Đã cập nhật số lượng sách "' . $book->title . '" thành công.');
    }

    /**
     * Xóa mặt hàng khỏi giỏ hàng.
     * $item được Resolve là Model CartItem.
     */
    public function destroy(CartItem $item)
    {
        $bookTitle = $item->book->title;
        $item->delete();

        return redirect()->route('cart.index')->with('success', 'Đã xóa sách "' . $bookTitle . '" khỏi giỏ hàng.');
    }
}
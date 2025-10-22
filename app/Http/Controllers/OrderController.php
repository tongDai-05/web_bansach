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
    // Áp dụng middleware 'auth' và 'role:admin' cho tất cả các phương thức
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Hiển thị danh sách tất cả đơn hàng (cho Admin).
     */
    public function index()
    {
        $orders = Order::latest()->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Hiển thị chi tiết một đơn hàng.
     */
    public function show(Order $order)
    {
        $order->load('items');

        $statuses = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đã giao hàng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return view('admin.orders.show', compact('order', 'statuses'));
    }

    /**
     * Cập nhật trạng thái của đơn hàng.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);
        
        // Nếu Admin chuyển trạng thái sang "cancelled", reset cencellation_requested
        if ($validated['status'] === 'cancelled') {
             $order->update(['cancellation_requested' => false]);
        }

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
    }
    
    /**
     * 1. Admin Duyệt Yêu Cầu Hủy từ User (processRefund).
     * Cập nhật tồn kho và hủy đơn hàng.
     */
    public function processRefund(Order $order)
    {
        // Kiểm tra nếu đơn hàng đã bị hủy hoặc hoàn thành thì không thể hoàn tiền
        if (in_array($order->status, ['cancelled', 'completed'])) {
            return redirect()->back()->with('error', 'Không thể hoàn tiền cho đơn hàng đã ' . $order->status . '.');
        }

        return $this->performCancellation($order, 'Đã duyệt yêu cầu hủy và hoàn tiền thành công!');
    }

    /**
     * 2. Admin Chủ động Hủy đơn hàng (adminCancelOrder).
     * Dùng chung logic với processRefund nhưng không cần kiểm tra cencellation_requested.
     * Phương thức này cần thiết để khớp với Route đã định nghĩa.
     */
    public function adminCancelOrder(Order $order)
    {
        // Kiểm tra nếu đơn hàng đã bị hủy hoặc hoàn thành thì không thể hủy
        if (in_array($order->status, ['cancelled', 'completed'])) {
            return redirect()->back()->with('error', 'Không thể hủy đơn hàng đã ' . $order->status . '.');
        }
        
        return $this->performCancellation($order, 'Đã chủ động hủy và hoàn tiền thành công!');
    }

    /**
     * Logic chung để Hủy đơn hàng và hoàn lại tồn kho.
     */
    protected function performCancellation(Order $order, $successMessage)
    {
        DB::beginTransaction();
        try {
            // 1. Cập nhật trạng thái đơn hàng thành "cancelled" và bỏ cờ yêu cầu
            $order->update([
                'status' => 'cancelled',
                'cancellation_requested' => false,
            ]);

            // 2. Hoàn lại số lượng sách vào tồn kho
            foreach ($order->items as $item) {
                // Tăng số lượng tồn kho của sách
                $book = Book::find($item->book_id);
                if ($book) {
                    $book->increment('quantity', $item->quantity);
                }
            }
            
            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)->with('success', $successMessage . ' Tồn kho đã được cập nhật.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Lỗi: Không thể xử lý hủy/hoàn tiền. Vui lòng thử lại.');
        }
    }



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
                'cancellation_requested' => false, // Set default value
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

            // SỬA: CHUYỂN HƯỚNG ĐẾN TÊN ROUTE MỚI LÀ orders.show
            return redirect()->route('orders.show', $order->id)->with('success', 'Đặt hàng thành công! Mã đơn hàng của bạn là #' . $order->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra trong quá trình xử lý đơn hàng. Vui lòng thử lại.');
        }
    }
    
    /**
     * Hiển thị chi tiết đơn hàng (User/Admin).
     */
    public function showOrder(Order $order)
    {
        // Kiểm tra quyền truy cập: Chỉ user sở hữu hoặc admin mới được xem
        if (Auth::check()) {
            // Lấy order user id
            $orderUserId = $order->user_id;

            // Kiểm tra: nếu user id của order không khớp với Auth user id và Auth user không phải admin
            if ($orderUserId !== Auth::id() && Auth::user()->role !== 'admin') {
                abort(403, 'Bạn không có quyền xem đơn hàng này.');
            }
        } else {
             // Đảm bảo chỉ user đã đăng nhập mới được xem chi tiết đơn hàng
             abort(403, 'Bạn phải đăng nhập để xem đơn hàng này.'); 
        }
        
        return view('cart.order-success', compact('order'));
    }

    /**
     * Hiển thị lịch sử đơn hàng của người dùng đã đăng nhập.
     */
    public function orderHistory()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $orders = Auth::user()->orders()->latest()->paginate(10);

        return view('cart.order-history', compact('orders'));
    }

    /**
     * Cho phép người dùng yêu cầu hủy/hoàn tiền đơn hàng.
     */
    public function requestCancellation(Order $order)
    {
        // 1. Chỉ cho phép người dùng hiện tại yêu cầu hủy đơn hàng của họ
        if ($order->user_id !== Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập đơn hàng này.');
        }

        // 2. Chỉ cho phép yêu cầu hủy nếu đơn hàng đang ở trạng thái pending/processing và chưa bị hủy/hoàn thành
        if ($order->status === 'cancelled' || $order->status === 'completed') {
            return redirect()->back()->with('error', 'Đơn hàng đã hoàn thành hoặc đã bị hủy. Không thể yêu cầu hủy.');
        }
        
        // 3. Nếu đã có yêu cầu rồi thì không cho phép gửi lại
        if ($order->cancellation_requested) {
             return redirect()->back()->with('error', 'Bạn đã gửi yêu cầu hủy đơn hàng này trước đó.');
        }

        // 4. Cập nhật trạng thái yêu cầu hủy
        $order->update(['cancellation_requested' => true]);

        return redirect()->back()->with('success', 'Yêu cầu hủy/hoàn tiền đơn hàng #' . $order->id . ' đã được gửi tới Admin. Chúng tôi sẽ phản hồi sớm!');
    }
}

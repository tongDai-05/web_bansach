<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Book; // Cần import Model Book để cập nhật tồn kho
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // Có thể cần nếu bạn dùng Auth::id()

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

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Cập nhật trạng thái đơn hàng thành công!');
    }
    
    /**
     * Xử lý hoàn tiền/hủy đơn hàng (Chức năng Admin).
     * Đảm bảo phương thức này tồn tại và tên là refundOrder.
     */
    public function refundOrder(Order $order)
    {
        // Kiểm tra nếu đơn hàng đã bị hủy hoặc hoàn thành thì không thể hoàn tiền
        if (in_array($order->status, ['cancelled', 'completed'])) {
            return redirect()->back()->with('error', 'Không thể hoàn tiền cho đơn hàng đã ' . $order->status . '.');
        }

        DB::beginTransaction();
        try {
            // 1. Cập nhật trạng thái đơn hàng thành "cancelled" (Đã hủy)
            $order->update(['status' => 'cancelled']);

            // 2. Hoàn lại số lượng sách vào tồn kho
            foreach ($order->items as $item) {
                // Tăng số lượng tồn kho của sách
                $book = Book::find($item->book_id);
                if ($book) {
                    $book->increment('quantity', $item->quantity);
                }
            }
            
            DB::commit();

            return redirect()->route('admin.orders.show', $order->id)->with('success', 'Đã hủy và hoàn tiền thành công! Tồn kho đã được cập nhật.');

        } catch (\Exception $e) {
            DB::rollBack();
            // In ra lỗi để debug, nhưng trả về thông báo lỗi thân thiện hơn
            return redirect()->back()->with('error', 'Lỗi: Không thể xử lý hoàn tiền. Vui lòng thử lại. Lỗi chi tiết: ' . $e->getMessage());
        }
    }
}

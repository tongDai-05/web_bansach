<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Book; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 

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
}

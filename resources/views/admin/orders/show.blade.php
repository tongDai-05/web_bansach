@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Chi tiết Đơn hàng #{{ $order->id }}</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    {{-- Hiển thị thông báo lỗi khi không thể hoàn tiền --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        {{-- CỘT BÊN TRÁI: THÔNG TIN CHUNG --}}
        <div class="col-md-5">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Thông tin Khách hàng & Vận chuyển
                </div>
                <div class="card-body">
                    <p><strong>Khách hàng:</strong> {{ $order->customer_name }}</p>
                    <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                    <p><strong>Điện thoại:</strong> {{ $order->customer_phone }}</p>
                    <p><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>
                    <p><strong>Ngày đặt hàng:</strong> {{ $order->created_at->format('H:i:s d/m/Y') }}</p>
                    <h5 class="mt-3"><strong>Tổng cộng:</strong> <span class="text-danger">{{ number_format($order->total_price, 0, ',', '.') }} đ</span></h5>
                </div>
            </div>
            
            {{-- FORM CẬP NHẬT TRẠNG THÁI --}}
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    Cập nhật Trạng thái
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.updateStatus', $order->id) }}" method="POST">
                        @csrf
                        @method('PUT') 
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái hiện tại</label>
                            <select name="status" id="status" class="form-control" required>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ $order->status === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Cập nhật Trạng thái</button>
                    </form>
                </div>
            </div>
            
            {{-- NÚT HOÀN TIỀN/HỦY ĐƠN HÀNG --}}
            <div class="card">
                <div class="card-header bg-danger text-white">
                    Thao tác nguy hiểm
                </div>
                <div class="card-body">
                    {{-- Chỉ hiển thị nút Hoàn tiền nếu đơn hàng chưa bị Hủy hoặc Hoàn thành --}}
                    @if(!in_array($order->status, ['cancelled', 'completed']))
                        {{-- SỬA: Đổi route từ admin.orders.refund sang admin.orders.processRefund --}}
                        <form action="{{ route('admin.orders.processRefund', $order->id) }}" method="POST" onsubmit="return confirm('Xác nhận hoàn tiền và hủy đơn hàng #{{ $order->id }}? Thao tác này KHÔNG thể hoàn tác và sẽ cập nhật lại tồn kho.')">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                🔄 Hủy & Hoàn tiền
                            </button>
                            <small class="text-muted d-block mt-2">Đơn hàng sẽ được chuyển sang trạng thái "Đã hủy" và số lượng sách sẽ được cộng lại vào kho.</small>
                        </form>
                    @else
                        <div class="alert alert-secondary mb-0">Đơn hàng này đã **{{ $order->status === 'completed' ? 'Hoàn thành' : 'Đã hủy' }}**. Không thể hoàn tiền.</div>
                    @endif
                </div>
            </div>
            
        </div>

        {{-- CỘT BÊN PHẢI: CHI TIẾT SẢN PHẨM --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header bg-success text-white">
                    Chi tiết Sản phẩm
                </div>
                <ul class="list-group list-group-flush">
                    @foreach($order->items as $item)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $item->book_title }}</strong> ({{ $item->book_author }})
                                    <p class="mb-0 text-muted">Giá: {{ number_format($item->unit_price, 0, ',', '.') }} đ</p>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-secondary">SL: {{ $item->quantity }}</span>
                                    <p class="mb-0 fw-bold">{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }} đ</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Quay lại Danh sách Đơn hàng</a>
    </div>
</div>
@endsection

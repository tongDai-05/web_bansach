@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">🎉 Chi tiết Đơn hàng #{{ $order->id }}</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    
                    <p class="lead">Đơn hàng của bạn đang được xử lý.</p>
                    
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item"><strong>Mã Đơn hàng:</strong> #{{ $order->id }}</li>
                        <li class="list-group-item"><strong>Tổng giá trị:</strong> {{ number_format($order->total_price, 0, ',', '.') }} đ</li>
                        {{-- HIỂN THỊ TRẠNG THÁI --}}
                        <li class="list-group-item">
                            <strong>Trạng thái:</strong> 
                            @php
                                $statusMap = [
                                    'pending' => ['label' => 'Chờ xử lý', 'class' => 'bg-warning text-dark'],
                                    'processing' => ['label' => 'Đang xử lý', 'class' => 'bg-info text-dark'],
                                    'shipped' => ['label' => 'Đã giao hàng', 'class' => 'bg-primary'],
                                    'completed' => ['label' => 'Hoàn thành', 'class' => 'bg-success'],
                                    'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-danger'],
                                ];
                                $statusInfo = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'bg-secondary'];
                            @endphp
                            <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                            
                            @if($order->cancellation_requested)
                                <span class="badge bg-danger ms-2">ĐÃ YÊU CẦU HỦY</span>
                            @endif
                        </li>
                        {{-- END HIỂN THỊ TRẠNG THÁI --}}
                        <li class="list-group-item"><strong>Người nhận:</strong> {{ $order->customer_name }}</li>
                        <li class="list-group-item"><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</li>
                    </ul>
                    
                    {{-- CHI TIẾT SẢN PHẨM --}}
                    <h5 class="mt-4 mb-3">Sản phẩm đã đặt</h5>
                    <ul class="list-group mb-4">
                        @foreach($order->items()->get() as $item)
                        <li class="list-group-item d-flex justify-content-between">
                            <div>
                                {{ $item->book_title }} (SL: {{ $item->quantity }})
                            </div>
                            <span class="fw-bold">{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }} đ</span>
                        </li>
                        @endforeach
                    </ul>
                    {{-- END CHI TIẾT SẢN PHẨM --}}

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('books.index') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
                        
                        {{-- NÚT YÊU CẦU HỦY --}}
                        {{-- Chỉ cho phép hủy nếu là trạng thái 'pending' và chưa yêu cầu hủy --}}
                        @if($order->status === 'pending' && !$order->cancellation_requested)
                            <form action="{{ route('orders.requestCancellation', $order->id) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn yêu cầu hủy đơn hàng #{{ $order->id }}? Admin sẽ duyệt yêu cầu này.')">
                                @csrf
                                <button type="submit" class="btn btn-danger">Yêu cầu Hủy/Hoàn tiền</button>
                            </form>
                        @elseif($order->cancellation_requested)
                            <button class="btn btn-secondary" disabled>Đã gửi yêu cầu Hủy</button>
                        @endif
                        {{-- END NÚT YÊU CẦU HỦY --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

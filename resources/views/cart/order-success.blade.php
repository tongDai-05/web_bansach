@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">🎉 Đặt hàng Thành công!</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    <p class="lead">Cảm ơn bạn đã đặt hàng. Đơn hàng của bạn đang được xử lý.</p>
                    
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item"><strong>Mã Đơn hàng:</strong> #{{ $order->id }}</li>
                        <li class="list-group-item"><strong>Tổng giá trị:</strong> {{ number_format($order->total_price, 0, ',', '.') }} đ</li>
                        <li class="list-group-item"><strong>Trạng thái:</strong> <span class="badge bg-warning text-dark">{{ $order->status }}</span></li>
                        <li class="list-group-item"><strong>Người nhận:</strong> {{ $order->customer_name }}</li>
                        <li class="list-group-item"><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</li>
                    </ul>

                    <a href="{{ route('books.index') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
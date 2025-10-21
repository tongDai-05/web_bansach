@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Thông tin Thanh toán</h2>
    
    {{-- Hiển thị thông báo lỗi (ví dụ: không đủ tồn kho) --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    
    <div class="row">
        
        <div class="col-md-7">
            <h4 class="mb-3">Thông tin nhận hàng</h4>
            {{-- Form này sẽ gửi POST request đến OrderController@processOrder --}}
            <form action="{{ route('order.process') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="customer_name" class="form-label">Họ và Tên</label>
                    {{-- Sử dụng $userData['name'] được truyền từ Controller để điền thông tin mặc định --}}
                    <input type="text" class="form-control @error('customer_name') is-invalid @enderror" id="customer_name" name="customer_name" value="{{ old('customer_name', $userData['name'] ?? '') }}" required>
                    @error('customer_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="customer_email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('customer_email') is-invalid @enderror" id="customer_email" name="customer_email" value="{{ old('customer_email', $userData['email'] ?? '') }}" required>
                    @error('customer_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="customer_phone" class="form-label">Số điện thoại</label>
                    <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required>
                    @error('customer_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="shipping_address" class="form-label">Địa chỉ nhận hàng</label>
                    <textarea class="form-control @error('shipping_address') is-invalid @enderror" id="shipping_address" name="shipping_address" rows="3" required>{{ old('shipping_address') }}</textarea>
                    @error('shipping_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <hr class="my-4">
                
                <button class="w-100 btn btn-primary btn-lg" type="submit">Xác nhận Đặt hàng</button>
            </form>
        </div>

        {{-- CỘT HIỂN THỊ TỔNG QUAN ĐƠN HÀNG --}}
        <div class="col-md-5">
            <h4 class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted">Đơn hàng của bạn</span>
                <span class="badge bg-secondary rounded-pill">{{ $cartItems->count() }}</span>
            </h4>
            <ul class="list-group mb-3">
                @foreach($cartItems as $item)
                <li class="list-group-item d-flex justify-content-between lh-sm">
                    <div>
                        <h6 class="my-0">{{ $item->book->title }}</h6>
                        <small class="text-muted">SL: {{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }} đ</small>
                    </div>
                    <span class="text-muted">{{ number_format($item->price * $item->quantity, 0, ',', '.') }} đ</span>
                </li>
                @endforeach
                
                <li class="list-group-item d-flex justify-content-between">
                    <span>Tổng cộng (VNĐ)</span>
                    <strong>{{ number_format($total, 0, ',', '.') }} đ</strong>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
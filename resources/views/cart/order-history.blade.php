@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>🕒 Lịch sử Đơn hàng của bạn</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    @if($orders->isEmpty())
        <div class="alert alert-info">Bạn chưa có đơn hàng nào. <a href="{{ route('books.index') }}">Bắt đầu mua sắm ngay!</a></div>
    @else
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Mã ĐH</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td><strong class="text-danger">{{ number_format($order->total_price, 0, ',', '.') }} đ</strong></td>
                        <td>
                            {{-- Hiển thị trạng thái với màu sắc tương ứng --}}
                            @php
                                $statusMap = [
                                    'pending' => ['label' => 'Chờ xử lý', 'class' => 'bg-warning text-dark'],
                                    'processing' => ['label' => 'Đang xử lý', 'class' => 'bg-info text-dark'],
                                    'shipped' => ['label' => 'Đã giao hàng', 'class' => 'bg-primary'],
                                    'completed' => ['label' => 'Hoàn thành', 'class' => 'bg-success'],
                                    'cancelled' => ['label' => 'Đã hủy', 'class' => 'bg-danger'],
                                ];
                                // Sử dụng status mặc định nếu không khớp
                                $statusInfo = $statusMap[$order->status] ?? ['label' => ucfirst($order->status), 'class' => 'bg-secondary'];
                            @endphp
                            <span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span>
                        </td>
                        <td>
                            {{-- SỬA CHỮA: Sử dụng route 'orders.show' --}}
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-info">Xem</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        {{ $orders->links() }}
    @endif
</div>
@endsection

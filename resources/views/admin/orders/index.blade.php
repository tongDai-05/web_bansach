@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>📋 Quản lý Đơn hàng</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Mã ĐH</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày đặt</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>#{{ $order->id }}</td>
                    <td>{{ $order->customer_name }} ({{ $order->customer_email }})</td>
                    <td>{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                    <td>
                        {{-- Hiển thị trạng thái với màu sắc tương ứng --}}
                        @php
                            $badgeClass = [
                                'pending' => 'bg-warning text-dark',
                                'processing' => 'bg-info text-dark',
                                'shipped' => 'bg-primary',
                                'completed' => 'bg-success',
                                'cancelled' => 'bg-danger',
                            ][$order->status] ?? 'bg-secondary';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">Xem chi tiết</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted p-4">
                        Không có đơn hàng nào.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $orders->links() }}
</div>
@endsection
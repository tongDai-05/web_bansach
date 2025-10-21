@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>📚 Danh sách sách</h2>
        @if(auth()->check() && auth()->user()->role === 'admin')
            <a href="{{ route('books.create') }}" class="btn btn-primary">➕ Thêm sách mới</a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Ảnh bìa</th>
                <th>Tên sách</th>
                <th>Tác giả</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($books as $book)
                <tr>
                    <td>{{ $book->id }}</td>
                    <td>
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}" width="60" height="80" style="object-fit: cover;">
                        @else
                            <span class="text-muted">Không có ảnh</span>
                        @endif
                    </td>
                    <td>{{ $book->title }}</td>
                    <td>{{ $book->author }}</td>
                    <td>{{ number_format($book->price, 0, ',', '.') }} đ</td>
                    <td>{{ $book->quantity }}</td>
                    <td>
                        <a href="{{ route('books.show', $book) }}" class="btn btn-sm btn-info">Xem</a>

                        @if(auth()->check() && auth()->user()->role === 'admin')
                            <a href="{{ route('books.edit', $book) }}" class="btn btn-sm btn-warning">Sửa</a>
                            <form action="{{ route('books.destroy', $book) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa sách này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Xóa</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $books->links() }}
</div>
@endsection

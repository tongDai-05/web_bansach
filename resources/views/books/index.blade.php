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
    
    {{-- FORM TÌM KIẾM --}}
    <form method="GET" action="{{ route('books.index') }}" class="mb-4">
        <div class="input-group">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Tìm kiếm theo tên sách hoặc tác giả..." 
                value="{{ request('search') }}">
            <button class="btn btn-outline-secondary" type="submit">Tìm</button>
            @if(request('search'))
                <a href="{{ route('books.index') }}" class="btn btn-outline-danger">Xóa tìm kiếm</a>
            @endif
        </div>
    </form>
    {{-- END FORM TÌM KIẾM --}}

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
            {{-- ĐÃ KHÔI PHỤC VÒNG LẶP HIỂN THỊ SÁCH --}}
            @forelse ($books as $book)
                <tr>
                    <td>{{ $book->id }}</td>
                    <td>
                        {{-- Dùng cover_image đã được đồng bộ --}}
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
            @empty
                {{-- THÔNG BÁO KHI KHÔNG CÓ KẾT QUẢ --}}
                <tr>
                    <td colspan="7" class="text-center text-muted p-4">
                        Không tìm thấy cuốn sách nào.
                        @if(request('search'))
                            Vui lòng thử từ khóa khác.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $books->links() }}
</div>
@endsection
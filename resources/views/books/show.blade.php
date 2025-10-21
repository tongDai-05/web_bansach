@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="row">
        <div class="col-md-4">
            <img src="{{ asset('storage/'.$book->image) }}" class="img-fluid rounded" alt="{{ $book->title }}">
        </div>
        <div class="col-md-8">
            <h2>{{ $book->title }}</h2>
            <p><strong>Tác giả:</strong> {{ $book->author }}</p>
            <p><strong>Giá:</strong> {{ number_format($book->price, 0, ',', '.') }} VNĐ</p>
            <p><strong>Mô tả:</strong> {{ $book->description }}</p>

            <a href="{{ route('books.index') }}" class="btn btn-secondary">Quay lại</a>

            @if(auth()->check() && auth()->user()->role === 'admin')
                <a href="{{ route('books.edit', $book->id) }}" class="btn btn-warning">Sửa</a>
                <form action="{{ route('books.destroy', $book->id) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn chắc chắn muốn xóa sách này?')">Xóa</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

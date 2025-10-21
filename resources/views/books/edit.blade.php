@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Sửa thông tin sách</h2>

    <form action="{{ route('books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label class="form-label">Tên sách</label>
            <input type="text" name="title" value="{{ $book->title }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tác giả</label>
            <input type="text" name="author" value="{{ $book->author }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Giá</label>
            <input type="number" name="price" value="{{ $book->price }}" class="form-control" required min="0">
        </div>
        
        <div class="mb-3">
            <label class="form-label">Số lượng</label>
            <input type="number" name="quantity" value="{{ $book->quantity }}" class="form-control" required min="0">
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control" rows="3">{{ $book->description }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Ảnh bìa (chọn ảnh mới nếu muốn đổi)</label>
            <input type="file" name="cover_image" class="form-control"> 
            @if($book->cover_image) 
                <img src="{{ asset('storage/'.$book->cover_image) }}" alt="Ảnh bìa" class="mt-2" style="width:120px;">
            @endif
        </div>

        <button type="submit" class="btn btn-success">Cập nhật</button>
        <a href="{{ route('books.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection
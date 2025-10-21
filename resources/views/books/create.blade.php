@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2> Thêm sách mới</h2>
    <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Tên sách</label>
            <input type="text" class="form-control" id="title" name="title" required>
        </div>

        <div class="mb-3">
            <label for="author" class="form-label">Tác giả</label>
            <input type="text" class="form-control" id="author" name="author" required>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Số lượng</label>
            <input type="number" class="form-control" id="quantity" name="quantity" value="{{ old('quantity') }}" required min="0">
        </div>

        <div class="mb-3">
            <label for="price" class="form-label">Giá (VNĐ)</label>
            <input type="number" class="form-control" id="price" name="price" required min="0">
        </div>

      

        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
        </div>

        <div class="mb-3">
            <label for="cover_image" class="form-label">Ảnh bìa sách</label>
            <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success">💾 Lưu sách</button>
        <a href="{{ route('books.index') }}" class="btn btn-secondary">🔙 Quay lại</a>
    </form>
</div>
@endsection
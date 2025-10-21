<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->except(['index', 'show']);
    }



    // Hiển thị danh sách sách
    public function index(Request $request) // <--- NHẬN VÀO OBJECT REQUEST
    {
       // Khởi tạo query ban đầu (có thể là sắp xếp mới nhất)
        $query = \App\Models\Book::latest();
        
        // Lấy từ khóa tìm kiếm (Sử dụng trim() để loại bỏ khoảng trắng dư thừa)
        $search = trim($request->input('search'));

        // Logic Tìm kiếm: Chỉ áp dụng nếu $search KHÔNG rỗng và có giá trị
        if (!empty($search)) {
            // Sử dụng Closure để nhóm các điều kiện OR, tránh xung đột
            $query->where(function ($q) use ($search) {
                // Kiểm tra tiêu đề (title) hoặc tác giả (author)
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%");
            });
        }
        
        // Phân trang và giữ lại query string (search parameter)
        $books = $query->paginate(10)->withQueryString();
        
        return view('books.index', compact('books'));
    }

    // Form thêm sách
    public function create()
    {
        return view('books.create');
    }

    // Lưu sách mới
    public function store(Request $request)
    {
       // Validate dữ liệu
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
        'description' => 'nullable|string',
        'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);
    // Xử lý upload ảnh
    if ($request->hasFile('cover_image')) {
        $path = $request->file('cover_image')->store('books', 'public');
        $validated['cover_image'] = $path;
    }
    // Lưu sách vào database
    \App\Models\Book::create($validated);
    //  Chuyển hướng về danh sách với thông báo
    return redirect()->route('books.index')->with('success', 'Thêm sách thành công!');
    }



    // Hiển thị chi tiết sách
    public function show(Book $book)
    {
        return view('books.show', compact('book'));
    }

    // Form sửa sách
    public function edit(Book $book)
    {
        return view('books.edit', compact('book'));
    }

    // Cập nhật sách
    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'price' => 'required|numeric',
            'quantity' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('cover_image')) {
            // Xóa ảnh cũ
            if ($book->cover_image) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $book->update($data);

        return redirect()->route('books.index')->with('success', 'Cập nhật sách thành công!');
    }

    // Xóa sách
    public function destroy(Book $book)
    {
        if ($book->cover_image) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();
        return redirect()->route('books.index')->with('success', 'Xóa sách thành công!');
    }
}

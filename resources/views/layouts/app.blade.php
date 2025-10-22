<?php
// File: resources/views/layouts/app.blade.php

?>
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Website Bán Sách Tự Động') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ route('books.index') }}">
                    {{ config('app.name', 'Website Bán Sách Tự Động') }}
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    {{-- Left Side Of Navbar - SỬ DỤNG CHO CÁC LIÊN KẾT CHÍNH --}}
                    <ul class="navbar-nav me-auto">
                        {{-- Thêm liên kết Trang chủ hoặc Sách ở đây nếu cần --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('books.index') }}">Sách</a>
                        </li>
                    
                        {{-- THÊM LIÊN KẾT ADMIN DASHBOARD/QUẢN LÝ ĐƠN HÀNG --}}
                        @auth
                            @if (Auth::user()->role === 'admin')
                                <li class="nav-item">
                                    <a class="nav-link text-danger fw-bold" href="{{ route('admin.orders.index') }}">
                                        🛠️ Quản lý Đơn hàng
                                    </a>
                                </li>
                            @endif
                        @endauth
                        
                    </ul>

                    {{-- Right Side Of Navbar - CHỈ CHỨA GIỎ HÀNG VÀ AUTH LINKS --}}
                    <ul class="navbar-nav ms-auto">
                        
                        {{-- LIÊN KẾT GIỎ HÀNG --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('cart.index') }}">
                                🛒 Giỏ hàng
                            </a>
                        </li>
                        
                        {{-- AUTHENTICATION LINKS (Login/Register/Logout Dropdown) --}}
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            {{-- LIÊN KẾT LỊCH SỬ ĐƠN HÀNG ĐỘC LẬP (NÊN THÊM ĐỂ TRÁNH LỖI DROPPER) --}}
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('orders.history') }}">
                                    Lịch sử Đơn hàng
                                </a>
                            </li>

                            <li class="nav-item dropdown">
                                {{-- Thẻ A chỉ dùng để hiển thị tên và toggle dropdown --}}
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    {{-- BỎ LIÊN KẾT LỊCH SỬ ĐƠN HÀNG KHỎI DROP DOWN ĐỂ TRÁNH NHẦM LẪN VÀ ĐƯA RA NGOÀI --}}
                                    
                                    {{-- GIỮ LẠI LOGOUT --}}
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>
</html>

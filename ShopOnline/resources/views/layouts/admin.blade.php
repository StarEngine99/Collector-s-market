<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị - Chợ Figure & Thẻ Bài')</title>

    {{-- Bootstrap 5.3 & Bootstrap Icons (MIT License) --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">
    <link href="{{ asset('css/storefront.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark sticky-top shadow-sm px-3">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-light btn-sm d-lg-none"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#adminSidebarOffcanvas"
                    aria-controls="adminSidebarOffcanvas"
                    aria-label="Mở menu quản trị">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand fw-bold mb-0" href="{{ Route::has('home') ? route('home') : url('/') }}">
                <i class="bi bi-shield-lock me-1"></i> Quản trị Cửa hàng
            </a>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('account.index') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-person-circle me-1"></i> Tài khoản cá nhân
            </a>
            <a href="{{ Route::has('home') ? route('home') : url('/') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-shop me-1"></i> Xem trang bán hàng
            </a>
            @auth
                <span class="text-white small d-none d-md-inline">{{ Auth::user()->HoTen }}</span>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
                    </button>
                </form>
            @endauth
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <aside class="col-lg-2 d-none d-lg-flex flex-column admin-sidebar min-vh-100 px-2 py-3">
                <a class="navbar-brand fw-bold text-white px-3 mb-3"
                   href="{{ Auth::user()->can('users.manage') ? route('admin.dashboard') : route('home') }}">
                    <i class="bi bi-stars me-1"></i> Collector Market
                </a>
                <div class="list-group list-group-flush flex-grow-1">
                    @include('admin.partials.navigation')
                </div>
                <div class="admin-profile mx-2 mt-4 pt-3">
                    @auth
                        <div class="fw-semibold text-white">{{ Auth::user()->HoTen }}</div>
                        <div class="small text-white-50">Quản trị cửa hàng</div>
                    @endauth
                </div>
            </aside>

            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="adminSidebarOffcanvas" aria-labelledby="adminSidebarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title fw-bold" id="adminSidebarLabel">
                        <i class="bi bi-stars me-1"></i> Menu quản trị
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
                </div>
                <div class="offcanvas-body p-0">
                    <div class="list-group list-group-flush">
                        @include('admin.partials.navigation')
                    </div>
                </div>
            </div>

            <main class="col-12 col-lg-10 py-4 px-3 px-md-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
    @stack('scripts')
</body>
</html>

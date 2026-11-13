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
            <a href="{{ Route::has('home') ? route('home') : url('/') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-shop me-1"></i> Xem trang bán hàng
            </a>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            {{-- Sidebar trên desktop --}}
            <aside class="col-lg-2 d-none d-lg-block bg-white border-end min-vh-100 py-3">
                <div class="list-group list-group-flush">
                    <div class="px-3 py-2 text-uppercase text-muted small fw-semibold">Nội dung (B6)</div>
                    <a href="{{ Route::has('admin.posts.index') ? route('admin.posts.index') : '#' }}"
                       class="list-group-item list-group-item-action">
                        <i class="bi bi-file-earmark-text me-2"></i> Bài viết
                    </a>
                    <a href="{{ Route::has('admin.banners.index') ? route('admin.banners.index') : '#' }}"
                       class="list-group-item list-group-item-action">
                        <i class="bi bi-images me-2"></i> Banner trang chủ
                    </a>

                    <div class="px-3 py-2 mt-2 text-uppercase text-muted small fw-semibold">Báo cáo (B7)</div>
                    <a href="{{ Route::has('admin.reports.index') ? route('admin.reports.index') : '#' }}"
                       class="list-group-item list-group-item-action">
                        <i class="bi bi-bar-chart-line me-2"></i> Thống kê doanh thu
                    </a>
                </div>
            </aside>

            {{-- Sidebar offcanvas trên mobile --}}
            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="adminSidebarOffcanvas" aria-labelledby="adminSidebarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="adminSidebarLabel">Menu quản trị</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
                </div>
                <div class="offcanvas-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="{{ Route::has('admin.posts.index') ? route('admin.posts.index') : '#' }}"
                           class="list-group-item list-group-item-action">
                            <i class="bi bi-file-earmark-text me-2"></i> Bài viết
                        </a>
                        <a href="{{ Route::has('admin.banners.index') ? route('admin.banners.index') : '#' }}"
                           class="list-group-item list-group-item-action">
                            <i class="bi bi-images me-2"></i> Banner trang chủ
                        </a>
                        <a href="{{ Route::has('admin.reports.index') ? route('admin.reports.index') : '#' }}"
                           class="list-group-item list-group-item-action">
                            <i class="bi bi-bar-chart-line me-2"></i> Thống kê doanh thu
                        </a>
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

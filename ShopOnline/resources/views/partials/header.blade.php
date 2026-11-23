@php
    $categories = $menuCategories ?? collect();
@endphp

<header class="sticky-top shadow-sm bg-white">
    {{-- Thanh thông báo nhỏ phía trên --}}
    <div class="bg-dark text-white py-1 small">
        <div class="container d-flex justify-content-between align-items-center">
            <span><i class="bi bi-box-seam me-1"></i> Chuyên mô hình chính hãng &amp; thẻ bài sưu tầm</span>
            <div class="d-none d-md-flex gap-3">
                <a href="{{ Route::has('pages.about') ? route('pages.about') : '#' }}" class="text-white-50 text-decoration-none">Giới thiệu</a>
                <a href="{{ Route::has('pages.contact') ? route('pages.contact') : '#' }}" class="text-white-50 text-decoration-none">Liên hệ</a>
            </div>
        </div>
    </div>

    {{-- Thanh điều hướng chính --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom" aria-label="Điều hướng chính">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary d-flex align-items-center gap-2"
               href="{{ Route::has('home') ? route('home') : url('/') }}">
                <i class="bi bi-stars"></i>
                <span>Chợ Figure &amp; Thẻ</span>
            </a>

            <button class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#mainNavbar"
                    aria-controls="mainNavbar"
                    aria-expanded="false"
                    aria-label="Bật/tắt menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                {{-- Menu danh mục động từ CSDL --}}
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active fw-semibold' : '' }}"
                           href="{{ Route::has('home') ? route('home') : url('/') }}">
                            Trang chủ
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active fw-semibold' : '' }}"
                           href="{{ Route::has('products.all') ? route('products.all') : route('products.search') }}">
                            Sản Phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('coupons.*') ? 'active fw-semibold' : '' }}"
                           href="{{ Route::has('coupons.index') ? route('coupons.index') : '#' }}">
                            Phiếu Giảm Giá
                        </a>
                    </li>
                </ul>

                {{-- Form tìm kiếm (A4) --}}
                <form class="d-flex me-lg-3 my-2 my-lg-0 storefront-search-form"
                      role="search"
                      method="GET"
                      action="{{ Route::has('products.search') ? route('products.search') : '#' }}">
                    <div class="input-group input-group-sm">
                        <input class="form-control"
                               type="search"
                               name="q"
                               value="{{ request('q') }}"
                               placeholder="Tìm mô hình, thẻ bài..."
                               aria-label="Tìm kiếm sản phẩm"
                               maxlength="120">
                        <button class="btn btn-outline-primary" type="submit" aria-label="Tìm kiếm">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>

                {{-- Nút Giỏ hàng (TV3) & Tài khoản (TV1/TV3) --}}
                <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                    @auth
                        <form id="headerImageSearchForm"
                              class="d-none"
                              method="POST"
                              enctype="multipart/form-data"
                              action="{{ route('products.image-search') }}">
                            @csrf
                            <input id="headerImageSearchInput" type="file" name="image" accept="image/jpeg,image/png,image/webp">
                        </form>
                        <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                id="headerImageSearchButton"
                                title="Tìm sản phẩm bằng ảnh">
                            <i class="bi bi-camera"></i>
                            <span class="d-none d-xl-inline ms-1">Tìm bằng ảnh</span>
                        </button>
                    @else
                        <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                title="Đăng nhập để tìm bằng ảnh"
                                onclick="window.location.href='{{ route('login') }}'">
                            <i class="bi bi-camera"></i>
                        </button>
                    @endauth

                    <button type="button"
                            class="btn btn-outline-secondary btn-sm"
                            id="themeToggle"
                            aria-label="Chuyển chế độ sáng/tối"
                            title="Chế độ sáng/tối">
                        <i class="bi bi-moon-stars"></i>
                    </button>

                    <a href="{{ Route::has('cart.index') ? route('cart.index') : '#' }}"
                       class="btn btn-outline-dark btn-sm position-relative btn-cart-animate">
                        <i class="bi bi-cart3 me-1"></i> Giỏ hàng
                    </a>

                    @auth
                        @can('users.manage')
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-speedometer2 me-1"></i> Quản trị
                            </a>
                        @else
                            <a href="{{ route('account.index') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-person-circle me-1"></i> Tài khoản
                            </a>
                        @endcan
                    @else
                        <a href="{{ Route::has('login') ? route('login') : '#' }}"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>

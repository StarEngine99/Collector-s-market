@extends('layouts.app')

@section('title', 'Trang chủ - Chợ Figure & Thẻ Bài')

@section('content')
    {{-- 1. Banner trượt --}}
    <section class="mb-5" aria-label="Banner khuyến mãi">
        @if ($banners->isNotEmpty())
            <div id="homeBannerCarousel" class="carousel slide rounded-3 overflow-hidden shadow-sm" data-bs-ride="carousel">
                @if ($banners->count() > 1)
                    <div class="carousel-indicators">
                        @foreach ($banners as $index => $banner)
                            <button type="button"
                                    data-bs-target="#homeBannerCarousel"
                                    data-bs-slide-to="{{ $index }}"
                                    class="{{ $index === 0 ? 'active' : '' }}"
                                    aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-label="Banner {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif

                <div class="carousel-inner">
                    @foreach ($banners as $index => $banner)
                        @php
                            $bannerImg = str_starts_with($banner->DuongDanHinhAnh, 'http')
                                ? $banner->DuongDanHinhAnh
                                : asset($banner->DuongDanHinhAnh);
                            $hasTarget = !empty($banner->DuongDanDich)
                                && (str_starts_with($banner->DuongDanDich, '/') || str_starts_with($banner->DuongDanDich, 'http'));
                        @endphp
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            @if ($hasTarget)
                                <a href="{{ $banner->DuongDanDich }}" class="d-block">
                            @endif
                                <img src="{{ $bannerImg }}"
                                     class="d-block w-100 object-fit-cover"
                                     style="max-height: 400px; min-height: 200px;"
                                     alt="{{ $banner->TieuDe }}">
                            @if ($hasTarget)
                                </a>
                            @endif

                            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded px-3 py-2">
                                <h2 class="h5 mb-0">{{ $banner->TieuDe }}</h2>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($banners->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Trước</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#homeBannerCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Sau</span>
                    </button>
                @endif
            </div>
        @else
            <div class="p-4 p-md-5 rounded-3 bg-primary bg-gradient text-white shadow-sm">
                <h1 class="h3 fw-bold mb-2">Chào mừng đến với Chợ Figure &amp; Thẻ Bài</h1>
                <p class="mb-0 opacity-75">Khám phá các mẫu mô hình chính hãng, thẻ bài sưu tầm và phụ kiện bảo quản.</p>
            </div>
        @endif
    </section>

    {{-- 2. Sản phẩm nổi bật --}}
    <section class="mb-5" aria-labelledby="featured-heading">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 id="featured-heading" class="h4 fw-bold mb-0">
                <i class="bi bi-star-fill text-warning me-2"></i>Sản phẩm nổi bật
            </h2>
        </div>

        @if ($featuredProducts->isNotEmpty())
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                @foreach ($featuredProducts as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div class="alert alert-light border text-muted mb-0">
                Hiện chưa có sản phẩm nổi bật nào.
            </div>
        @endif
    </section>

    {{-- 3. Sản phẩm mới --}}
    <section class="mb-3" aria-labelledby="newest-heading">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 id="newest-heading" class="h4 fw-bold mb-0">
                <i class="bi bi-lightning-charge-fill text-primary me-2"></i>Sản phẩm mới
            </h2>
            <a href="{{ Route::has('products.search') ? route('products.search', ['sort' => 'newest']) : '#' }}"
               class="btn btn-outline-primary btn-sm">
                Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        @if ($newProducts->isNotEmpty())
            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3">
                @foreach ($newProducts as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <div class="alert alert-light border text-muted mb-0">
                Hiện chưa có sản phẩm mới nào.
            </div>
        @endif
    </section>
@endsection

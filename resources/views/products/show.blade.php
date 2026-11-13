@extends('layouts.app')

@section('title', $product->TenSanPham . ' - Chợ Figure & Thẻ Bài')

@section('content')
    @php
        $inStock = $product->TrangThai === 'active' && (int) $product->SoLuongTon > 0;
        $firstImage = $images->first();
        $mainImageUrl = $firstImage !== null
            ? (str_starts_with($firstImage->DuongDanHinhAnh, 'http') ? $firstImage->DuongDanHinhAnh : asset($firstImage->DuongDanHinhAnh))
            : null;
    @endphp

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item">
                <a href="{{ Route::has('home') ? route('home') : url('/') }}" class="text-decoration-none">Trang chủ</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ Route::has('categories.show') ? route('categories.show', $product->DuongDanDanhMuc) : '#' }}"
                   class="text-decoration-none">
                    {{ $product->TenDanhMuc }}
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->TenSanPham }}</li>
        </ol>
    </nav>

    {{-- Chi tiết sản phẩm --}}
    <div class="card border-0 shadow-sm mb-5">
        <div class="card-body p-3 p-md-4">
            <div class="row g-4">
                {{-- Cột ảnh sản phẩm --}}
                <div class="col-12 col-md-5">
                    <div class="border rounded bg-white overflow-hidden mb-2 text-center">
                        @if ($mainImageUrl)
                            <img id="mainProductImage"
                                 src="{{ $mainImageUrl }}"
                                 alt="{{ $firstImage->MoTaHinhAnh ?? $product->TenSanPham }}"
                                 class="img-fluid w-100 object-fit-contain"
                                 style="max-height: 420px;">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary"
                                 style="height: 360px;">
                                <i class="bi bi-image fs-1"></i>
                            </div>
                        @endif
                    </div>

                    @if ($images->count() > 1)
                        <div class="d-flex gap-2 overflow-auto pb-1">
                            @foreach ($images as $index => $image)
                                @php
                                    $thumbUrl = str_starts_with($image->DuongDanHinhAnh, 'http')
                                        ? $image->DuongDanHinhAnh
                                        : asset($image->DuongDanHinhAnh);
                                @endphp
                                <button type="button"
                                        class="btn p-0 border rounded overflow-hidden flex-shrink-0 product-thumb-btn {{ $index === 0 ? 'border-primary border-2' : '' }}"
                                        data-image-src="{{ $thumbUrl }}"
                                        data-image-alt="{{ $image->MoTaHinhAnh ?? $product->TenSanPham }}"
                                        aria-label="Xem ảnh {{ $index + 1 }}">
                                    <img src="{{ $thumbUrl }}"
                                         alt="{{ $image->MoTaHinhAnh ?? $product->TenSanPham }}"
                                         style="width: 68px; height: 68px; object-fit: cover;">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Cột thông tin & Thêm vào giỏ --}}
                <div class="col-12 col-md-7 d-flex flex-column">
                    <div class="mb-2 d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ Route::has('categories.show') ? route('categories.show', $product->DuongDanDanhMuc) : '#' }}"
                           class="badge bg-primary-subtle text-primary text-decoration-none">
                            {{ $product->TenDanhMuc }}
                        </a>
                        <span class="text-muted small">SKU: {{ $product->MaSKU }}</span>
                    </div>

                    <h1 class="h3 fw-bold mb-3">{{ $product->TenSanPham }}</h1>

                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="d-flex align-items-baseline gap-3 flex-wrap">
                            <span class="h3 fw-bold text-danger mb-0">
                                {{ number_format((float) ($product->GiaSauGiam ?? $product->Gia), 0, ',', '.') }} đ
                            </span>
                            @if (!empty($product->CoGiamGia))
                                <span class="text-muted text-decoration-line-through">
                                    {{ number_format((float) $product->GiaGoc, 0, ',', '.') }} đ
                                </span>
                                @if (($product->PhanTramGiam ?? 0) > 0)
                                    <span class="badge bg-danger">-{{ $product->PhanTramGiam }}%</span>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <span class="fw-semibold me-2">Tình trạng:</span>
                        @if ($inStock)
                            <span class="text-success fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i>Còn hàng ({{ (int) $product->SoLuongTon }} sản phẩm)
                            </span>
                        @else
                            <span class="text-danger fw-semibold">
                                <i class="bi bi-x-circle-fill me-1"></i>Tạm hết hàng
                            </span>
                        @endif
                    </div>

                    {{-- Form Thêm vào giỏ (gọi tới route giỏ hàng của TV3) --}}
                    <form method="POST"
                          action="{{ Route::has('cart.add') ? route('cart.add') : '#' }}"
                          class="mb-4">
                        @csrf
                        <input type="hidden" name="MaSanPham" value="{{ $product->MaSanPham }}">

                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <label for="quantityInput" class="col-form-label fw-semibold">Số lượng:</label>
                            </div>
                            <div class="col-4 col-sm-3 col-md-2">
                                <input id="quantityInput"
                                       type="number"
                                       name="SoLuong"
                                       class="form-control text-center"
                                       value="1"
                                       min="1"
                                       max="{{ max(1, (int) $product->SoLuongTon) }}"
                                       {{ $inStock ? '' : 'disabled' }}
                                       required>
                            </div>
                            <div class="col-12 col-sm-auto">
                                <button type="submit"
                                        class="btn btn-primary w-100 px-4"
                                        {{ $inStock ? '' : 'disabled' }}>
                                    <i class="bi bi-cart-plus me-1"></i>
                                    {{ $inStock ? 'Thêm vào giỏ' : 'Hết hàng' }}
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="border-top pt-3 mt-auto">
                        <h2 class="h6 fw-bold mb-2">Mô tả sản phẩm</h2>
                        @if (!empty($product->MoTa))
                            <div class="text-body-secondary">{!! nl2br(e($product->MoTa)) !!}</div>
                        @else
                            <p class="text-muted small mb-0">Đang cập nhật mô tả cho sản phẩm này.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sản phẩm liên quan --}}
    <section aria-labelledby="related-heading">
        <h2 id="related-heading" class="h4 fw-bold mb-3">
            <i class="bi bi-collection me-2 text-primary"></i>Sản phẩm liên quan
        </h2>

        @if ($relatedProducts->isNotEmpty())
            <div class="row row-cols-2 row-cols-md-4 g-3">
                @foreach ($relatedProducts as $related)
                    @include('partials.product-card', ['product' => $related])
                @endforeach
            </div>
        @else
            <div class="alert alert-light border text-muted mb-0">
                Chưa có sản phẩm liên quan khác trong danh mục này.
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.product-thumb-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                const mainImg = document.getElementById('mainProductImage');
                if (!mainImg) {
                    return;
                }

                mainImg.src = button.dataset.imageSrc || mainImg.src;
                mainImg.alt = button.dataset.imageAlt || mainImg.alt;

                document.querySelectorAll('.product-thumb-btn').forEach(function (item) {
                    item.classList.remove('border-primary', 'border-2');
                });
                button.classList.add('border-primary', 'border-2');
            });
        });
    </script>
@endpush

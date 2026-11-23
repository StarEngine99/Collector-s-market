@extends('layouts.app')

@section('title', $product->TenSanPham . ' - Chợ Figure & Thẻ Bài')

@section('content')
    @php
        $inStock = $product->TrangThai === 'active' && (int) $product->SoLuongTon > 0;
        $firstImage = $images->first();
        $mainImageUrl = $firstImage !== null
            ? (str_starts_with($firstImage->DuongDanHinhAnh, 'http') ? $firstImage->DuongDanHinhAnh : asset($firstImage->DuongDanHinhAnh))
            : null;
        $allDetailImages = $images->map(function ($img) {
            $url = str_starts_with($img->DuongDanHinhAnh, 'http') ? $img->DuongDanHinhAnh : asset($img->DuongDanHinhAnh);
            return [
                'src' => $url,
                'alt' => $img->MoTaHinhAnh ?? ''
            ];
        })->values();
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
                    <div class="border rounded bg-white overflow-hidden mb-2 text-center position-relative product-detail-image-box"
                         id="detailMainImageBox"
                         data-images='@json($allDetailImages)'
                         data-current-index="0">
                        @if ($mainImageUrl)
                            <img id="mainProductImage"
                                 src="{{ $mainImageUrl }}"
                                 alt="{{ $firstImage->MoTaHinhAnh ?? $product->TenSanPham }}"
                                 class="img-fluid w-100 object-fit-contain product-detail-main-img"
                                 style="max-height: 420px;">
                        @else
                            <div class="d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary"
                                 style="height: 360px;">
                                <i class="bi bi-image fs-1"></i>
                            </div>
                        @endif

                        @if ($images->count() > 1)
                            <!-- Nút chuyển ảnh trước < -->
                            <button type="button"
                                    id="btnDetailPrev"
                                    class="btn-detail-nav btn-detail-prev"
                                    aria-label="Ảnh trước">
                                <i class="bi bi-chevron-left"></i>
                            </button>

                            <!-- Nút chuyển ảnh tiếp theo > -->
                            <button type="button"
                                    id="btnDetailNext"
                                    class="btn-detail-nav btn-detail-next"
                                    aria-label="Ảnh tiếp theo">
                                <i class="bi bi-chevron-right"></i>
                            </button>

                            <!-- Chỉ số ảnh (1 / 4) -->
                            <div class="position-absolute bottom-0 end-0 m-2 px-2 py-0.5 rounded-pill bg-dark bg-opacity-75 text-white small detail-counter" id="detailImageCounter">
                                1 / {{ $images->count() }}
                            </div>
                        @endif
                    </div>

                    @if ($images->count() > 1)
                        <div class="d-flex gap-2 overflow-auto pb-1" id="detailThumbnailStrip">
                            @foreach ($images as $index => $image)
                                @php
                                    $thumbUrl = str_starts_with($image->DuongDanHinhAnh, 'http')
                                        ? $image->DuongDanHinhAnh
                                        : asset($image->DuongDanHinhAnh);
                                @endphp
                                <button type="button"
                                        class="btn p-0 border rounded overflow-hidden flex-shrink-0 product-thumb-btn {{ $index === 0 ? 'border-primary border-2 active' : '' }}"
                                        data-index="{{ $index }}"
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

                    <h1 class="h3 fw-bold mb-2">{{ $product->TenSanPham }}</h1>

                    {{-- Đánh giá sao & Đã bán (Yêu cầu 3 & 5) --}}
                    <div class="d-flex align-items-center gap-2 text-muted small mb-3">
                        <div class="d-flex align-items-center text-warning">
                            @for ($s = 1; $s <= 5; $s++)
                                <i class="bi bi-star{{ $s <= round($avgRating ?? 5) ? '-fill' : '' }}"></i>
                            @endfor
                            <span class="ms-1 fw-bold text-dark">{{ $avgRating ?? 5.0 }}</span>
                        </div>
                        <span>·</span>
                        <a href="#reviewsSection" class="text-decoration-none text-muted">{{ $reviewsCount ?? 0 }} đánh giá</a>
                        <span>·</span>
                        <span class="text-secondary">Đã bán <strong>{{ $soldCount ?? 1 }}</strong></span>
                    </div>

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

                    <form method="POST"
                          action="{{ route('products.favorite', $product->MaSanPham) }}"
                          class="d-inline mb-4">
                        @csrf
                        <button type="submit"
                                class="btn {{ !empty($isFavorited) ? 'btn-danger' : 'btn-outline-danger' }} px-3 d-flex align-items-center justify-content-center gap-1"
                                title="Yêu thích sản phẩm">
                            <i class="bi {{ !empty($isFavorited) ? 'bi-heart-fill' : 'bi-heart' }}"></i>
                            <span>{{ !empty($isFavorited) ? 'Đã yêu thích' : 'Yêu thích' }}</span>
                        </button>
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

    {{-- Phần Đánh giá & Bình luận (Yêu cầu 3: 1-5 sao, bình luận) --}}
    <section id="reviewsSection" class="card border-0 shadow-sm mb-5">
        <div class="card-body p-4">
            <h2 class="h5 fw-bold mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-star-fill text-warning"></i>
                <span>Đánh giá sản phẩm ({{ $reviewsCount ?? 0 }})</span>
            </h2>

            <div class="row g-4 mb-4">
                {{-- Tổng quan điểm số --}}
                <div class="col-12 col-md-4 text-center border-end-md pb-3 pb-md-0">
                    <div class="display-4 fw-bold text-warning mb-1">{{ $avgRating ?? 5.0 }}<span class="fs-4 text-muted">/5</span></div>
                    <div class="text-warning mb-2">
                        @for ($s = 1; $s <= 5; $s++)
                            <i class="bi bi-star{{ $s <= round($avgRating ?? 5) ? '-fill' : '' }} fs-5"></i>
                        @endfor
                    </div>
                    <div class="text-muted small">Dựa trên {{ $reviewsCount ?? 0 }} lượt đánh giá của khách hàng</div>
                </div>

                {{-- Form gửi đánh giá mới --}}
                <div class="col-12 col-md-8">
                    @auth
                        <div class="bg-light p-3 rounded-3 border">
                            <h3 class="h6 fw-bold mb-2">Viết đánh giá của bạn</h3>
                            <form method="POST" action="{{ route('products.reviews.store', $product->MaSanPham) }}">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-muted mb-1">Chọn số sao đánh giá:</label>
                                    <div class="star-rating-select d-flex gap-2 text-warning fs-4 cursor-pointer" id="starRatingSelect">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star-fill star-option" data-value="{{ $i }}" style="cursor: pointer;"></i>
                                        @endfor
                                    </div>
                                    <input type="hidden" name="SoSao" id="inputSoSao" value="5">
                                </div>

                                <div class="mb-3">
                                    <textarea name="NoiDung"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Chia sẻ cảm nhận về chất lượng mô hình/thẻ bài, độ hoàn thiện, đóng gói..."
                                              required></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                    <i class="bi bi-send me-1"></i>Gửi đánh giá
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="bg-light p-4 rounded-3 border text-center">
                            <p class="text-muted mb-2">Bạn cần đăng nhập để viết đánh giá cho sản phẩm này.</p>
                            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm px-4">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập ngay
                            </a>
                        </div>
                    @endauth
                </div>
            </div>

            {{-- Danh sách đánh giá đã có --}}
            <div class="border-top pt-4">
                <h3 class="h6 fw-bold mb-3">Tất cả nhận xét</h3>
                @if (!empty($reviews) && $reviews->isNotEmpty())
                    <div class="d-flex flex-column gap-3">
                        @foreach ($reviews as $review)
                            <div class="p-3 bg-white rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                                             style="width: 36px; height: 36px; font-size: 14px;">
                                            {{ strtoupper(substr($review->HoTen ?? 'K', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold small">{{ $review->HoTen ?? 'Khách hàng' }}</div>
                                            <div class="text-warning" style="font-size: 12px;">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= (int)$review->SoSao ? '-fill' : '' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-muted small" style="font-size: 11px;">
                                        {{ \Carbon\Carbon::parse($review->NgayTao)->diffForHumans() }}
                                    </div>
                                </div>
                                <p class="mb-0 text-secondary small">{{ $review->NoiDung }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted text-center py-4 bg-light rounded-3">
                        <i class="bi bi-chat-dots fs-3 d-block mb-1"></i>
                        <span>Chưa có đánh giá nào cho sản phẩm này. Hãy là người đầu tiên đánh giá!</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

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
        document.addEventListener('DOMContentLoaded', function () {
            const box = document.getElementById('detailMainImageBox');
            const mainImg = document.getElementById('mainProductImage');
            const counter = document.getElementById('detailImageCounter');
            const prevBtn = document.getElementById('btnDetailPrev');
            const nextBtn = document.getElementById('btnDetailNext');
            const thumbs = document.querySelectorAll('.product-thumb-btn');

            if (!box || !mainImg) return;

            let images = [];
            try {
                images = JSON.parse(box.dataset.images || '[]');
            } catch (e) {
                images = [];
            }
            if (!images.length) return;

            let currentIndex = 0;

            function updateDetailImage(index, animate = true) {
                if (index < 0) index = images.length - 1;
                if (index >= images.length) index = 0;
                currentIndex = index;
                box.dataset.currentIndex = currentIndex;

                const targetData = images[currentIndex];
                if (!targetData) return;

                if (animate) {
                    mainImg.style.opacity = '0';
                    mainImg.style.transform = 'scale(0.97)';
                    setTimeout(() => {
                        mainImg.src = targetData.src;
                        mainImg.alt = targetData.alt || mainImg.alt;
                        mainImg.style.opacity = '1';
                        mainImg.style.transform = 'scale(1)';
                    }, 160);
                } else {
                    mainImg.src = targetData.src;
                    mainImg.alt = targetData.alt || mainImg.alt;
                }

                if (counter) {
                    counter.textContent = `${currentIndex + 1} / ${images.length}`;
                }

                // Cập nhật viền active cho ảnh thu nhỏ (thumbnail)
                thumbs.forEach((th, i) => {
                    const isActive = i === currentIndex;
                    th.classList.toggle('border-primary', isActive);
                    th.classList.toggle('border-2', isActive);
                    th.classList.toggle('active', isActive);
                    if (isActive) {
                        th.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    }
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    updateDetailImage(currentIndex - 1);
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    updateDetailImage(currentIndex + 1);
                });
            }

            thumbs.forEach((thumb, idx) => {
                thumb.addEventListener('click', (e) => {
                    e.preventDefault();
                    updateDetailImage(idx);
                });
            });

            // Hỗ trợ phím mũi tên bàn phím Trái / Phải
            window.addEventListener('keydown', (e) => {
                if (['ArrowLeft'].includes(e.key)) {
                    updateDetailImage(currentIndex - 1);
                } else if (['ArrowRight'].includes(e.key)) {
                    updateDetailImage(currentIndex + 1);
                }
            });
        });
    </script>
@endpush

@php
    $detailUrl = Route::has('products.show')
        ? route('products.show', $product->DuongDan)
        : '#';

    // Lấy toàn bộ danh sách ảnh (nếu có từ DanhSachHinhAnh hoặc fallback HinhAnhDaiDien)
    $rawImages = collect($product->DanhSachHinhAnh ?? []);
    if ($rawImages->isEmpty() && !empty($product->HinhAnhDaiDien)) {
        $rawImages = collect([$product->HinhAnhDaiDien]);
    }
    $imagesList = $rawImages->map(function ($img) {
        $path = is_object($img) ? $img->DuongDanHinhAnh : $img;
        return str_starts_with($path, 'http') ? $path : asset($path);
    })->values();

    $imageUrl = $imagesList->first() ?? null;
    $hasMultiple = $imagesList->count() > 1;
@endphp

<div class="col">
    <div class="card h-100 shadow-sm product-card" data-has-multiple="{{ $hasMultiple ? '1' : '0' }}" style="border: 1px solid rgba(13,110,253,.12);">
        <div class="position-relative bg-white rounded-top overflow-hidden product-card-image-wrap"
             data-images='@json($imagesList)'
             data-current-index="0">
            
            <a href="{{ $detailUrl }}" class="text-decoration-none d-block overflow-hidden product-card-image-link">
                @if ($imageUrl)
                    <img src="{{ $imageUrl }}"
                         class="card-img-top object-fit-contain product-card-img"
                         style="height: 210px;"
                         alt="{{ $product->MoTaHinhAnh ?? $product->TenSanPham }}">
                @else
                    <div class="d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary"
                         style="height: 210px;">
                        <i class="bi bi-image fs-1"></i>
                    </div>
                @endif
            </a>

            @if ($hasMultiple)
                <!-- Nút chuyển ảnh trước < -->
                <button type="button"
                        class="btn-card-nav btn-card-prev"
                        aria-label="Ảnh trước">
                    <i class="bi bi-chevron-left"></i>
                </button>

                <!-- Nút chuyển ảnh tiếp theo > -->
                <button type="button"
                        class="btn-card-nav btn-card-next"
                        aria-label="Ảnh tiếp theo">
                    <i class="bi bi-chevron-right"></i>
                </button>

                <!-- Dấu chấm chỉ báo số lượng ảnh -->
                <div class="card-dots position-absolute bottom-0 start-50 translate-middle-x mb-2 d-flex gap-1 py-1 px-2 rounded-pill">
                    @foreach ($imagesList as $idx => $img)
                        <span class="card-dot {{ $idx === 0 ? 'active' : '' }}" data-idx="{{ $idx }}"></span>
                    @endforeach
                </div>
            @endif

            <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1 card-badges" style="z-index: 4;">
                @if (!empty($product->NoiBat))
                    <span class="badge bg-warning text-dark">Nổi bật</span>
                @endif
                @if (!empty($product->CoGiamGia) && ($product->PhanTramGiam ?? 0) > 0)
                    <span class="badge bg-danger">-{{ $product->PhanTramGiam }}%</span>
                @endif
            </div>
            @if (!empty($product->LaHangMoi))
                <span class="badge bg-success position-absolute top-0 end-0 m-2" style="z-index: 4;">Hàng Mới</span>
            @endif
        </div>

        <div class="card-body d-flex flex-column">
            <div class="small text-muted mb-1">{{ $product->TenDanhMuc ?? '' }}</div>

            <h3 class="h6 card-title mb-2">
                <a href="{{ $detailUrl }}" class="text-dark text-decoration-none product-title-link">
                    {{ $product->TenSanPham }}
                </a>
            </h3>

            <div class="mt-auto">
                <div class="d-flex align-items-baseline gap-2 flex-wrap mb-1">
                    <span class="fw-bold text-danger fs-6">
                        {{ number_format((float) ($product->GiaSauGiam ?? $product->Gia), 0, ',', '.') }} đ
                    </span>
                    @if (!empty($product->CoGiamGia))
                        <span class="text-muted text-decoration-line-through small">
                            {{ number_format((float) $product->GiaGoc, 0, ',', '.') }} đ
                        </span>
                    @endif
                </div>

                {{-- Đánh giá và số lượng đã bán thực tế --}}
                <div class="d-flex justify-content-between align-items-center small text-muted pt-1 border-top mt-1" style="font-size: 11px;">
                    @if (($product->SoDanhGia ?? 0) > 0)
                        <div class="d-flex align-items-center text-warning">
                            <i class="bi bi-star-fill me-1"></i>
                            <span class="fw-bold text-dark me-1 product-card-rating">{{ number_format((float) $product->DiemDanhGia, 1) }}</span>
                            <span class="text-muted">({{ $product->SoDanhGia }})</span>
                        </div>
                    @else
                        <div class="d-flex align-items-center text-secondary" aria-label="Chưa có đánh giá">
                            @for ($s = 0; $s < 5; $s++)
                                <i class="bi bi-star me-1"></i>
                            @endfor
                        </div>
                    @endif
                    @if (($product->DaBan ?? 0) > 0)
                        <div class="text-secondary product-card-sold">
                            Đã bán {{ $product->DaBan }}
                        </div>
                    @endif
                </div>

                <div class="mt-1 small">
                    @if ((int) $product->SoLuongTon > 0)
                        <span class="text-success"><i class="bi bi-check-circle me-1"></i>Còn hàng</span>
                    @else
                        <span class="text-secondary"><i class="bi bi-x-circle me-1"></i>Tạm hết hàng</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

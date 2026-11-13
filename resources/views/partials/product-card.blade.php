@php
    $detailUrl = Route::has('products.show')
        ? route('products.show', $product->DuongDan)
        : '#';
    $imageUrl = !empty($product->HinhAnhDaiDien)
        ? (str_starts_with($product->HinhAnhDaiDien, 'http') ? $product->HinhAnhDaiDien : asset($product->HinhAnhDaiDien))
        : null;
@endphp

<div class="col">
    <div class="card h-100 shadow-sm border-0 product-card">
        <a href="{{ $detailUrl }}" class="text-decoration-none position-relative bg-white rounded-top overflow-hidden d-block">
            @if ($imageUrl)
                <img src="{{ $imageUrl }}"
                     class="card-img-top object-fit-cover"
                     style="height: 210px;"
                     alt="{{ $product->MoTaHinhAnh ?? $product->TenSanPham }}">
            @else
                <div class="d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary"
                     style="height: 210px;">
                    <i class="bi bi-image fs-1"></i>
                </div>
            @endif

            <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1">
                @if (!empty($product->NoiBat))
                    <span class="badge bg-warning text-dark">Nổi bật</span>
                @endif
                @if (!empty($product->CoGiamGia) && ($product->PhanTramGiam ?? 0) > 0)
                    <span class="badge bg-danger">-{{ $product->PhanTramGiam }}%</span>
                @endif
            </div>
        </a>

        <div class="card-body d-flex flex-column">
            <div class="small text-muted mb-1">{{ $product->TenDanhMuc ?? '' }}</div>

            <h3 class="h6 card-title mb-2">
                <a href="{{ $detailUrl }}" class="text-dark text-decoration-none stretched-link">
                    {{ $product->TenSanPham }}
                </a>
            </h3>

            <div class="mt-auto">
                <div class="d-flex align-items-baseline gap-2 flex-wrap">
                    <span class="fw-bold text-danger fs-6">
                        {{ number_format((float) ($product->GiaSauGiam ?? $product->Gia), 0, ',', '.') }} đ
                    </span>
                    @if (!empty($product->CoGiamGia))
                        <span class="text-muted text-decoration-line-through small">
                            {{ number_format((float) $product->GiaGoc, 0, ',', '.') }} đ
                        </span>
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

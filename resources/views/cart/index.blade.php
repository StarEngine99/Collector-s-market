@extends('layouts.app')

@section('title', 'Giỏ hàng - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">
                <i class="bi bi-cart3 text-primary me-2"></i>Giỏ hàng của bạn
            </h1>
            <p class="text-muted small mb-0">
                Hiện có <strong>{{ (int) $summary['total_quantity'] }}</strong> sản phẩm trong giỏ hàng
            </p>
        </div>

        @if ($summary['items']->isNotEmpty())
            <form method="POST"
                  action="{{ route('cart.clear') }}"
                  onsubmit="return confirm('Bạn chắc chắn muốn xóa toàn bộ sản phẩm trong giỏ hàng?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Làm trống giỏ
                </button>
            </form>
        @endif
    </div>

    @if ($summary['coupon_error'])
        <div class="alert alert-warning d-flex justify-content-between align-items-center" role="alert">
            <span><i class="bi bi-exclamation-triangle me-1"></i>{{ $summary['coupon_error'] }}</span>
            <form method="POST" action="{{ route('cart.coupon.remove') }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-dark">Gỡ mã</button>
            </form>
        </div>
    @endif

    @if ($summary['items']->isNotEmpty())
        <div class="row g-4">
            {{-- Danh sách sản phẩm trong giỏ --}}
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-center" style="width: 150px;">Số lượng</th>
                                    <th class="text-end">Thành tiền</th>
                                    <th class="text-end">Xóa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($summary['items'] as $item)
                                    @php
                                        $imgUrl = !empty($item->HinhAnhDaiDien)
                                            ? (str_starts_with($item->HinhAnhDaiDien, 'http') ? $item->HinhAnhDaiDien : asset($item->HinhAnhDaiDien))
                                            : null;
                                        $detailUrl = Route::has('products.show')
                                            ? route('products.show', $item->DuongDan)
                                            : '#';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                @if ($imgUrl)
                                                    <img src="{{ $imgUrl }}"
                                                         alt="{{ $item->TenSanPham }}"
                                                         class="rounded border flex-shrink-0"
                                                         style="width: 64px; height: 64px; object-fit: cover;">
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center bg-secondary-subtle text-secondary rounded flex-shrink-0"
                                                         style="width: 64px; height: 64px;">
                                                        <i class="bi bi-image fs-4"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <a href="{{ $detailUrl }}" class="fw-semibold text-dark text-decoration-none d-block">
                                                        {{ $item->TenSanPham }}
                                                    </a>
                                                    <div class="small text-muted">
                                                        SKU: {{ $item->MaSKU }} · {{ $item->TenDanhMuc }}
                                                    </div>
                                                    @if (!$item->ConBan)
                                                        <span class="badge bg-danger mt-1">Tạm hết hàng</span>
                                                    @elseif (!$item->DuTonKho)
                                                        <span class="badge bg-warning text-dark mt-1">
                                                            Chỉ còn {{ (int) $item->SoLuongTon }} sản phẩm trong kho
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <td class="text-end">
                                            <div class="fw-semibold text-danger">
                                                {{ number_format((float) $item->DonGia, 0, ',', '.') }} đ
                                            </div>
                                            @if (!empty($item->CoGiamGia))
                                                <div class="small text-muted text-decoration-line-through">
                                                    {{ number_format((float) $item->GiaGoc, 0, ',', '.') }} đ
                                                </div>
                                            @endif
                                        </td>

                                        <td class="text-center">
                                            <form method="POST"
                                                  action="{{ route('cart.update', $item->MaChiTietGioHang) }}"
                                                  class="d-inline-flex align-items-center gap-1">
                                                @csrf
                                                @method('PATCH')
                                                <input type="number"
                                                       name="SoLuong"
                                                       value="{{ (int) $item->SoLuong }}"
                                                       min="1"
                                                       max="{{ max(1, (int) $item->SoLuongTon) }}"
                                                       class="form-control form-control-sm text-center"
                                                       style="width: 68px;"
                                                       aria-label="Số lượng {{ $item->TenSanPham }}">
                                                <button type="submit"
                                                        class="btn btn-outline-primary btn-sm"
                                                        title="Cập nhật số lượng">
                                                    <i class="bi bi-arrow-repeat"></i>
                                                </button>
                                            </form>
                                        </td>

                                        <td class="text-end fw-bold">
                                            {{ number_format((float) $item->ThanhTien, 0, ',', '.') }} đ
                                        </td>

                                        <td class="text-end">
                                            <form method="POST"
                                                  action="{{ route('cart.remove', $item->MaChiTietGioHang) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-outline-danger btn-sm"
                                                        title="Xóa khỏi giỏ">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Tiếp tục mua sắm
                    </a>
                </div>
            </div>

            {{-- Cột Mã giảm giá & Tóm tắt đơn hàng --}}
            <div class="col-12 col-lg-4">
                {{-- Khung Mã giảm giá --}}
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3 p-md-4">
                        <h2 class="h6 fw-bold mb-3">
                            <i class="bi bi-ticket-perforated text-primary me-1"></i>Mã giảm giá
                        </h2>

                        @if ($summary['coupon'])
                            <div class="d-flex justify-content-between align-items-center bg-success-subtle border border-success-subtle rounded p-2">
                                <div>
                                    <span class="badge bg-success font-monospace">{{ $summary['coupon']->MaUuDai }}</span>
                                    <div class="small text-success fw-semibold mt-1">
                                        Giảm {{ number_format((float) $summary['discount_amount'], 0, ',', '.') }} đ
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Gỡ mã</button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('cart.coupon.apply') }}">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                           name="MaUuDai"
                                           value="{{ old('MaUuDai', $appliedCouponCode) }}"
                                           class="form-control text-uppercase font-monospace @error('MaUuDai') is-invalid @enderror"
                                           placeholder="Nhập mã ưu đãi..."
                                           maxlength="80"
                                           required>
                                    <button type="submit" class="btn btn-outline-primary fw-semibold">
                                        Áp dụng
                                    </button>
                                    @error('MaUuDai')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-text small">Mỗi đơn hàng áp dụng tối đa 01 mã giảm giá.</div>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Khung Tổng tiền --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 p-md-4">
                        <h2 class="h6 fw-bold mb-3">Tóm tắt đơn hàng</h2>

                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Tạm tính (đã áp KM sản phẩm):</span>
                            <span class="fw-semibold">{{ number_format((float) $summary['subtotal'], 0, ',', '.') }} đ</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Giảm giá mã ưu đãi:</span>
                            <span class="text-success fw-semibold">
                                -{{ number_format((float) $summary['discount_amount'], 0, ',', '.') }} đ
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 small">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="fw-semibold">Miễn phí</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fw-bold">Tổng thanh toán:</span>
                            <span class="h4 fw-bold text-danger mb-0">
                                {{ number_format((float) $summary['total'], 0, ',', '.') }} đ
                            </span>
                        </div>

                        @auth
                            @if ($summary['has_invalid_items'])
                                <button type="button" class="btn btn-secondary w-100 py-2" disabled>
                                    Vui lòng điều chỉnh sản phẩm vượt tồn kho
                                </button>
                            @else
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 py-2 fw-semibold">
                                    <i class="bi bi-credit-card me-1"></i>Tiến hành thanh toán
                                </a>
                            @endif
                        @else
                            <div class="alert alert-info small mb-3">
                                <i class="bi bi-info-circle me-1"></i>
                                Giỏ hàng của bạn đang được lưu tạm. Vui lòng đăng nhập để tiến hành đặt hàng (giỏ hàng sẽ tự động được gộp vào tài khoản).
                            </div>
                            <a href="{{ route('login') }}" class="btn btn-primary w-100 py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập để thanh toán
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <i class="bi bi-cart-x text-secondary display-4 d-block mb-3"></i>
                <h2 class="h5 fw-bold mb-2">Giỏ hàng của bạn đang trống</h2>
                <p class="text-muted small mb-4">
                    Hãy khám phá các mẫu mô hình sưu tầm và thẻ bài mới nhất tại cửa hàng.
                </p>
                <a href="{{ route('home') }}" class="btn btn-primary px-4">
                    <i class="bi bi-shop me-1"></i>Khám phá sản phẩm ngay
                </a>
            </div>
        </div>
    @endif
@endsection

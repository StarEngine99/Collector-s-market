@extends('layouts.app')

@section('title', 'Kho Phiếu Giảm Giá - Chợ Figure & Thẻ Bài')

@section('content')
<div class="py-2">
    {{-- Banner tiêu đề --}}
    <div class="p-4 p-md-5 rounded-4 hero-animated-banner text-white shadow-sm mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="display-5"><i class="bi bi-ticket-perforated-fill text-warning"></i></div>
            <div>
                <h1 class="h3 fw-bold mb-1">Kho Phiếu Giảm Giá &amp; Ưu Đãi Đặc Biệt</h1>
                <p class="mb-0 text-white-50">Sưu tầm voucher giảm giá khi mua mô hình Figure và thẻ bài TCG chính hãng.</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
        </div>
    @endif

    {{-- Danh sách Phiếu Giảm Giá --}}
    @if ($coupons->isNotEmpty())
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
            @foreach ($coupons as $coupon)
                @php
                    $isPercentage = $coupon->LoaiGiamGia === 'percent';
                    $isSaved = in_array($coupon->MaPhieuGiamGia, $savedCouponIds, true);
                    $endTime = \Carbon\Carbon::parse($coupon->KetThucLuc);
                @endphp
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm coupon-card position-relative overflow-hidden rounded-4">
                        {{-- Phần dải màu trên đầu coupon --}}
                        <div class="p-3 bg-gradient text-white d-flex justify-content-between align-items-center"
                             style="background: linear-gradient(135deg, #0d6efd 0%, #6366f1 100%);">
                            <div>
                                <span class="badge bg-warning text-dark fw-bold px-2.5 py-1.5 fs-6 shadow-sm">
                                    {{ $isPercentage ? 'GIẢM ' . (float)$coupon->GiaTriGiam . '%' : 'GIẢM ' . number_format((float)$coupon->GiaTriGiam, 0, ',', '.') . ' đ' }}
                                </span>
                            </div>
                            <div class="small text-white-50">
                                <i class="bi bi-clock-history me-1"></i>HSD: {{ $endTime->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column p-3 p-md-4">
                            <div class="mb-3">
                                <div class="text-muted small mb-1">Mã ưu đãi:</div>
                                @if (($coupon->LoaiPhieu ?? 'san_pham') === 'van_chuyen')
                                    <span class="badge bg-info text-dark mb-2">Phiếu vận chuyển</span>
                                @else
                                    <span class="badge bg-primary mb-2">Phiếu sản phẩm</span>
                                @endif
                                <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-3 border">
                                    <span class="font-monospace fw-bold fs-5 text-primary">{{ $coupon->MaUuDai }}</span>
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm btn-copy-code"
                                            data-code="{{ $coupon->MaUuDai }}"
                                            title="Sao chép mã">
                                        <i class="bi bi-clipboard me-1"></i>Sao chép
                                    </button>
                                </div>
                            </div>

                            <div class="small text-muted mb-3 flex-grow-1">
                                <div class="mb-1">
                                    <i class="bi bi-info-circle me-1 text-primary"></i>
                                    Đơn tối thiểu: <strong>{{ number_format((float)$coupon->DonHangToiThieu, 0, ',', '.') }} đ</strong>
                                </div>
                                @if ($coupon->SoLanSuDungToiDaMoiNguoi)
                                    <div>
                                        <i class="bi bi-person-check me-1 text-success"></i>
                                        Tối đa {{ $coupon->SoLanSuDungToiDaMoiNguoi }} lượt/khách
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex gap-2 pt-2 border-top">
                                @auth
                                    @if ($isSaved)
                                        <button type="button" class="btn btn-success btn-sm flex-fill disabled" disabled>
                                            <i class="bi bi-check2-circle me-1"></i>Đã lưu vào ví
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('coupons.claim', $coupon->MaPhieuGiamGia) }}" class="flex-fill">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                                <i class="bi bi-bookmark-plus me-1"></i>Lưu phiếu
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập để lưu
                                    </a>
                                @endauth
                                @if ($isSaved)
                                    <form method="POST" action="{{ route('cart.coupon.apply') }}" class="flex-fill">
                                        @csrf
                                        <input type="hidden" name="MaUuDai" value="{{ $coupon->MaUuDai }}">
                                        <button type="submit" class="btn btn-primary btn-sm w-100">
                                            Dùng phiếu <i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('cart.index') }}" class="btn btn-primary btn-sm flex-fill">
                                        Dùng ngay <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5 bg-white rounded-4 shadow-sm border p-4">
            <i class="bi bi-ticket-perforated fs-1 text-muted d-block mb-2"></i>
            <h3 class="h5 fw-bold text-muted">Hiện tại chưa có phiếu giảm giá nào mới</h3>
            <p class="text-muted small mb-3">Các chương trình ưu đãi sẽ được cập nhật sớm nhất. Vui lòng quay lại sau!</p>
            <a href="{{ route('home') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Về trang chủ mua sắm
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.btn-copy-code').forEach(btn => {
        btn.addEventListener('click', function () {
            const code = this.dataset.code;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(code).then(() => {
                    const original = this.innerHTML;
                    this.innerHTML = '<i class="bi bi-check2 me-1"></i>Đã sao chép';
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-success');
                    setTimeout(() => {
                        this.innerHTML = original;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-primary');
                    }, 2000);
                });
            }
        });
    });
</script>
@endpush

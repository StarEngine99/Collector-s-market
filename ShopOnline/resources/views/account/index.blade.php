@extends('layouts.app')

@section('title', 'Tài khoản cá nhân | Collector\'s Market')

@section('content')
<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold mb-1">Tài khoản cá nhân</h1>
            <p class="text-muted mb-0">Xin chào <strong>{{ $user->HoTen }}</strong> ({{ $user->Email }})</p>
        </div>
        <div class="d-flex gap-2">
            @can('users.manage')
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    <i class="bi bi-speedometer2 me-1"></i> Bảng điều khiển quản trị
                </a>
            @endcan
            <a href="{{ route('account.orders.index') }}" class="btn btn-outline-primary">
                Lịch sử đơn hàng
            </a>
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger">Đăng xuất</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        {{-- Thông tin cá nhân --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <h2 class="h5 fw-bold mb-0">Thông tin cá nhân</h2>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="editProfileButton">
                            <i class="bi bi-pencil me-1"></i>Chỉnh sửa
                        </button>
                    </div>

                    <div id="profileSummary">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div id="accountAvatar"
                                 class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                 style="width:88px;height:88px;background:linear-gradient(135deg,#5368e8,#7c3aed);font-size:28px;overflow:hidden;">
                                {{ mb_strtoupper(mb_substr($user->HoTen, 0, 1)) }}
                            </div>
                            <label for="avatarInput" class="btn btn-sm btn-outline-secondary mb-0">
                                <i class="bi bi-camera me-1"></i>Đổi avatar
                            </label>
                            <input id="avatarInput" type="file" accept="image/*" class="d-none">
                        </div>
                        <div class="mb-2"><span class="text-muted small d-block">Tên</span><strong>{{ $user->HoTen }}</strong></div>
                        <div class="mb-2"><span class="text-muted small d-block">Số điện thoại</span><strong>{{ $user->SoDienThoai ?: 'Chưa cập nhật' }}</strong></div>
                        @php($defaultAddress = $addresses->firstWhere('MacDinh', 1) ?? $addresses->first())
                        <div>
                            <span class="text-muted small d-block">Địa chỉ</span>
                            @if($defaultAddress)
                                <strong>{{ $defaultAddress->DiaChiChiTiet }}, {{ $defaultAddress->PhuongXa }}, {{ $defaultAddress->QuanHuyen }}, {{ $defaultAddress->TinhThanh }}</strong>
                            @else
                                <strong>Chưa có địa chỉ</strong>
                            @endif
                        </div>
                    </div>

                    <form id="profileEditForm"
                          action="{{ route('account.profile.update') }}"
                          method="POST"
                          novalidate
                          class="d-none">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="HoTen" class="form-label fw-semibold">Tên</label>
                            <input type="text" id="HoTen" name="HoTen" value="{{ old('HoTen', $user->HoTen) }}"
                                   class="form-control @error('HoTen') is-invalid @enderror" required maxlength="150">
                            @error('HoTen')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="SoDienThoai" class="form-label fw-semibold">Số điện thoại</label>
                            <input type="text" id="SoDienThoai" name="SoDienThoai"
                                   value="{{ old('SoDienThoai', $user->SoDienThoai) }}"
                                   class="form-control @error('SoDienThoai') is-invalid @enderror"
                                   maxlength="20" placeholder="0901234567">
                            @error('SoDienThoai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Lưu</button>
                            <button type="button" class="btn btn-outline-secondary" id="cancelProfileEdit">Hủy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Đổi mật khẩu --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-3">Đổi mật khẩu</h2>
                    <form action="{{ route('account.password.update') }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="MatKhauHienTai" class="form-label fw-semibold">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                            <input
                                type="password"
                                id="MatKhauHienTai"
                                name="MatKhauHienTai"
                                class="form-control @error('MatKhauHienTai') is-invalid @enderror"
                                required
                            >
                            @error('MatKhauHienTai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="MatKhau" class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input
                                type="password"
                                id="MatKhau"
                                name="MatKhau"
                                class="form-control @error('MatKhau') is-invalid @enderror"
                                required
                                minlength="8"
                                placeholder="Tối thiểu 8 ký tự"
                            >
                            @error('MatKhau')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="MatKhau_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <input
                                type="password"
                                id="MatKhau_confirmation"
                                name="MatKhau_confirmation"
                                class="form-control"
                                required
                                minlength="8"
                            >
                        </div>

                        <button type="submit" class="btn btn-outline-primary">Cập nhật mật khẩu</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3">
            <h2 class="h5 fw-bold mb-0">Sổ địa chỉ giao hàng</h2>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('account.addresses.store') }}" method="POST" class="border rounded p-3 mb-4">
                @csrf
                <h3 class="h6 fw-bold mb-3">Thêm địa chỉ mới</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="new_TenNguoiNhan" class="form-label">Người nhận</label>
                        <input id="new_TenNguoiNhan" name="TenNguoiNhan" class="form-control" required maxlength="150">
                    </div>
                    <div class="col-md-6">
                        <label for="new_SoDienThoai" class="form-label">Số điện thoại</label>
                        <input id="new_SoDienThoai" name="SoDienThoai" class="form-control" required maxlength="30">
                    </div>
                    <div class="col-md-4">
                        <label for="new_TinhThanh" class="form-label">Tỉnh / thành phố</label>
                        <input id="new_TinhThanh" name="TinhThanh" class="form-control" required maxlength="120">
                    </div>
                    <div class="col-md-4">
                        <label for="new_QuanHuyen" class="form-label">Quận / huyện</label>
                        <input id="new_QuanHuyen" name="QuanHuyen" class="form-control" required maxlength="120">
                    </div>
                    <div class="col-md-4">
                        <label for="new_PhuongXa" class="form-label">Phường / xã</label>
                        <input id="new_PhuongXa" name="PhuongXa" class="form-control" required maxlength="120">
                    </div>
                    <div class="col-12">
                        <label for="new_DiaChiChiTiet" class="form-label">Địa chỉ chi tiết</label>
                        <input id="new_DiaChiChiTiet" name="DiaChiChiTiet" class="form-control" required maxlength="255">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input id="new_MacDinh" name="MacDinh" value="1" type="checkbox" class="form-check-input">
                            <label for="new_MacDinh" class="form-check-label">Đặt làm địa chỉ mặc định</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Thêm địa chỉ</button>
            </form>

            <div class="row g-3">
                @forelse($addresses as $address)
                    <div class="col-12">
                        <form action="{{ route('account.addresses.update', $address->MaDiaChi) }}"
                              method="POST" class="border rounded p-3">
                            @csrf
                            @method('PUT')
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                <h3 class="h6 fw-bold mb-0">
                                    Địa chỉ #{{ $address->MaDiaChi }}
                                    @if($address->MacDinh)
                                        <span class="badge bg-primary ms-1">Mặc định</span>
                                    @endif
                                </h3>
                                <div class="d-flex gap-2">
                                    @unless($address->MacDinh)
                                        <button type="submit" form="default-address-{{ $address->MaDiaChi }}"
                                                class="btn btn-sm btn-outline-primary">Đặt mặc định</button>
                                    @endunless
                                    <button type="submit" form="delete-address-{{ $address->MaDiaChi }}"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Bạn có chắc muốn xóa địa chỉ này?');">Xóa</button>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="TenNguoiNhan_{{ $address->MaDiaChi }}" class="form-label">Người nhận</label>
                                    <input id="TenNguoiNhan_{{ $address->MaDiaChi }}" name="TenNguoiNhan"
                                           value="{{ $address->TenNguoiNhan }}" class="form-control" required maxlength="150">
                                </div>
                                <div class="col-md-6">
                                    <label for="SoDienThoai_{{ $address->MaDiaChi }}" class="form-label">Số điện thoại</label>
                                    <input id="SoDienThoai_{{ $address->MaDiaChi }}" name="SoDienThoai"
                                           value="{{ $address->SoDienThoai }}" class="form-control" required maxlength="30">
                                </div>
                                <div class="col-md-4">
                                    <label for="TinhThanh_{{ $address->MaDiaChi }}" class="form-label">Tỉnh / thành phố</label>
                                    <input id="TinhThanh_{{ $address->MaDiaChi }}" name="TinhThanh"
                                           value="{{ $address->TinhThanh }}" class="form-control" required maxlength="120">
                                </div>
                                <div class="col-md-4">
                                    <label for="QuanHuyen_{{ $address->MaDiaChi }}" class="form-label">Quận / huyện</label>
                                    <input id="QuanHuyen_{{ $address->MaDiaChi }}" name="QuanHuyen"
                                           value="{{ $address->QuanHuyen }}" class="form-control" required maxlength="120">
                                </div>
                                <div class="col-md-4">
                                    <label for="PhuongXa_{{ $address->MaDiaChi }}" class="form-label">Phường / xã</label>
                                    <input id="PhuongXa_{{ $address->MaDiaChi }}" name="PhuongXa"
                                           value="{{ $address->PhuongXa }}" class="form-control" required maxlength="120">
                                </div>
                                <div class="col-12">
                                    <label for="DiaChiChiTiet_{{ $address->MaDiaChi }}" class="form-label">Địa chỉ chi tiết</label>
                                    <input id="DiaChiChiTiet_{{ $address->MaDiaChi }}" name="DiaChiChiTiet"
                                           value="{{ $address->DiaChiChiTiet }}" class="form-control" required maxlength="255">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-outline-primary mt-3">Lưu địa chỉ</button>
                        </form>
                        <form id="default-address-{{ $address->MaDiaChi }}"
                              action="{{ route('account.addresses.default', $address->MaDiaChi) }}" method="POST" class="d-none">
                            @csrf
                            @method('PATCH')
                        </form>
                        <form id="delete-address-{{ $address->MaDiaChi }}"
                              action="{{ route('account.addresses.destroy', $address->MaDiaChi) }}" method="POST" class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                @empty
                    <div class="col-12 text-muted">Bạn chưa lưu địa chỉ giao hàng nào.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Yêu thích, đã mua, phiếu giảm và đánh giá --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white pt-3 pb-0">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabFavorites" type="button">Yêu thích</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPurchased" type="button">Đã mua</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCoupons" type="button">Phiếu giảm</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabReviews" type="button">Đã đánh giá</button></li>
            </ul>
        </div>
        <div class="tab-content card-body">
            <div class="tab-pane fade show active" id="tabFavorites">
                @if($favoriteProducts->isEmpty())
                    <div class="text-muted text-center py-3">Bạn chưa yêu thích sản phẩm nào.</div>
                @else
                    <div class="row g-3">
                        @foreach($favoriteProducts as $product)
                            <div class="col-6 col-md-3">
                                <a href="{{ route('products.show', $product->DuongDan) }}" class="text-decoration-none text-dark">
                                    <div class="border rounded p-2 h-100">
                                        <img src="{{ $product->HinhAnh ? asset($product->HinhAnh) : asset('images/no-image.png') }}" class="w-100 rounded mb-2" style="height:120px;object-fit:contain;background-color:#fff;" alt="{{ $product->TenSanPham }}">
                                        <div class="fw-semibold small">{{ $product->TenSanPham }}</div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="tab-pane fade" id="tabPurchased">
                @if($purchasedProducts->isEmpty())
                    <div class="text-muted text-center py-3">Bạn chưa có sản phẩm đã mua.</div>
                @else
                    <div class="row g-3">
                        @foreach($purchasedProducts as $product)
                            <div class="col-6 col-md-3">
                                <a href="{{ route('products.show', $product->DuongDan) }}" class="text-decoration-none text-dark">
                                    <div class="border rounded p-2 h-100">
                                        <img src="{{ $product->HinhAnh ? asset($product->HinhAnh) : asset('images/no-image.png') }}" class="w-100 rounded mb-2" style="height:120px;object-fit:contain;background-color:#fff;" alt="{{ $product->TenSanPham }}">
                                        <div class="fw-semibold small">{{ $product->TenSanPham }}</div>
                                        <div class="text-muted small">Đã mua {{ (int)$product->SoLuongDaMua }}</div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="tab-pane fade" id="tabCoupons">
                @if($ownedCoupons->isEmpty())
                    <div class="text-muted text-center py-3">Bạn chưa sở hữu phiếu giảm giá.</div>
                @else
                    <div class="d-grid gap-2">
                        @foreach($ownedCoupons as $coupon)
                            <div class="border rounded p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <span class="badge bg-primary font-monospace">{{ $coupon->MaUuDai }}</span>
                                    <div class="small text-muted mt-1">
                                        {{ $coupon->LoaiGiamGia === 'percent' ? 'Giảm ' . rtrim(rtrim(number_format((float)$coupon->GiaTriGiam, 2, ',', '.'), '0'), ',') . '%' : 'Giảm ' . number_format((float)$coupon->GiaTriGiam, 0, ',', '.') . ' đ' }}
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('cart.coupon.apply') }}">
                                    @csrf
                                    <input type="hidden" name="MaUuDai" value="{{ $coupon->MaUuDai }}">
                                    <button type="submit" class="btn btn-sm btn-primary">Dùng phiếu</button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="tab-pane fade" id="tabReviews">
                @if($reviewedProducts->isEmpty())
                    <div class="text-muted text-center py-3">Bạn chưa đánh giá sản phẩm nào.</div>
                @else
                    <div class="d-grid gap-3">
                        @foreach($reviewedProducts as $review)
                            <div class="border rounded p-3">
                                <a href="{{ route('products.show', $review->DuongDan) }}" class="fw-semibold text-decoration-none">{{ $review->TenSanPham }}</a>
                                <div class="text-warning my-1">
                                    @for($star=1;$star<=5;$star++)
                                        <i class="bi bi-star{{ $star <= (int)$review->SoSao ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <div class="small text-muted">{{ $review->NoiDung }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Đơn hàng gần đây --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-bold mb-0">Đơn hàng gần đây</h2>
            <a href="{{ route('account.orders.index') }}" class="btn btn-sm btn-link text-decoration-none">Xem tất cả &rarr;</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Thanh toán</th>
                            <th class="text-end">Tổng thanh toán</th>
                            <th class="text-center">Trạng thái đơn</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td class="ps-4 fw-bold font-monospace">
                                    <a href="{{ route('account.orders.show', $order->SoDonHang) }}" class="text-decoration-none">
                                        {{ $order->SoDonHang }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($order->NgayTao)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="small">{{ \App\Support\OrderStatus::paymentMethodLabel($order->PhuongThucThanhToan) }}</span>
                                    <span class="badge {{ \App\Support\OrderStatus::paymentBadgeClass($order->TrangThaiThanhToan) }} ms-1">
                                        {{ \App\Support\OrderStatus::paymentStatusLabel($order->TrangThaiThanhToan) }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    {{ number_format($order->TongThanhToan, 0, ',', '.') }} đ
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ \App\Support\OrderStatus::badgeClass($order->TrangThai) }}">
                                        {{ \App\Support\OrderStatus::label($order->TrangThai) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('account.orders.show', $order->SoDonHang) }}" class="btn btn-sm btn-outline-primary">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Bạn chưa có đơn hàng nào. <a href="{{ route('home') }}">Khám phá sản phẩm ngay</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButton = document.getElementById('editProfileButton');
    const cancelButton = document.getElementById('cancelProfileEdit');
    const summary = document.getElementById('profileSummary');
    const form = document.getElementById('profileEditForm');

    if (editButton && cancelButton && summary && form) {
        editButton.addEventListener('click', function () {
            summary.classList.add('d-none');
            editButton.classList.add('d-none');
            form.classList.remove('d-none');
        });
        cancelButton.addEventListener('click', function () {
            form.classList.add('d-none');
            summary.classList.remove('d-none');
            editButton.classList.remove('d-none');
        });
    }

    const avatarInput = document.getElementById('avatarInput');
    const avatar = document.getElementById('accountAvatar');
    if (avatarInput && avatar) {
        const savedAvatar = localStorage.getItem('shopAvatar');
        if (savedAvatar) {
            avatar.innerHTML = '<img src="' + savedAvatar + '" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">';
        }
        avatarInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function () {
                localStorage.setItem('shopAvatar', reader.result);
                avatar.innerHTML = '<img src="' + reader.result + '" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">';
            };
            reader.readAsDataURL(file);
        });
    }
});
</script>
@endpush


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
            <a href="{{ route('account.orders') }}" class="btn btn-outline-primary">
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
                    <h2 class="h5 fw-bold mb-3">Thông tin cá nhân</h2>
                    <form action="{{ route('account.profile.update') }}" method="POST" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Địa chỉ Email</label>
                            <input type="email" class="form-control bg-light" value="{{ $user->Email }}" disabled readonly>
                            <small class="text-muted">Email dùng để đăng nhập và nhận thông báo đơn hàng.</small>
                        </div>

                        <div class="mb-3">
                            <label for="HoTen" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                id="HoTen"
                                name="HoTen"
                                value="{{ old('HoTen', $user->HoTen) }}"
                                class="form-control @error('HoTen') is-invalid @enderror"
                                required
                                maxlength="150"
                            >
                            @error('HoTen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="SoDienThoai" class="form-label fw-semibold">Số điện thoại</label>
                            <input
                                type="text"
                                id="SoDienThoai"
                                name="SoDienThoai"
                                value="{{ old('SoDienThoai', $user->SoDienThoai) }}"
                                class="form-control @error('SoDienThoai') is-invalid @enderror"
                                maxlength="20"
                                placeholder="0901234567"
                            >
                            @error('SoDienThoai')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
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

    {{-- Đơn hàng gần đây --}}
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-bold mb-0">Đơn hàng gần đây</h2>
            <a href="{{ route('account.orders') }}" class="btn btn-sm btn-link text-decoration-none">Xem tất cả &rarr;</a>
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
                                    <span class="badge bg-{{ \App\Support\OrderStatus::paymentStatusBadgeClass($order->TrangThaiThanhToan) }} ms-1">
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
                                    Bạn chưa có đơn hàng nào. <a href="{{ route('products.index') }}">Khám phá sản phẩm ngay</a>.
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


@extends('layouts.admin')

@section('title', 'Chi tiết khách hàng ' . $customer->HoTen . ' | Quản trị Collector\'s Market')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="{{ route('admin.customers.index') }}">Quản lý khách hàng</a></li>
                <li class="breadcrumb-item active" aria-current="page">#{{ $customer->MaNguoiDung }} - {{ $customer->HoTen }}</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">
            Khách hàng: {{ $customer->HoTen }}
            @if($customer->TrangThai === 'active')
                <span class="badge bg-success fs-6 ms-2">Hoạt động</span>
            @else
                <span class="badge bg-danger fs-6 ms-2">Đã khóa</span>
            @endif
        </h1>
    </div>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.customers.status', $customer->MaNguoiDung) }}"
              method="POST"
              onsubmit="return confirm('Xác nhận thay đổi trạng thái tài khoản {{ $customer->Email }}?');">
            @csrf
            @method('PATCH')
            @if($customer->TrangThai === 'active')
                <input type="hidden" name="TrangThai" value="locked">
                <button type="submit" class="btn btn-danger">Khóa tài khoản</button>
            @else
                <input type="hidden" name="TrangThai" value="active">
                <button type="submit" class="btn btn-success">Mở khóa tài khoản</button>
            @endif
        </form>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">&larr; Danh sách khách hàng</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- Thống kê mua hàng của khách --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tổng số đơn hàng</div>
                <div class="h4 fw-bold mb-0 mt-1">{{ $stats['total_orders'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Đơn hoàn tất</div>
                <div class="h4 fw-bold text-success mb-0 mt-1">{{ $stats['completed_orders'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Đơn đã hủy</div>
                <div class="h4 fw-bold text-danger mb-0 mt-1">{{ $stats['cancelled_orders'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Tổng chi tiêu (không tính đơn hủy)</div>
                <div class="h4 fw-bold text-primary mb-0 mt-1">{{ number_format($stats['total_spent'], 0, ',', '.') }} đ</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    {{-- Thông tin cá nhân & Sổ địa chỉ --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-bold mb-0">Thông tin tài khoản</h2>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="text-muted small d-block">Mã người dùng</span>
                    <strong class="font-monospace">#{{ $customer->MaNguoiDung }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Họ và tên</span>
                    <strong>{{ $customer->HoTen }}</strong>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Email</span>
                    <span>{{ $customer->Email }}</span>
                </div>
                <div class="mb-2">
                    <span class="text-muted small d-block">Số điện thoại</span>
                    <span>{{ $customer->SoDienThoai ?: 'Chưa cập nhật' }}</span>
                </div>
                <div class="mb-0">
                    <span class="text-muted small d-block">Ngày tạo tài khoản</span>
                    <span>{{ \Carbon\Carbon::parse($customer->NgayTao)->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h2 class="h6 fw-bold mb-0">Địa chỉ đã lưu ({{ $addresses->count() }})</h2>
            </div>
            <div class="card-body">
                @forelse($addresses as $address)
                    <div class="pb-3 mb-3 border-bottom last-border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>{{ $address->TenNguoiNhan }}</strong>
                            @if($address->LaMacDinh)
                                <span class="badge bg-info text-dark">Mặc định</span>
                            @endif
                        </div>
                        <div class="small text-muted">{{ $address->SoDienThoai }}</div>
                        <div class="small mt-1">
                            {{ $address->DiaChiChiTiet }}, {{ $address->PhuongXa }}, {{ $address->QuanHuyen }}, {{ $address->TinhThanh }}
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">Khách hàng chưa lưu địa chỉ giao hàng nào.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Lịch sử đơn hàng của khách --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h2 class="h5 fw-bold mb-0">Lịch sử đơn hàng ({{ $orders->count() }})</h2>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Thanh toán</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-end pe-4">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td class="ps-4 fw-bold font-monospace">
                                        <a href="{{ route('admin.orders.show', $order->MaDonHang) }}" class="text-decoration-none">
                                            {{ $order->SoDonHang }}
                                        </a>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($order->NgayTao)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="small">{{ \App\Support\OrderStatus::paymentMethodLabel($order->PhuongThucThanhToan) }}</div>
                                        <span class="badge bg-{{ \App\Support\OrderStatus::paymentStatusBadgeClass($order->TrangThaiThanhToan) }}">
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
                                        <a href="{{ route('admin.orders.show', $order->MaDonHang) }}" class="btn btn-sm btn-outline-primary">
                                            Xem đơn
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        Khách hàng chưa có đơn hàng nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

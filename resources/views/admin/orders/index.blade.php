@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng - Quản trị')

@section('content')
    @php use App\Support\OrderStatus; @endphp

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Quản lý đơn hàng</h1>
            <p class="text-muted small mb-0">Theo dõi, lọc trạng thái, xác nhận thanh toán và cập nhật tiến độ giao hàng</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="searchOrder" class="form-label small fw-semibold">Tìm kiếm</label>
                    <input id="searchOrder"
                           type="text"
                           name="q"
                           value="{{ $keyword }}"
                           class="form-control form-control-sm"
                           placeholder="Mã đơn, tên người nhận, SĐT, email...">
                </div>
                <div class="col-6 col-md-3">
                    <label for="filterStatus" class="form-label small fw-semibold">Trạng thái đơn hàng</label>
                    <select id="filterStatus" name="status" class="form-select form-select-sm">
                        <option value="">Tất cả trạng thái</option>
                        @foreach ($statusLabels as $code => $label)
                            <option value="{{ $code }}" {{ $status === $code ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label for="filterPayment" class="form-label small fw-semibold">Thanh toán</label>
                    <select id="filterPayment" name="payment_status" class="form-select form-select-sm">
                        <option value="">Tất cả</option>
                        <option value="pending" {{ $paymentStatus === 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                        <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-primary btn-sm flex-grow-1">Lọc</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">Đặt lại</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mã đơn</th>
                        <th>Người nhận</th>
                        <th>Thanh toán</th>
                        <th class="text-end">Tổng tiền</th>
                        <th>Trạng thái đơn</th>
                        <th>Ngày đặt</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $payMethod = $order->PhuongThucThanhToan ?? 'cod';
                            $payStatus = $order->TrangThaiThanhToan ?? 'pending';
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->MaDonHang) }}"
                                   class="fw-bold font-monospace text-decoration-none">
                                    {{ $order->SoDonHang }}
                                </a>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $order->TenNguoiNhan }}</div>
                                <div class="small text-muted">{{ $order->SoDienThoaiNguoiNhan }}</div>
                            </td>
                            <td>
                                <div class="small fw-semibold">
                                    {{ $payMethod === 'bank_transfer' ? 'Chuyển khoản' : 'COD' }}
                                </div>
                                <span class="badge {{ OrderStatus::paymentBadgeClass($payStatus) }}">
                                    {{ OrderStatus::paymentStatusLabel($payStatus) }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-danger">
                                {{ number_format((float) $order->TongTien, 0, ',', '.') }} đ
                            </td>
                            <td>
                                <span class="badge {{ OrderStatus::badgeClass($order->TrangThai) }}">
                                    {{ OrderStatus::label($order->TrangThai) }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ $order->DatLuc ?? $order->NgayTao }}
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('admin.orders.show', $order->MaDonHang) }}"
                                       class="btn btn-outline-primary btn-sm"
                                       title="Xem & Cập nhật">
                                        <i class="bi bi-eye me-1"></i>Chi tiết
                                    </a>
                                    <a href="{{ route('admin.orders.print', $order->MaDonHang) }}"
                                       target="_blank"
                                       class="btn btn-outline-secondary btn-sm"
                                       title="In đơn hàng">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Không tìm thấy đơn hàng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection

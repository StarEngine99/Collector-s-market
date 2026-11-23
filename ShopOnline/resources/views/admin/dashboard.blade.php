@extends('layouts.admin')

@section('title', 'Dashboard - Quản trị Cửa hàng')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="text-uppercase text-primary small fw-bold mb-1">Tổng quan cửa hàng</div>
            <h1 class="h3 fw-bold mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Hoạt động kinh doanh trong tháng {{ $monthLabel }}.</p>
        </div>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-primary">
            <i class="bi bi-bar-chart-line me-1"></i> Xem báo cáo
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 dashboard-stat">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">Doanh thu tháng</div>
                        <div class="fs-4 fw-bold mt-2">{{ number_format($monthlyRevenue, 0, ',', '.') }} đ</div>
                        <div class="text-muted small mt-1">Từ đơn hàng hoàn tất</div>
                    </div>
                    <span class="dashboard-stat-icon text-primary bg-primary-subtle"><i class="bi bi-currency-exchange"></i></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 dashboard-stat">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">Đơn cần xử lý</div>
                        <div class="fs-4 fw-bold mt-2">{{ number_format($pendingOrderCount) }}</div>
                        <a href="{{ route('admin.orders.index') }}" class="small text-decoration-none">Mở danh sách đơn</a>
                    </div>
                    <span class="dashboard-stat-icon text-warning bg-warning-subtle"><i class="bi bi-box-seam"></i></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 dashboard-stat">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">Sản phẩm đang bán</div>
                        <div class="fs-4 fw-bold mt-2">{{ number_format($activeProductCount) }}</div>
                        <div class="text-muted small mt-1">{{ number_format($lowStockCount) }} sản phẩm sắp hết hàng</div>
                    </div>
                    <span class="dashboard-stat-icon text-success bg-success-subtle"><i class="bi bi-boxes"></i></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 dashboard-stat">
                <div class="card-body d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">Khách hàng hoạt động</div>
                        <div class="fs-4 fw-bold mt-2">{{ number_format($customerCount) }}</div>
                        <a href="{{ route('admin.customers.index') }}" class="small text-decoration-none">Mở danh sách khách</a>
                    </div>
                    <span class="dashboard-stat-icon text-info bg-info-subtle"><i class="bi bi-person-check"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                    <div>
                        <h2 class="h6 fw-bold mb-1">Đơn hàng gần đây</h2>
                        <p class="text-muted small mb-0">Theo dõi các đơn mới nhất của cửa hàng.</p>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary btn-sm">Xem tất cả</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="pe-3">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentOrders as $order)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('admin.orders.show', $order->MaDonHang) }}" class="fw-semibold text-decoration-none">
                                            {{ $order->SoDonHang }}
                                        </a>
                                    </td>
                                    <td>{{ $order->TenKhachHang ?: $order->TenNguoiNhan }}</td>
                                    <td class="text-muted small">
                                        {{ \Illuminate\Support\Carbon::parse($order->DatLuc ?? $order->NgayTao)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-end fw-semibold">{{ number_format((float) $order->TongTien, 0, ',', '.') }} đ</td>
                                    <td class="pe-3">
                                        <span class="badge {{ \App\Support\OrderStatus::badgeClass($order->TrangThai) }}">
                                            {{ \App\Support\OrderStatus::label($order->TrangThai) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">Chưa có đơn hàng.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-4">
            <section class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-bold mb-1">Cần chú ý</h2>
                    <p class="text-muted small mb-0">Các việc cần theo dõi trong cửa hàng.</p>
                </div>
                <div class="card-body d-grid gap-3 align-content-start">
                    <a href="{{ route('admin.reports.index') }}" class="dashboard-notice">
                        <span class="dashboard-notice-icon text-danger bg-danger-subtle"><i class="bi bi-exclamation-triangle"></i></span>
                        <span>
                            <strong>{{ number_format($lowStockCount) }} sản phẩm</strong>
                            <small class="d-block text-muted">Sắp hết hàng (tồn kho ≤ 5)</small>
                        </span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('admin.orders.index') }}" class="dashboard-notice">
                        <span class="dashboard-notice-icon text-warning bg-warning-subtle"><i class="bi bi-hourglass-split"></i></span>
                        <span>
                            <strong>{{ number_format($pendingOrderCount) }} đơn hàng</strong>
                            <small class="d-block text-muted">Đang chờ xử lý</small>
                        </span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('admin.promotions.index') }}" class="dashboard-notice">
                        <span class="dashboard-notice-icon text-primary bg-primary-subtle"><i class="bi bi-megaphone"></i></span>
                        <span>
                            <strong>Khuyến mãi</strong>
                            <small class="d-block text-muted">Quản lý ưu đãi đang áp dụng</small>
                        </span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                </div>
            </section>
        </div>
    </div>
@endsection

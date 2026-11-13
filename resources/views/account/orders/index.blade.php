@extends('layouts.app')

@section('title', 'Lịch sử đơn hàng | Collector\'s Market')

@section('content')
<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('account.index') }}">Tài khoản</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Lịch sử đơn hàng</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0">Lịch sử đơn hàng của bạn</h1>
        </div>
        <a href="{{ route('account.index') }}" class="btn btn-outline-secondary">&larr; Quay lại tài khoản</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Bộ lọc trạng thái đơn hàng --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="small fw-semibold text-muted me-2">Trạng thái:</span>
                <a href="{{ route('account.orders') }}"
                   class="btn btn-sm {{ $status === '' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    Tất cả
                </a>
                @foreach($statusLabels as $code => $label)
                    <a href="{{ route('account.orders', ['status' => $code]) }}"
                       class="btn btn-sm {{ $status === $code ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Danh sách đơn hàng --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mã đơn hàng</th>
                            <th>Ngày đặt</th>
                            <th>Người nhận</th>
                            <th>Thanh toán</th>
                            <th class="text-end">Tổng thanh toán</th>
                            <th class="text-center">Trạng thái đơn</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="ps-4 fw-bold font-monospace">
                                    <a href="{{ route('account.orders.show', $order->SoDonHang) }}" class="text-decoration-none">
                                        {{ $order->SoDonHang }}
                                    </a>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($order->NgayTao)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $order->TenNguoiNhan }}</div>
                                    <small class="text-muted">{{ $order->SoDienThoaiNhan }}</small>
                                </td>
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
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('account.orders.show', $order->SoDonHang) }}" class="btn btn-sm btn-outline-primary">
                                            Chi tiết
                                        </a>
                                        @if(\App\Support\OrderStatus::canCustomerCancel($order->TrangThai))
                                            <form action="{{ route('account.orders.cancel', $order->SoDonHang) }}" method="POST"
                                                  onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng {{ $order->SoDonHang }}?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hủy đơn</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    Không tìm thấy đơn hàng nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection


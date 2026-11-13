@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng ' . $order->SoDonHang . ' | Collector\'s Market')

@section('content')
<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('account.index') }}">Tài khoản</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('account.orders') }}">Lịch sử đơn hàng</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $order->SoDonHang }}</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0">
                Đơn hàng <span class="text-primary font-monospace">{{ $order->SoDonHang }}</span>
            </h1>
            <small class="text-muted">Đặt lúc {{ \Carbon\Carbon::parse($order->NgayTao)->format('d/m/Y H:i') }}</small>
        </div>
        <div class="d-flex gap-2">
            @if(\App\Support\OrderStatus::canCustomerCancel($order->TrangThai))
                <form action="{{ route('account.orders.cancel', $order->SoDonHang) }}" method="POST"
                      onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng {{ $order->SoDonHang }}?');">
                    @csrf
                    <button type="submit" class="btn btn-danger">Hủy đơn hàng</button>
                </form>
            @endif
            <a href="{{ route('account.orders') }}" class="btn btn-outline-secondary">&larr; Danh sách đơn hàng</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Tiến trình trạng thái đơn hàng --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <h2 class="h6 fw-bold mb-0">Trạng thái đơn hàng</h2>
                <span class="badge bg-{{ \App\Support\OrderStatus::badgeClass($order->TrangThai) }} fs-6">
                    {{ \App\Support\OrderStatus::label($order->TrangThai) }}
                </span>
            </div>

            @php
                $progressStatuses = [
                    \App\Support\OrderStatus::PREPARING,
                    \App\Support\OrderStatus::PREPARED,
                    \App\Support\OrderStatus::SHIPPING,
                    \App\Support\OrderStatus::IN_TRANSIT,
                    \App\Support\OrderStatus::DELIVERING,
                    \App\Support\OrderStatus::COMPLETED,
                ];
                $currentIndex = array_search($order->TrangThai, $progressStatuses, true);
            @endphp

            @if($order->TrangThai === \App\Support\OrderStatus::CANCELLED)
                <div class="alert alert-danger mb-0">
                    Đơn hàng này đã bị hủy. Tồn kho sản phẩm đã được hoàn lại.
                </div>
            @else
                <div class="row g-2 text-center">
                    @foreach($progressStatuses as $idx => $stepCode)
                        @php
                            $isDone = $currentIndex !== false && $idx <= $currentIndex;
                            $isCurrent = $order->TrangThai === $stepCode;
                        @endphp
                        <div class="col-6 col-md-2">
                            <div class="p-2 rounded border {{ $isCurrent ? 'border-primary bg-primary bg-opacity-10 fw-bold' : ($isDone ? 'border-success bg-light' : 'text-muted') }}">
                                <div class="small">Bước {{ $idx + 1 }}</div>
                                <div class="small">{{ \App\Support\OrderStatus::label($stepCode) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 small text-muted">
                    @if(\App\Support\OrderStatus::canCustomerCancel($order->TrangThai))
                        * Bạn có thể hủy đơn hàng khi đơn đang ở trạng thái <strong>"Đang chuẩn bị hàng"</strong> hoặc <strong>"Đã chuẩn bị hàng"</strong>.
                    @else
                        * Đơn hàng đã chuyển sang giai đoạn vận chuyển/hoàn tất nên không thể hủy trực tiếp.
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Hướng dẫn chuyển khoản nếu đơn dùng bank_transfer và chưa thanh toán --}}
    @if(($payment->PhuongThuc ?? '') === \App\Support\OrderStatus::PAYMENT_METHOD_BANK_TRANSFER
        && ($payment->TrangThai ?? '') === \App\Support\OrderStatus::PAYMENT_STATUS_PENDING
        && $order->TrangThai !== \App\Support\OrderStatus::CANCELLED)
        @php
            $transferNote = str_replace(
                '{ORDER_NUMBER}',
                $order->SoDonHang,
                (string) ($bankTransferConfig['transfer_note_prefix'] ?? 'CM {ORDER_NUMBER}')
            );
        @endphp
        <div class="card border-primary shadow-sm mb-4">
            <div class="card-header bg-primary text-white fw-bold">
                Thông tin chuyển khoản ngân hàng
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Ngân hàng</div>
                        <div class="fw-bold">{{ $bankTransferConfig['bank_name'] ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Chi nhánh</div>
                        <div class="fw-semibold">{{ $bankTransferConfig['bank_branch'] ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Số tài khoản</div>
                        <div class="fw-bold fs-5 text-primary font-monospace">{{ $bankTransferConfig['account_number'] ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Chủ tài khoản</div>
                        <div class="fw-bold">{{ $bankTransferConfig['account_holder'] ?? '' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Số tiền cần chuyển</div>
                        <div class="fw-bold fs-5 text-danger">{{ number_format($order->TongThanhToan, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Nội dung chuyển khoản</div>
                        <div class="fw-bold font-monospace bg-light border rounded px-2 py-1 d-inline-block">{{ $transferNote }}</div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        {{-- Danh sách sản phẩm trong đơn --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 fw-bold mb-0">Sản phẩm đã đặt</h2>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Sản phẩm</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end pe-4">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-semibold">{{ $item->TenSanPham }}</div>
                                            <small class="text-muted font-monospace">SKU: {{ $item->SKU }}</small>
                                        </td>
                                        <td class="text-end">{{ number_format($item->DonGia, 0, ',', '.') }} đ</td>
                                        <td class="text-center">{{ $item->SoLuong }}</td>
                                        <td class="text-end pe-4 fw-semibold">{{ number_format($item->ThanhTien, 0, ',', '.') }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end ps-4">Tạm tính:</td>
                                    <td class="text-end pe-4 fw-semibold">{{ number_format($order->TamTinh, 0, ',', '.') }} đ</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end ps-4">
                                        Giảm giá mã khuyến mãi
                                        @if($coupon)
                                            (<span class="font-monospace">{{ $coupon->MaCode }}</span>)
                                        @endif:
                                    </td>
                                    <td class="text-end pe-4 text-success">-{{ number_format($order->SoTienGiamGia, 0, ',', '.') }} đ</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end ps-4">Phí vận chuyển:</td>
                                    <td class="text-end pe-4">{{ number_format($order->PhiVanChuyen, 0, ',', '.') }} đ</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end ps-4 fw-bold fs-5">Tổng thanh toán:</td>
                                    <td class="text-end pe-4 fw-bold fs-5 text-danger">{{ number_format($order->TongThanhToan, 0, ',', '.') }} đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Thông tin nhận hàng & thanh toán --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-bold mb-0">Thông tin giao hàng</h2>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="text-muted small d-block">Người nhận</span>
                        <strong>{{ $order->TenNguoiNhan }}</strong>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small d-block">Số điện thoại</span>
                        <span>{{ $order->SoDienThoaiNhan }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted small d-block">Địa chỉ nhận hàng</span>
                        <span>{{ $order->DiaChiGiaoHang }}</span>
                    </div>
                    @if($order->GhiChu)
                        <div class="mt-3 pt-2 border-top">
                            <span class="text-muted small d-block">Ghi chú</span>
                            <span>{{ $order->GhiChu }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h6 fw-bold mb-0">Thông tin thanh toán</h2>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="text-muted small d-block">Phương thức</span>
                        <strong>{{ \App\Support\OrderStatus::paymentMethodLabel($payment->PhuongThuc ?? null) }}</strong>
                    </div>
                    <div class="mb-0">
                        <span class="text-muted small d-block">Trạng thái thanh toán</span>
                        <span class="badge bg-{{ \App\Support\OrderStatus::paymentStatusBadgeClass($payment->TrangThai ?? null) }}">
                            {{ \App\Support\OrderStatus::paymentStatusLabel($payment->TrangThai ?? null) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


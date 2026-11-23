@extends('layouts.app')

@section('title', 'Đặt hàng thành công - Chợ Figure & Thẻ Bài')

@section('content')
    @php
        use App\Support\OrderStatus;
        $paymentMethod = $payment->PhuongThuc ?? 'cod';
        $paymentStatus = $payment->TrangThai ?? 'pending';
    @endphp

    <div class="row justify-content-center py-3">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle mb-3"
                         style="width: 64px; height: 64px;">
                        <i class="bi bi-check-lg fs-1"></i>
                    </div>
                    <h1 class="h4 fw-bold mb-2">Cảm ơn bạn đã đặt hàng!</h1>
                    <p class="text-muted mb-3">
                        Đơn hàng <strong class="text-dark font-monospace">{{ $order->SoDonHang }}</strong> của bạn đã được ghi nhận.
                    </p>

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <span class="badge {{ OrderStatus::badgeClass($order->TrangThai) }} px-3 py-2">
                            Trạng thái đơn: {{ OrderStatus::label($order->TrangThai) }}
                        </span>
                        <span class="badge {{ OrderStatus::paymentBadgeClass($paymentStatus) }} px-3 py-2">
                            Thanh toán: {{ OrderStatus::paymentStatusLabel($paymentStatus) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Hướng dẫn chuyển khoản nếu chọn Chuyển khoản ngân hàng --}}
            @if ($paymentMethod === 'bank_transfer')
                <div class="card border-primary shadow-sm mb-4">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="bi bi-bank me-1"></i>Hướng dẫn thanh toán Chuyển khoản ngân hàng
                    </div>
                    <div class="card-body p-4">
                        <p class="small text-muted mb-3">
                            Vui lòng chuyển khoản đúng số tiền và nội dung bên dưới. Quản trị viên sẽ xác nhận trạng thái
                            <strong>Đã thanh toán</strong> ngay sau khi nhận được tiền.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <th class="bg-light" style="width: 35%;">Ngân hàng</th>
                                        <td>{{ $bankTransferConfig['bank_name'] ?? 'Đang cập nhật' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Số tài khoản</th>
                                        <td class="font-monospace fw-bold text-primary">{{ $bankTransferConfig['account_number'] ?? 'Đang cập nhật' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Chủ tài khoản</th>
                                        <td class="fw-semibold">{{ $bankTransferConfig['account_holder'] ?? 'Đang cập nhật' }}</td>
                                    </tr>
                                    @if (!empty($bankTransferConfig['branch']))
                                        <tr>
                                            <th class="bg-light">Chi nhánh</th>
                                            <td>{{ $bankTransferConfig['branch'] }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th class="bg-light">Số tiền cần chuyển</th>
                                        <td class="fw-bold text-danger">{{ number_format((float) $order->TongTien, 0, ',', '.') }} đ</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Nội dung chuyển khoản</th>
                                        <td>
                                            <span class="badge bg-warning text-dark font-monospace fs-6">{{ $order->SoDonHang }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Chi tiết đơn hàng --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h6 fw-bold mb-3">Thông tin nhận hàng &amp; Sản phẩm</h2>

                    <div class="row g-3 small mb-3">
                        <div class="col-12 col-md-6">
                            <div><strong>Người nhận:</strong> {{ $order->TenNguoiNhan }}</div>
                            <div><strong>Điện thoại:</strong> {{ $order->SoDienThoaiNguoiNhan }}</div>
                            <div><strong>Địa chỉ:</strong> {{ $order->DiaChiGiaoHang }}</div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div><strong>Phương thức thanh toán:</strong> {{ OrderStatus::paymentMethodLabel($paymentMethod) }}</div>
                            <div><strong>Thời gian đặt:</strong> {{ $order->DatLuc ?? $order->NgayTao }}</div>
                            @if (!empty($order->GhiChu))
                                <div><strong>Ghi chú:</strong> {{ $order->GhiChu }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Đơn giá</th>
                                    <th class="text-center">SL</th>
                                    <th class="text-end">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $item->TenSanPham }}</div>
                                            <div class="small text-muted">SKU: {{ $item->MaSKU }}</div>
                                        </td>
                                        <td class="text-end">{{ number_format((float) $item->DonGia, 0, ',', '.') }} đ</td>
                                        <td class="text-center">{{ (int) $item->SoLuong }}</td>
                                        <td class="text-end fw-semibold">{{ number_format((float) $item->ThanhTien, 0, ',', '.') }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end text-muted">Tạm tính:</td>
                                    <td class="text-end">{{ number_format((float) $order->TamTinh, 0, ',', '.') }} đ</td>
                                </tr>
                                @if ((float) $order->TienGiam > 0)
                                    <tr>
                                        <td colspan="3" class="text-end text-muted">
                                            Giảm giá {{ $coupon ? '(' . $coupon->MaUuDai . ')' : '' }}:
                                        </td>
                                        <td class="text-end text-success">-{{ number_format((float) $order->TienGiam, 0, ',', '.') }} đ</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                                    <td class="text-end fw-bold text-danger fs-6">{{ number_format((float) $order->TongTien, 0, ',', '.') }} đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                    <i class="bi bi-shop me-1"></i>Tiếp tục mua sắm
                </a>
                @if (Route::has('account.orders.show'))
                    <a href="{{ route('account.orders.show', $order->SoDonHang) }}" class="btn btn-primary">
                        <i class="bi bi-receipt me-1"></i>Xem trong Lịch sử đơn hàng
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection

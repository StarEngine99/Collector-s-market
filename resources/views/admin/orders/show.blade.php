@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng ' . $order->SoDonHang . ' - Quản trị')

@section('content')
    @php
        use App\Support\OrderStatus;
        $payMethod = $payment->PhuongThuc ?? 'cod';
        $payStatus = $payment->TrangThai ?? 'pending';
        $isFinalState = in_array($order->TrangThai, [OrderStatus::COMPLETED, OrderStatus::CANCELLED], true);
    @endphp

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Quay lại danh sách đơn hàng
            </a>
            <h1 class="h4 fw-bold mb-0 mt-1">
                Đơn hàng <span class="font-monospace text-primary">{{ $order->SoDonHang }}</span>
            </h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.print', $order->MaDonHang) }}"
               target="_blank"
               class="btn btn-outline-dark btn-sm">
                <i class="bi bi-printer me-1"></i>In chi tiết đơn
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Cột Chi tiết sản phẩm & Thông tin giao hàng --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    Danh sách sản phẩm trong đơn
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sản phẩm</th>
                                <th>SKU</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-center">SL</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td class="fw-semibold">{{ $item->TenSanPham }}</td>
                                    <td class="small text-muted font-monospace">{{ $item->MaSKU }}</td>
                                    <td class="text-end">{{ number_format((float) $item->DonGia, 0, ',', '.') }} đ</td>
                                    <td class="text-center">{{ (int) $item->SoLuong }}</td>
                                    <td class="text-end fw-semibold">{{ number_format((float) $item->ThanhTien, 0, ',', '.') }} đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end">Tạm tính:</td>
                                <td class="text-end fw-semibold">{{ number_format((float) $order->TamTinh, 0, ',', '.') }} đ</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">
                                    Giảm giá {{ $coupon ? '(' . $coupon->MaUuDai . ')' : '' }}:
                                </td>
                                <td class="text-end text-success fw-semibold">
                                    -{{ number_format((float) $order->TienGiam, 0, ',', '.') }} đ
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">Phí vận chuyển:</td>
                                <td class="text-end">{{ number_format((float) $order->PhiVanChuyen, 0, ',', '.') }} đ</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Tổng thanh toán:</td>
                                <td class="text-end fw-bold text-danger fs-5">
                                    {{ number_format((float) $order->TongTien, 0, ',', '.') }} đ
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    Thông tin nhận hàng &amp; Khách hàng
                </div>
                <div class="card-body">
                    <div class="row g-3 small">
                        <div class="col-12 col-md-6">
                            <div class="text-muted mb-1">Người nhận hàng</div>
                            <div class="fw-semibold fs-6">{{ $order->TenNguoiNhan }}</div>
                            <div><strong>SĐT:</strong> {{ $order->SoDienThoaiNguoiNhan }}</div>
                            <div><strong>Địa chỉ giao:</strong> {{ $order->DiaChiGiaoHang }}</div>
                            @if (!empty($order->GhiChu))
                                <div class="mt-2 p-2 bg-light rounded border">
                                    <strong>Ghi chú khách hàng:</strong> {{ $order->GhiChu }}
                                </div>
                            @endif
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="text-muted mb-1">Tài khoản đặt hàng</div>
                            @if ($customer)
                                <div class="fw-semibold">{{ $customer->HoTen }} (#{{ $customer->MaNguoiDung }})</div>
                                <div><strong>Email:</strong> {{ $customer->Email }}</div>
                                <div><strong>SĐT TK:</strong> {{ $customer->SoDienThoai ?? '—' }}</div>
                            @else
                                <div class="text-muted">Khách vãng lai / Tài khoản đã xóa</div>
                            @endif
                            <div class="mt-2"><strong>Đặt lúc:</strong> {{ $order->DatLuc ?? $order->NgayTao }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cột Cập nhật trạng thái đơn & Xác nhận thanh toán --}}
        <div class="col-12 col-lg-4">
            {{-- Khung Cập nhật trạng thái đơn hàng --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    1. Trạng thái đơn hàng
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="small text-muted d-block mb-1">Trạng thái hiện tại:</span>
                        <span class="badge {{ OrderStatus::badgeClass($order->TrangThai) }} fs-6">
                            {{ OrderStatus::label($order->TrangThai) }}
                        </span>
                    </div>

                    @can('orders.update-status')
                        @if ($isFinalState)
                            <div class="alert alert-secondary small mb-0">
                                Đơn hàng đã ở trạng thái kết thúc ({{ OrderStatus::label($order->TrangThai) }}), không thể thay đổi trạng thái.
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.orders.status', $order->MaDonHang) }}">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label for="TrangThai" class="form-label small fw-semibold">Chuyển sang trạng thái:</label>
                                    <select id="TrangThai" name="TrangThai" class="form-select form-select-sm" required>
                                        @foreach ($statusLabels as $code => $label)
                                            <option value="{{ $code }}" {{ $order->TrangThai === $code ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="ly_do_huy" class="form-label small fw-semibold">
                                        Ghi chú / Lý do (nếu hủy đơn):
                                    </label>
                                    <input id="ly_do_huy"
                                           type="text"
                                           name="ly_do_huy"
                                           maxlength="300"
                                           class="form-control form-control-sm"
                                           placeholder="Nhập lý do nếu chọn Đã hủy...">
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                                    <i class="bi bi-check2-circle me-1"></i>Cập nhật trạng thái đơn
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>

            {{-- Khung Trạng thái thanh toán --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    2. Thông tin &amp; Xác nhận thanh toán
                </div>
                <div class="card-body">
                    <div class="small mb-3">
                        <div class="mb-1">
                            <strong>Phương thức:</strong> {{ OrderStatus::paymentMethodLabel($payMethod) }}
                        </div>
                        <div class="mb-1">
                            <strong>Trạng thái:</strong>
                            <span class="badge {{ OrderStatus::paymentBadgeClass($payStatus) }}">
                                {{ OrderStatus::paymentStatusLabel($payStatus) }}
                            </span>
                        </div>
                        @if (!empty($payment?->ThanhToanLuc))
                            <div><strong>Thanh toán lúc:</strong> {{ $payment->ThanhToanLuc }}</div>
                        @endif
                        @if (!empty($payment?->MaGiaoDichNhaCungCap))
                            <div><strong>Mã GD ngân hàng:</strong> <span class="font-monospace">{{ $payment->MaGiaoDichNhaCungCap }}</span></div>
                        @endif
                    </div>

                    @can('orders.update-status')
                        <form method="POST" action="{{ route('admin.orders.payment', $order->MaDonHang) }}">
                            @csrf
                            @method('PATCH')

                            <div class="mb-2">
                                <label for="TrangThaiThanhToan" class="form-label small fw-semibold">Trạng thái thanh toán:</label>
                                <select id="TrangThaiThanhToan" name="TrangThaiThanhToan" class="form-select form-select-sm">
                                    <option value="pending" {{ $payStatus === 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                                    <option value="paid" {{ $payStatus === 'paid' ? 'selected' : '' }}>Đã thanh toán (Đã nhận tiền)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="MaGiaoDichNhaCungCap" class="form-label small fw-semibold">
                                    Mã tham chiếu giao dịch (tùy chọn):
                                </label>
                                <input id="MaGiaoDichNhaCungCap"
                                       type="text"
                                       name="MaGiaoDichNhaCungCap"
                                       value="{{ old('MaGiaoDichNhaCungCap', $payment->MaGiaoDichNhaCungCap ?? '') }}"
                                       maxlength="150"
                                       class="form-control form-control-sm"
                                       placeholder="VD: FT26318...">
                            </div>

                            <button type="submit" class="btn btn-outline-success btn-sm w-100 fw-semibold">
                                <i class="bi bi-cash-check me-1"></i>Lưu trạng thái thanh toán
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection

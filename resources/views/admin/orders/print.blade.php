<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In đơn hàng {{ $order->SoDonHang }} - Chợ Figure &amp; Thẻ Bài</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
        }
    </style>
</head>
<body class="bg-light py-4">
    @php
        use App\Support\OrderStatus;
        $payMethod = $payment->PhuongThuc ?? 'cod';
        $payStatus = $payment->TrangThai ?? 'pending';
    @endphp

    <div class="container" style="max-width: 820px;">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('admin.orders.show', $order->MaDonHang) }}" class="btn btn-outline-secondary btn-sm">
                &larr; Quay lại chi tiết đơn
            </a>
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm">
                In phiếu đơn hàng
            </button>
        </div>

        <div class="card border shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
                    <div>
                        <h1 class="h4 fw-bold mb-1">CHỢ FIGURE &amp; THẺ BÀI</h1>
                        <div class="small text-muted">Trường Đại học Đồng Tháp — TP. Cao Lãnh, Đồng Tháp</div>
                    </div>
                    <div class="text-end">
                        <div class="h5 fw-bold mb-1">PHIẾU ĐƠN HÀNG</div>
                        <div class="font-monospace fw-semibold">{{ $order->SoDonHang }}</div>
                        <div class="small text-muted">Ngày đặt: {{ $order->DatLuc ?? $order->NgayTao }}</div>
                    </div>
                </div>

                <div class="row g-3 small mb-4">
                    <div class="col-6">
                        <div class="fw-bold text-uppercase text-muted mb-1">Thông tin người nhận</div>
                        <div><strong>Họ tên:</strong> {{ $order->TenNguoiNhan }}</div>
                        <div><strong>Điện thoại:</strong> {{ $order->SoDienThoaiNguoiNhan }}</div>
                        <div><strong>Địa chỉ:</strong> {{ $order->DiaChiGiaoHang }}</div>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold text-uppercase text-muted mb-1">Thông tin đơn hàng</div>
                        <div><strong>Trạng thái đơn:</strong> {{ OrderStatus::label($order->TrangThai) }}</div>
                        <div><strong>Phương thức TT:</strong> {{ OrderStatus::paymentMethodLabel($payMethod) }}</div>
                        <div><strong>Trạng thái TT:</strong> {{ OrderStatus::paymentStatusLabel($payStatus) }}</div>
                    </div>
                </div>

                <table class="table table-bordered align-middle small mb-4">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Sản phẩm</th>
                            <th>Mã SKU</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-center">SL</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $idx => $item)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $item->TenSanPham }}</td>
                                <td class="font-monospace">{{ $item->MaSKU }}</td>
                                <td class="text-end">{{ number_format((float) $item->DonGia, 0, ',', '.') }} đ</td>
                                <td class="text-center">{{ (int) $item->SoLuong }}</td>
                                <td class="text-end fw-semibold">{{ number_format((float) $item->ThanhTien, 0, ',', '.') }} đ</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end">Tạm tính:</td>
                            <td class="text-end">{{ number_format((float) $order->TamTinh, 0, ',', '.') }} đ</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end">
                                Giảm giá {{ $coupon ? '(' . $coupon->MaUuDai . ')' : '' }}:
                            </td>
                            <td class="text-end">-{{ number_format((float) $order->TienGiam, 0, ',', '.') }} đ</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Tổng thanh toán:</td>
                            <td class="text-end fw-bold fs-6">{{ number_format((float) $order->TongTien, 0, ',', '.') }} đ</td>
                        </tr>
                    </tfoot>
                </table>

                @if (!empty($order->GhiChu))
                    <div class="small mb-4">
                        <strong>Ghi chú:</strong> {{ $order->GhiChu }}
                    </div>
                @endif

                <div class="row text-center small mt-5 pt-3">
                    <div class="col-6">
                        <div class="fw-semibold">Người lập phiếu</div>
                        <div class="text-muted">(Ký, ghi rõ họ tên)</div>
                    </div>
                    <div class="col-6">
                        <div class="fw-semibold">Người nhận hàng</div>
                        <div class="text-muted">(Ký, ghi rõ họ tên)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>


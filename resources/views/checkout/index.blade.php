@extends('layouts.app')

@section('title', 'Thanh toán đơn hàng - Chợ Figure & Thẻ Bài')

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Trang chủ</a></li>
            <li class="breadcrumb-item"><a href="{{ route('cart.index') }}" class="text-decoration-none">Giỏ hàng</a></li>
            <li class="breadcrumb-item active" aria-current="page">Thanh toán</li>
        </ol>
    </nav>

    <h1 class="h4 fw-bold mb-4">
        <i class="bi bi-shield-check text-primary me-2"></i>Thanh toán &amp; Đặt hàng
    </h1>

    <form method="POST" action="{{ route('checkout.store') }}" id="checkoutForm">
        @csrf

        <div class="row g-4">
            {{-- Cột Thông tin giao hàng & Phương thức thanh toán --}}
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">1. Thông tin giao hàng</h2>

                        @if ($addresses->isNotEmpty())
                            <div class="mb-3">
                                <label for="savedAddressSelect" class="form-label small fw-semibold text-muted">
                                    Chọn nhanh từ sổ địa chỉ đã lưu:
                                </label>
                                <select id="savedAddressSelect" name="MaDiaChi" class="form-select form-select-sm">
                                    <option value="">-- Nhập địa chỉ mới hoặc chỉnh sửa bên dưới --</option>
                                    @foreach ($addresses as $addr)
                                        <option value="{{ $addr->MaDiaChi }}"
                                                data-name="{{ $addr->TenNguoiNhan }}"
                                                data-phone="{{ $addr->SoDienThoai }}"
                                                data-province="{{ $addr->TinhThanh }}"
                                                data-district="{{ $addr->QuanHuyen }}"
                                                data-ward="{{ $addr->PhuongXa }}"
                                                data-detail="{{ $addr->DiaChiChiTiet }}"
                                                {{ (int) old('MaDiaChi', $defaultAddress->MaDiaChi ?? 0) === (int) $addr->MaDiaChi ? 'selected' : '' }}>
                                            {{ $addr->TenNguoiNhan }} ({{ $addr->SoDienThoai }}) — {{ $addr->DiaChiChiTiet }}, {{ $addr->PhuongXa }}, {{ $addr->QuanHuyen }}, {{ $addr->TinhThanh }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="TenNguoiNhan" class="form-label fw-semibold">
                                    Họ và tên người nhận <span class="text-danger">*</span>
                                </label>
                                <input id="TenNguoiNhan"
                                       type="text"
                                       name="TenNguoiNhan"
                                       value="{{ old('TenNguoiNhan', $defaultAddress->TenNguoiNhan ?? $user->HoTen) }}"
                                       class="form-control @error('TenNguoiNhan') is-invalid @enderror"
                                       minlength="2"
                                       maxlength="150"
                                       required>
                                @error('TenNguoiNhan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="SoDienThoaiNguoiNhan" class="form-label fw-semibold">
                                    Số điện thoại nhận hàng <span class="text-danger">*</span>
                                </label>
                                <input id="SoDienThoaiNguoiNhan"
                                       type="tel"
                                       name="SoDienThoaiNguoiNhan"
                                       value="{{ old('SoDienThoaiNguoiNhan', $defaultAddress->SoDienThoai ?? $user->SoDienThoai) }}"
                                       class="form-control @error('SoDienThoaiNguoiNhan') is-invalid @enderror"
                                       pattern="[0-9+\-\s().]{8,20}"
                                       maxlength="30"
                                       required>
                                @error('SoDienThoaiNguoiNhan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="TinhThanh" class="form-label fw-semibold">
                                    Tỉnh / Thành phố <span class="text-danger">*</span>
                                </label>
                                <input id="TinhThanh"
                                       type="text"
                                       name="TinhThanh"
                                       value="{{ old('TinhThanh', $defaultAddress->TinhThanh ?? '') }}"
                                       class="form-control @error('TinhThanh') is-invalid @enderror"
                                       placeholder="VD: Đồng Tháp"
                                       maxlength="120"
                                       required>
                                @error('TinhThanh')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="QuanHuyen" class="form-label fw-semibold">
                                    Quận / Huyện / TP <span class="text-danger">*</span>
                                </label>
                                <input id="QuanHuyen"
                                       type="text"
                                       name="QuanHuyen"
                                       value="{{ old('QuanHuyen', $defaultAddress->QuanHuyen ?? '') }}"
                                       class="form-control @error('QuanHuyen') is-invalid @enderror"
                                       placeholder="VD: TP. Cao Lãnh"
                                       maxlength="120"
                                       required>
                                @error('QuanHuyen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="PhuongXa" class="form-label fw-semibold">
                                    Phường / Xã <span class="text-danger">*</span>
                                </label>
                                <input id="PhuongXa"
                                       type="text"
                                       name="PhuongXa"
                                       value="{{ old('PhuongXa', $defaultAddress->PhuongXa ?? '') }}"
                                       class="form-control @error('PhuongXa') is-invalid @enderror"
                                       placeholder="VD: Phường 6"
                                       maxlength="120"
                                       required>
                                @error('PhuongXa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="DiaChiChiTiet" class="form-label fw-semibold">
                                    Địa chỉ chi tiết (Số nhà, tên đường) <span class="text-danger">*</span>
                                </label>
                                <input id="DiaChiChiTiet"
                                       type="text"
                                       name="DiaChiChiTiet"
                                       value="{{ old('DiaChiChiTiet', $defaultAddress->DiaChiChiTiet ?? '') }}"
                                       class="form-control @error('DiaChiChiTiet') is-invalid @enderror"
                                       placeholder="VD: 783 Phạm Hữu Lầu"
                                       minlength="3"
                                       maxlength="255"
                                       required>
                                @error('DiaChiChiTiet')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input id="luu_dia_chi"
                                           type="checkbox"
                                           name="luu_dia_chi"
                                           value="1"
                                           class="form-check-input"
                                           {{ old('luu_dia_chi') ? 'checked' : '' }}>
                                    <label for="luu_dia_chi" class="form-check-label small">
                                        Lưu địa chỉ này vào sổ địa chỉ tài khoản cho lần mua sau
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="GhiChu" class="form-label fw-semibold">Ghi chú đơn hàng (không bắt buộc)</label>
                                <textarea id="GhiChu"
                                          name="GhiChu"
                                          rows="2"
                                          maxlength="1000"
                                          class="form-control @error('GhiChu') is-invalid @enderror"
                                          placeholder="Ghi chú về thời gian nhận hàng hoặc hướng dẫn giao hàng...">{{ old('GhiChu') }}</textarea>
                                @error('GhiChu')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Phương thức thanh toán: chỉ COD và Chuyển khoản --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">2. Phương thức thanh toán</h2>
                        @php $selectedPayment = old('PhuongThuc', 'cod'); @endphp

                        <div class="d-flex flex-column gap-3">
                            <div class="form-check border rounded p-3">
                                <input class="form-check-input payment-method-radio"
                                       type="radio"
                                       name="PhuongThuc"
                                       id="paymentCod"
                                       value="cod"
                                       {{ $selectedPayment === 'cod' ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label d-block" for="paymentCod">
                                    <span class="fw-bold"><i class="bi bi-cash-coin text-success me-1"></i>Thanh toán khi nhận hàng (COD)</span>
                                    <div class="small text-muted mt-1">
                                        Thanh toán bằng tiền mặt trực tiếp cho nhân viên giao hàng khi nhận được sản phẩm.
                                    </div>
                                </label>
                            </div>

                            <div class="form-check border rounded p-3">
                                <input class="form-check-input payment-method-radio"
                                       type="radio"
                                       name="PhuongThuc"
                                       id="paymentBank"
                                       value="bank_transfer"
                                       {{ $selectedPayment === 'bank_transfer' ? 'checked' : '' }}>
                                <label class="form-check-label d-block" for="paymentBank">
                                    <span class="fw-bold"><i class="bi bi-bank text-primary me-1"></i>Chuyển khoản ngân hàng</span>
                                    <div class="small text-muted mt-1">
                                        Chuyển khoản vào tài khoản ngân hàng của cửa hàng. Quản trị viên sẽ xác nhận sau khi nhận được thanh toán.
                                    </div>
                                </label>

                                <div id="bankTransferInfoBox"
                                     class="mt-3 p-3 bg-light border rounded small {{ $selectedPayment === 'bank_transfer' ? '' : 'd-none' }}">
                                    <div class="fw-semibold text-primary mb-2">
                                        <i class="bi bi-info-circle me-1"></i>Thông tin tài khoản nhận chuyển khoản:
                                    </div>
                                    <div><strong>Ngân hàng:</strong> {{ $bankTransferConfig['bank_name'] ?? 'Đang cập nhật' }}</div>
                                    <div><strong>Số tài khoản:</strong> <span class="font-monospace fw-bold">{{ $bankTransferConfig['account_number'] ?? 'Đang cập nhật' }}</span></div>
                                    <div><strong>Chủ tài khoản:</strong> {{ $bankTransferConfig['account_holder'] ?? 'Đang cập nhật' }}</div>
                                    @if (!empty($bankTransferConfig['branch']))
                                        <div><strong>Chi nhánh:</strong> {{ $bankTransferConfig['branch'] }}</div>
                                    @endif
                                    <div class="text-muted mt-2">
                                        * Nội dung chuyển khoản (Mã đơn hàng) sẽ được hiển thị ngay sau khi bạn bấm <strong>Đặt hàng</strong>.
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('PhuongThuc')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Cột Tóm tắt đơn hàng --}}
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm sticky-top" style="top: 84px;">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-bold mb-3">Đơn hàng của bạn ({{ (int) $summary['total_quantity'] }} sản phẩm)</h2>

                        <div class="border-bottom pb-3 mb-3 d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
                            @foreach ($summary['items'] as $item)
                                <div class="d-flex justify-content-between align-items-start gap-2 small">
                                    <div>
                                        <div class="fw-semibold">{{ $item->TenSanPham }}</div>
                                        <div class="text-muted">SL: {{ (int) $item->SoLuong }} × {{ number_format((float) $item->DonGia, 0, ',', '.') }} đ</div>
                                    </div>
                                    <div class="fw-semibold text-end text-nowrap">
                                        {{ number_format((float) $item->ThanhTien, 0, ',', '.') }} đ
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-semibold">{{ number_format((float) $summary['subtotal'], 0, ',', '.') }} đ</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2 small">
                            <span class="text-muted">
                                Giảm giá mã ưu đãi
                                @if ($summary['coupon'])
                                    ({{ $summary['coupon']->MaUuDai }})
                                @endif:
                            </span>
                            <span class="text-success fw-semibold">
                                -{{ number_format((float) $summary['discount_amount'], 0, ',', '.') }} đ
                            </span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 small">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="fw-semibold">Miễn phí</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fw-bold">Tổng thanh toán:</span>
                            <span class="h4 fw-bold text-danger mb-0">
                                {{ number_format((float) $summary['total'], 0, ',', '.') }} đ
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i>Xác nhận Đặt hàng
                        </button>

                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary w-100 mt-2 btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        (function () {
            const addressSelect = document.getElementById('savedAddressSelect');
            if (addressSelect) {
                addressSelect.addEventListener('change', function () {
                    const selected = addressSelect.options[addressSelect.selectedIndex];
                    if (!selected || !selected.value) {
                        return;
                    }
                    document.getElementById('TenNguoiNhan').value = selected.dataset.name || '';
                    document.getElementById('SoDienThoaiNguoiNhan').value = selected.dataset.phone || '';
                    document.getElementById('TinhThanh').value = selected.dataset.province || '';
                    document.getElementById('QuanHuyen').value = selected.dataset.district || '';
                    document.getElementById('PhuongXa').value = selected.dataset.ward || '';
                    document.getElementById('DiaChiChiTiet').value = selected.dataset.detail || '';
                });
            }

            const paymentRadios = document.querySelectorAll('.payment-method-radio');
            const bankBox = document.getElementById('bankTransferInfoBox');
            paymentRadios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    if (!bankBox) {
                        return;
                    }
                    if (radio.value === 'bank_transfer' && radio.checked) {
                        bankBox.classList.remove('d-none');
                    } else if (radio.value === 'cod' && radio.checked) {
                        bankBox.classList.add('d-none');
                    }
                });
            });
        })();
    </script>
@endpush

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-12 col-md-4">
        <label for="LoaiPhieu" class="form-label fw-semibold">
            Loại phiếu <span class="text-danger">*</span>
        </label>
        @php $currentVoucherType = old('LoaiPhieu', $coupon->LoaiPhieu ?? 'san_pham'); @endphp
        <select id="LoaiPhieu" name="LoaiPhieu" class="form-select @error('LoaiPhieu') is-invalid @enderror" required>
            <option value="san_pham" {{ $currentVoucherType === 'san_pham' ? 'selected' : '' }}>Phiếu giảm giá sản phẩm</option>
            <option value="van_chuyen" {{ $currentVoucherType === 'van_chuyen' ? 'selected' : '' }}>Phiếu giảm phí vận chuyển</option>
        </select>
        @error('LoaiPhieu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="MaUuDai" class="form-label fw-semibold">
            Mã ưu đãi <span class="text-danger">*</span>
        </label>
        <input id="MaUuDai"
               type="text"
               name="MaUuDai"
               value="{{ old('MaUuDai', $coupon->MaUuDai ?? '') }}"
               class="form-control text-uppercase font-monospace @error('MaUuDai') is-invalid @enderror"
               placeholder="VD: GIAM10, FIGURE50K"
               minlength="3"
               maxlength="80"
               pattern="[A-Za-z0-9_\-]+"
               required>
        <div class="form-text">Chỉ gồm chữ không dấu, số, gạch ngang (-) hoặc gạch dưới (_).</div>
        @error('MaUuDai')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="LoaiGiamGia" class="form-label fw-semibold">
            Loại giảm giá <span class="text-danger">*</span>
        </label>
        @php $currentType = old('LoaiGiamGia', $coupon->LoaiGiamGia ?? 'percent'); @endphp
        <select id="LoaiGiamGia"
                name="LoaiGiamGia"
                class="form-select @error('LoaiGiamGia') is-invalid @enderror"
                required>
            <option value="percent" {{ $currentType === 'percent' ? 'selected' : '' }}>Theo phần trăm (%)</option>
            <option value="fixed" {{ $currentType === 'fixed' ? 'selected' : '' }}>Số tiền cố định (đ)</option>
        </select>
        @error('LoaiGiamGia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="GiaTriGiam" class="form-label fw-semibold">
            Giá trị giảm <span class="text-danger">*</span>
        </label>
        <input id="GiaTriGiam"
               type="number"
               name="GiaTriGiam"
               value="{{ old('GiaTriGiam', isset($coupon) ? (float) $coupon->GiaTriGiam : '') }}"
               class="form-control @error('GiaTriGiam') is-invalid @enderror"
               min="0.01"
               step="0.01"
               placeholder="Nhập % (1-100) hoặc số tiền (đ)"
               required>
        @error('GiaTriGiam')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="DonHangToiThieu" class="form-label fw-semibold">
            Đơn hàng tối thiểu (đ)
        </label>
        <input id="DonHangToiThieu"
               type="number"
               name="DonHangToiThieu"
               value="{{ old('DonHangToiThieu', isset($coupon) ? (float) $coupon->DonHangToiThieu : 0) }}"
               class="form-control @error('DonHangToiThieu') is-invalid @enderror"
               min="0"
               step="1000"
               placeholder="0 (không yêu cầu tối thiểu)">
        <div class="form-text">Để 0 nếu áp dụng cho mọi giá trị đơn hàng.</div>
        @error('DonHangToiThieu')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="SoLanSuDungToiDa" class="form-label fw-semibold">
            Tổng số lượt sử dụng tối đa
        </label>
        <input id="SoLanSuDungToiDa"
               type="number"
               name="SoLanSuDungToiDa"
               value="{{ old('SoLanSuDungToiDa', $coupon->SoLanSuDungToiDa ?? '') }}"
               class="form-control @error('SoLanSuDungToiDa') is-invalid @enderror"
               min="1"
               step="1"
               placeholder="Để trống nếu không giới hạn">
        @error('SoLanSuDungToiDa')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="SoLanSuDungToiDaMoiNguoi" class="form-label fw-semibold">
            Số lượt tối đa mỗi khách hàng
        </label>
        <input id="SoLanSuDungToiDaMoiNguoi"
               type="number"
               name="SoLanSuDungToiDaMoiNguoi"
               value="{{ old('SoLanSuDungToiDaMoiNguoi', $coupon->SoLanSuDungToiDaMoiNguoi ?? '') }}"
               class="form-control @error('SoLanSuDungToiDaMoiNguoi') is-invalid @enderror"
               min="1"
               step="1"
               placeholder="Để trống nếu không giới hạn">
        @error('SoLanSuDungToiDaMoiNguoi')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="BatDauLuc" class="form-label fw-semibold">
            Thời gian bắt đầu <span class="text-danger">*</span>
        </label>
        @php
            $defaultStart = now()->format('Y-m-d\TH:i');
        @endphp
        <input id="BatDauLuc"
               type="datetime-local"
               name="BatDauLuc"
               value="{{ old('BatDauLuc', isset($coupon->BatDauLuc) ? date('Y-m-d\TH:i', strtotime($coupon->BatDauLuc)) : $defaultStart) }}"
               class="form-control @error('BatDauLuc') is-invalid @enderror"
               required>
        @error('BatDauLuc')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="KetThucLuc" class="form-label fw-semibold">
            Thời gian kết thúc <span class="text-danger">*</span>
        </label>
        @php
            $defaultEnd = now()->addDays(30)->format('Y-m-d\TH:i');
        @endphp
        <input id="KetThucLuc"
               type="datetime-local"
               name="KetThucLuc"
               value="{{ old('KetThucLuc', isset($coupon->KetThucLuc) ? date('Y-m-d\TH:i', strtotime($coupon->KetThucLuc)) : $defaultEnd) }}"
               class="form-control @error('KetThucLuc') is-invalid @enderror"
               required>
        @error('KetThucLuc')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="TrangThai" class="form-label fw-semibold">
            Trạng thái <span class="text-danger">*</span>
        </label>
        @php $currentStatus = old('TrangThai', $coupon->TrangThai ?? 'active'); @endphp
        <select id="TrangThai" name="TrangThai" class="form-select @error('TrangThai') is-invalid @enderror" required>
            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Kích hoạt (active)</option>
            <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>Ngừng kích hoạt (inactive)</option>
        </select>
        @error('TrangThai')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 d-flex gap-2 pt-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Lưu mã giảm giá
        </button>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>


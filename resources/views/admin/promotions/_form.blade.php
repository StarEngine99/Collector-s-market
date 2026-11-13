@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $checkedIds = collect(old('san_pham_ids', $selectedProductIds ?? []))
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div class="row g-3">
    <div class="col-12 col-md-8">
        <label for="TenKhuyenMai" class="form-label fw-semibold">
            Tên chương trình khuyến mãi <span class="text-danger">*</span>
        </label>
        <input id="TenKhuyenMai"
               type="text"
               name="TenKhuyenMai"
               value="{{ old('TenKhuyenMai', $promotion->TenKhuyenMai ?? '') }}"
               class="form-control @error('TenKhuyenMai') is-invalid @enderror"
               minlength="3"
               maxlength="180"
               placeholder="VD: Giảm giá tuần lễ Figure chính hãng"
               required>
        @error('TenKhuyenMai')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="TrangThai" class="form-label fw-semibold">
            Trạng thái <span class="text-danger">*</span>
        </label>
        @php $currentStatus = old('TrangThai', $promotion->TrangThai ?? 'active'); @endphp
        <select id="TrangThai" name="TrangThai" class="form-select @error('TrangThai') is-invalid @enderror" required>
            <option value="scheduled" {{ $currentStatus === 'scheduled' ? 'selected' : '' }}>Lên lịch (scheduled)</option>
            <option value="active" {{ $currentStatus === 'active' ? 'selected' : '' }}>Đang kích hoạt (active)</option>
            <option value="inactive" {{ $currentStatus === 'inactive' ? 'selected' : '' }}>Ngừng kích hoạt (inactive)</option>
        </select>
        @error('TrangThai')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-3">
        <label for="LoaiGiamGia" class="form-label fw-semibold">
            Loại giảm giá <span class="text-danger">*</span>
        </label>
        @php $currentType = old('LoaiGiamGia', $promotion->LoaiGiamGia ?? 'percent'); @endphp
        <select id="LoaiGiamGia" name="LoaiGiamGia" class="form-select @error('LoaiGiamGia') is-invalid @enderror" required>
            <option value="percent" {{ $currentType === 'percent' ? 'selected' : '' }}>Theo phần trăm (%)</option>
            <option value="fixed" {{ $currentType === 'fixed' ? 'selected' : '' }}>Số tiền cố định (đ)</option>
        </select>
        @error('LoaiGiamGia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-3">
        <label for="GiaTriGiam" class="form-label fw-semibold">
            Giá trị giảm <span class="text-danger">*</span>
        </label>
        <input id="GiaTriGiam"
               type="number"
               name="GiaTriGiam"
               value="{{ old('GiaTriGiam', isset($promotion) ? (float) $promotion->GiaTriGiam : '') }}"
               class="form-control @error('GiaTriGiam') is-invalid @enderror"
               min="0.01"
               step="0.01"
               placeholder="Nhập % hoặc số tiền"
               required>
        @error('GiaTriGiam')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-3">
        <label for="BatDauLuc" class="form-label fw-semibold">
            Bắt đầu lúc <span class="text-danger">*</span>
        </label>
        <input id="BatDauLuc"
               type="datetime-local"
               name="BatDauLuc"
               value="{{ old('BatDauLuc', isset($promotion->BatDauLuc) ? date('Y-m-d\TH:i', strtotime($promotion->BatDauLuc)) : '') }}"
               class="form-control @error('BatDauLuc') is-invalid @enderror"
               required>
        @error('BatDauLuc')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 col-md-3">
        <label for="KetThucLuc" class="form-label fw-semibold">
            Kết thúc lúc <span class="text-danger">*</span>
        </label>
        <input id="KetThucLuc"
               type="datetime-local"
               name="KetThucLuc"
               value="{{ old('KetThucLuc', isset($promotion->KetThucLuc) ? date('Y-m-d\TH:i', strtotime($promotion->KetThucLuc)) : '') }}"
               class="form-control @error('KetThucLuc') is-invalid @enderror"
               required>
        @error('KetThucLuc')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <label for="MoTa" class="form-label fw-semibold">Mô tả chương trình</label>
        <textarea id="MoTa"
                  name="MoTa"
                  rows="2"
                  maxlength="2000"
                  class="form-control @error('MoTa') is-invalid @enderror"
                  placeholder="Ghi chú ngắn về chương trình khuyến mãi...">{{ old('MoTa', $promotion->MoTa ?? '') }}</textarea>
        @error('MoTa')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-semibold mb-0">
                Chọn sản phẩm áp dụng khuyến mãi <span class="text-danger">*</span>
            </label>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="selectAllProductsBtn">Chọn tất cả</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllProductsBtn">Bỏ chọn</button>
            </div>
        </div>

        <div class="border rounded p-3 bg-white" style="max-height: 320px; overflow-y: auto;">
            @if ($products->isNotEmpty())
                <div class="row g-2">
                    @foreach ($products as $product)
                        @php $isChecked = in_array((int) $product->MaSanPham, $checkedIds, true); @endphp
                        <div class="col-12 col-md-6">
                            <div class="form-check border rounded p-2 ps-4 h-100">
                                <input class="form-check-input promotion-product-checkbox"
                                       type="checkbox"
                                       name="san_pham_ids[]"
                                       value="{{ $product->MaSanPham }}"
                                       id="prod_{{ $product->MaSanPham }}"
                                       {{ $isChecked ? 'checked' : '' }}>
                                <label class="form-check-label d-block small" for="prod_{{ $product->MaSanPham }}">
                                    <span class="fw-semibold">{{ $product->TenSanPham }}</span>
                                    <span class="text-muted">({{ $product->MaSKU }})</span>
                                    <div class="text-muted">
                                        {{ $product->TenDanhMuc }} — Giá gốc:
                                        <strong class="text-danger">{{ number_format((float) $product->Gia, 0, ',', '.') }} đ</strong>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted small text-center py-3">
                    Chưa có sản phẩm nào trong hệ thống để gắn khuyến mãi.
                </div>
            @endif
        </div>
        @error('san_pham_ids')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-12 d-flex gap-2 pt-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Lưu chương trình khuyến mãi
        </button>
        <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            const selectAllBtn = document.getElementById('selectAllProductsBtn');
            const clearAllBtn = document.getElementById('clearAllProductsBtn');
            const checkboxes = document.querySelectorAll('.promotion-product-checkbox');

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function () {
                    checkboxes.forEach(function (cb) { cb.checked = true; });
                });
            }
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', function () {
                    checkboxes.forEach(function (cb) { cb.checked = false; });
                });
            }
        })();
    </script>
@endpush

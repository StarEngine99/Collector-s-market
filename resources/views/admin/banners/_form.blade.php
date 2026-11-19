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
    <div class="col-12 col-md-8">
        <label for="TieuDe" class="form-label fw-semibold">Tiêu đề banner <span class="text-danger">*</span></label>
        <input id="TieuDe"
               type="text"
               name="TieuDe"
               value="{{ old('TieuDe', $banner->TieuDe ?? '') }}"
               class="form-control @error('TieuDe') is-invalid @enderror"
               minlength="2"
               maxlength="180"
               required>
    </div>

    <div class="col-12 col-md-4">
        <label for="ThuTuSapXep" class="form-label fw-semibold">Thứ tự hiển thị <span class="text-danger">*</span></label>
        <input id="ThuTuSapXep"
               type="number"
               name="ThuTuSapXep"
               value="{{ old('ThuTuSapXep', $banner->ThuTuSapXep ?? 0) }}"
               class="form-control @error('ThuTuSapXep') is-invalid @enderror"
               min="0"
               max="9999"
               required>
    </div>

    <div class="col-12 col-md-6">
        <label for="hinh_anh" class="form-label fw-semibold">
            Hình ảnh banner @if (!isset($banner)) <span class="text-danger">*</span> @endif
        </label>
        <input id="hinh_anh"
               type="file"
               name="hinh_anh"
               accept="image/jpeg,image/png,image/webp"
               class="form-control @error('hinh_anh') is-invalid @enderror"
               {{ isset($banner) ? '' : 'required' }}>
        <div class="form-text">Định dạng JPG/PNG/WEBP, dung lượng tối đa 2MB.</div>
        @if (isset($banner) && !empty($banner->DuongDanHinhAnh))
            <div class="mt-2">
                <img src="{{ str_starts_with($banner->DuongDanHinhAnh, 'http') ? $banner->DuongDanHinhAnh : asset($banner->DuongDanHinhAnh) }}"
                     alt="{{ $banner->TieuDe }}"
                     class="rounded border"
                     style="max-height: 110px;">
            </div>
        @endif
    </div>

    <div class="col-12 col-md-6">
        <label for="DuongDanDich" class="form-label fw-semibold">Đường dẫn đích khi bấm banner</label>
        <input id="DuongDanDich"
               type="text"
               name="DuongDanDich"
               value="{{ old('DuongDanDich', $banner->DuongDanDich ?? '') }}"
               class="form-control @error('DuongDanDich') is-invalid @enderror"
               maxlength="500"
               placeholder="vd: /danh-muc/mo-hinh">
    </div>

    <div class="col-12 col-md-4">
        <label for="BatDauLuc" class="form-label fw-semibold">Bắt đầu hiển thị</label>
        <input id="BatDauLuc"
               type="datetime-local"
               name="BatDauLuc"
               value="{{ old('BatDauLuc', isset($banner->BatDauLuc) ? date('Y-m-d\TH:i', strtotime($banner->BatDauLuc)) : '') }}"
               class="form-control @error('BatDauLuc') is-invalid @enderror">
    </div>

    <div class="col-12 col-md-4">
        <label for="KetThucLuc" class="form-label fw-semibold">Kết thúc hiển thị</label>
        <input id="KetThucLuc"
               type="datetime-local"
               name="KetThucLuc"
               value="{{ old('KetThucLuc', isset($banner->KetThucLuc) ? date('Y-m-d\TH:i', strtotime($banner->KetThucLuc)) : '') }}"
               class="form-control @error('KetThucLuc') is-invalid @enderror">
    </div>

    <div class="col-12 col-md-4">
        <label for="TrangThai" class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
        @php $currentBannerStatus = old('TrangThai', $banner->TrangThai ?? 'active'); @endphp
        <select id="TrangThai" name="TrangThai" class="form-select @error('TrangThai') is-invalid @enderror" required>
            <option value="active" {{ $currentBannerStatus === 'active' ? 'selected' : '' }}>Hiển thị (active)</option>
            <option value="inactive" {{ $currentBannerStatus === 'inactive' ? 'selected' : '' }}>Tắt (inactive)</option>
        </select>
    </div>

    <div class="col-12 d-flex gap-2 pt-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Lưu banner
        </button>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>

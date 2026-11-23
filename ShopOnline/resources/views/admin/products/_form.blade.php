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
    $currentCategoryId = old('MaDanhMuc', $product->MaDanhMuc ?? '');
    $currentStatus = old('TrangThai', $product->TrangThai ?? 'draft');
    $removedImageIds = old('XoaHinhAnh', []);
    $removedImageIds = is_array($removedImageIds) ? array_map('strval', $removedImageIds) : [];
@endphp

<div class="row g-3">
    <div class="col-12 col-md-8">
        <label for="TenSanPham" class="form-label fw-semibold">Tên sản phẩm <span class="text-danger">*</span></label>
        <input id="TenSanPham"
               name="TenSanPham"
               value="{{ old('TenSanPham', $product->TenSanPham ?? '') }}"
               class="form-control @error('TenSanPham') is-invalid @enderror"
               maxlength="200"
               required>
    </div>

    <div class="col-12 col-md-4">
        <label for="MaSKU" class="form-label fw-semibold">Mã SKU <span class="text-danger">*</span></label>
        <input id="MaSKU"
               name="MaSKU"
               value="{{ old('MaSKU', $product->MaSKU ?? '') }}"
               class="form-control @error('MaSKU') is-invalid @enderror"
               maxlength="80"
               required>
    </div>

    <div class="col-12 col-md-6">
        <label for="MaDanhMuc" class="form-label fw-semibold">Danh mục <span class="text-danger">*</span></label>
        <select id="MaDanhMuc" name="MaDanhMuc" class="form-select @error('MaDanhMuc') is-invalid @enderror" required>
            <option value="">Chọn danh mục</option>
            @foreach ($categories as $category)
                <option value="{{ $category->MaDanhMuc }}"
                        @selected((string) $currentCategoryId === (string) $category->MaDanhMuc)>
                    {{ $category->TenDanhMuc }}{{ $category->TrangThai === 'hidden' ? ' (đang ẩn)' : '' }}
                </option>
            @endforeach
        </select>
        @if ($categories->isEmpty())
            <div class="form-text text-danger">Cần có danh mục chưa bị xóa trước khi tạo sản phẩm.</div>
        @endif
    </div>

    <div class="col-12 col-md-3">
        <label for="Gia" class="form-label fw-semibold">Giá bán (₫) <span class="text-danger">*</span></label>
        <input id="Gia"
               type="number"
               name="Gia"
               value="{{ old('Gia', $product->Gia ?? '') }}"
               class="form-control @error('Gia') is-invalid @enderror"
               min="0"
               max="9999999999.99"
               step="0.01"
               required>
    </div>

    <div class="col-12 col-md-3">
        <label for="SoLuongTon" class="form-label fw-semibold">Số lượng tồn <span class="text-danger">*</span></label>
        <input id="SoLuongTon"
               type="number"
               name="SoLuongTon"
               value="{{ old('SoLuongTon', $product->SoLuongTon ?? 0) }}"
               class="form-control @error('SoLuongTon') is-invalid @enderror"
               min="0"
               required>
    </div>

    <div class="col-12 col-md-6">
        <label for="TrangThai" class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
        <select id="TrangThai" name="TrangThai" class="form-select @error('TrangThai') is-invalid @enderror" required>
            <option value="draft" @selected($currentStatus === 'draft')>Bản nháp</option>
            <option value="active" @selected($currentStatus === 'active')>Đang bán</option>
            <option value="hidden" @selected($currentStatus === 'hidden')>Đang ẩn</option>
        </select>
    </div>

    <div class="col-12 col-md-6 d-flex align-items-end">
        <div class="form-check mb-2">
            <input type="hidden" name="NoiBat" value="0">
            <input id="NoiBat"
                   type="checkbox"
                   name="NoiBat"
                   value="1"
                   class="form-check-input"
                   @checked((bool) old('NoiBat', $product->NoiBat ?? false))>
            <label for="NoiBat" class="form-check-label">Đánh dấu sản phẩm nổi bật</label>
        </div>
    </div>

    <div class="col-12">
        <label for="MoTa" class="form-label fw-semibold">Mô tả sản phẩm</label>
        <textarea id="MoTa"
                  name="MoTa"
                  rows="5"
                  maxlength="20000"
                  class="form-control @error('MoTa') is-invalid @enderror">{{ old('MoTa', $product->MoTa ?? '') }}</textarea>
    </div>

    @if ($images->isNotEmpty())
        <div class="col-12">
            <div class="form-label fw-semibold">Ảnh hiện có</div>
            <div class="d-flex flex-wrap gap-3">
                @foreach ($images as $image)
                    <label class="border rounded p-2 text-center">
                        <img src="{{ asset($image->DuongDanHinhAnh) }}"
                             alt="{{ $image->MoTaHinhAnh ?? $product->TenSanPham }}"
                             class="d-block rounded mb-2"
                             style="width: 110px; height: 110px; object-fit: cover;">
                        <span class="small">
                            <input type="checkbox"
                                   name="XoaHinhAnh[]"
                                   value="{{ $image->MaHinhAnh }}"
                                   @checked(in_array((string) $image->MaHinhAnh, $removedImageIds, true))>
                            Xóa ảnh
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    <div class="col-12">
        <label for="HinhAnh" class="form-label fw-semibold">Tải ảnh sản phẩm</label>
        <input id="HinhAnh"
               type="file"
               name="HinhAnh[]"
               accept="image/jpeg,image/png,image/webp"
               multiple
               class="form-control @error('HinhAnh') is-invalid @enderror">
        <div class="form-text">JPG/PNG/WEBP, tối đa 5MB mỗi ảnh và tối đa 5 ảnh cho mỗi sản phẩm.</div>
    </div>

    <div class="col-12 d-flex gap-2 pt-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Lưu sản phẩm
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>

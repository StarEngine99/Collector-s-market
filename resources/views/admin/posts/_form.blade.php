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
        <label for="TieuDe" class="form-label fw-semibold">Tiêu đề <span class="text-danger">*</span></label>
        <input id="TieuDe"
               type="text"
               name="TieuDe"
               value="{{ old('TieuDe', $post->TieuDe ?? '') }}"
               class="form-control @error('TieuDe') is-invalid @enderror"
               minlength="3"
               maxlength="200"
               required>
    </div>

    <div class="col-12 col-md-4">
        <label for="DuongDan" class="form-label fw-semibold">Đường dẫn (slug) <span class="text-danger">*</span></label>
        <input id="DuongDan"
               type="text"
               name="DuongDan"
               value="{{ old('DuongDan', $post->DuongDan ?? '') }}"
               class="form-control @error('DuongDan') is-invalid @enderror"
               maxlength="220"
               pattern="[a-z0-9-]+"
               placeholder="vd: huong-dan-bao-quan-figure"
               required>
    </div>

    <div class="col-12 col-md-6">
        <label for="TrangThai" class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
        @php $currentStatus = old('TrangThai', $post->TrangThai ?? 'draft'); @endphp
        <select id="TrangThai" name="TrangThai" class="form-select @error('TrangThai') is-invalid @enderror" required>
            <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>Bản nháp (draft)</option>
            <option value="published" {{ $currentStatus === 'published' ? 'selected' : '' }}>Xuất bản (published)</option>
            <option value="hidden" {{ $currentStatus === 'hidden' ? 'selected' : '' }}>Ẩn (hidden)</option>
        </select>
    </div>

    <div class="col-12 col-md-6">
        <label for="NgayDang" class="form-label fw-semibold">Ngày đăng</label>
        @php
            $ngayDangVal = old('NgayDang', isset($post->NgayDang) ? date('Y-m-d\TH:i', strtotime($post->NgayDang)) : '');
        @endphp
        <input id="NgayDang"
               type="datetime-local"
               name="NgayDang"
               value="{{ $ngayDangVal }}"
               class="form-control @error('NgayDang') is-invalid @enderror">
    </div>

    <div class="col-12">
        <label for="NoiDung" class="form-label fw-semibold">Nội dung <span class="text-danger">*</span></label>
        <textarea id="NoiDung"
                  name="NoiDung"
                  rows="10"
                  class="form-control @error('NoiDung') is-invalid @enderror"
                  minlength="10"
                  required>{{ old('NoiDung', $post->NoiDung ?? '') }}</textarea>
    </div>

    <div class="col-12 d-flex gap-2 pt-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Lưu bài viết
        </button>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</div>

@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm - Quản trị')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <h1 class="h4 fw-bold mb-0">Quản lý sản phẩm</h1>
        @can('products.create')
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Thêm sản phẩm
            </a>
        @endcan
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="keyword" class="form-label">Tên sản phẩm hoặc SKU</label>
                    <input id="keyword" name="keyword" value="{{ $filters['keyword'] }}" class="form-control" placeholder="Nhập từ khóa">
                </div>
                <div class="col-6 col-md-3">
                    <label for="category_id" class="form-label">Danh mục</label>
                    <select id="category_id" name="category_id" class="form-select">
                        <option value="">Tất cả danh mục</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->MaDanhMuc }}" @selected((string) $filters['category_id'] === (string) $category->MaDanhMuc)>
                                {{ $category->TenDanhMuc }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="active" @selected($filters['status'] === 'active')>Đang bán</option>
                        <option value="draft" @selected($filters['status'] === 'draft')>Bản nháp</option>
                        <option value="hidden" @selected($filters['status'] === 'hidden')>Đang ẩn</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label for="stock" class="form-label">Tồn kho</label>
                    <select id="stock" name="stock" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="in_stock" @selected($filters['stock'] === 'in_stock')>Còn hàng</option>
                        <option value="out_of_stock" @selected($filters['stock'] === 'out_of_stock')>Hết hàng</option>
                    </select>
                </div>
                <div class="col-6 col-md-1 d-grid">
                    <button type="submit" class="btn btn-outline-primary">Lọc</button>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Ảnh</th>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th class="text-end">Giá</th>
                        <th class="text-center">Tồn kho</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                @if ($product->DuongDanHinhAnh)
                                    <img src="{{ asset($product->DuongDanHinhAnh) }}"
                                         alt="{{ $product->TenSanPham }}"
                                         class="rounded border"
                                         style="width: 56px; height: 56px; object-fit: contain; background-color: #fff;">
                                @else
                                    <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted"
                                         style="width: 56px; height: 56px;" aria-label="Chưa có ảnh">
                                        <i class="bi bi-image"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $product->TenSanPham }}</div>
                                <div class="small text-muted">SKU: {{ $product->MaSKU }}</div>
                                @if ($product->NoiBat)
                                    <span class="badge text-bg-warning mt-1">Nổi bật</span>
                                @endif
                            </td>
                            <td>{{ $product->TenDanhMuc }}</td>
                            <td class="text-end">{{ number_format((float) $product->Gia, 0, ',', '.') }} ₫</td>
                            <td class="text-center">{{ number_format((int) $product->SoLuongTon, 0, ',', '.') }}</td>
                            <td>
                                @if ($product->TrangThai === 'active')
                                    <span class="badge bg-success">Đang bán</span>
                                @elseif ($product->TrangThai === 'draft')
                                    <span class="badge bg-secondary">Bản nháp</span>
                                @else
                                    <span class="badge bg-warning text-dark">Đang ẩn</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @can('products.update')
                                    <a href="{{ route('admin.products.edit', $product->MaSanPham) }}"
                                       class="btn btn-outline-primary btn-sm" aria-label="Sửa {{ $product->TenSanPham }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endcan
                                @can('products.delete')
                                    <form method="POST"
                                          action="{{ route('admin.products.destroy', $product->MaSanPham) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Xóa {{ $product->TenSanPham }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Chưa có sản phẩm phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Tìm kiếm & Lọc sản phẩm - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <h1 class="h4 fw-bold mb-0">
            <i class="bi bi-search me-2 text-primary"></i>Tìm kiếm &amp; Lọc sản phẩm
        </h1>
        <span class="badge bg-secondary-subtle text-dark align-self-start align-self-sm-center">
            {{ $products->total() }} kết quả
        </span>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        {{-- Bộ lọc --}}
        <aside class="col-12 col-lg-3">
            <form method="GET" action="{{ route('products.search') }}" class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-funnel me-2 text-primary"></i>Bộ lọc
                </div>
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <label for="filterKeyword" class="form-label small fw-semibold">Tên sản phẩm</label>
                        <input id="filterKeyword"
                               type="text"
                               name="q"
                               value="{{ $filters['q'] ?? '' }}"
                               maxlength="120"
                               class="form-control form-control-sm"
                               placeholder="Nhập tên mô hình, thẻ bài...">
                    </div>

                    <div>
                        <label for="filterCategory" class="form-label small fw-semibold">Danh mục</label>
                        <select id="filterCategory" name="category" class="form-select form-select-sm">
                            <option value="">Tất cả danh mục</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->DuongDan }}"
                                        {{ ($filters['category'] ?? '') === $cat->DuongDan ? 'selected' : '' }}>
                                    {{ $cat->TenDanhMuc }}
                                </option>
                                @foreach ($cat->children as $child)
                                    <option value="{{ $child->DuongDan }}"
                                            {{ ($filters['category'] ?? '') === $child->DuongDan ? 'selected' : '' }}>
                                        — {{ $child->TenDanhMuc }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label small fw-semibold">Khoảng giá (VNĐ)</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number"
                                       name="min_price"
                                       value="{{ $filters['min_price'] ?? '' }}"
                                       min="0"
                                       step="1000"
                                       class="form-control form-control-sm"
                                       placeholder="Từ">
                            </div>
                            <div class="col-6">
                                <input type="number"
                                       name="max_price"
                                       value="{{ $filters['max_price'] ?? '' }}"
                                       min="0"
                                       step="1000"
                                       class="form-control form-control-sm"
                                       placeholder="Đến">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="filterSort" class="form-label small fw-semibold">Sắp xếp</label>
                        <select id="filterSort" name="sort" class="form-select form-select-sm">
                            <option value="newest" {{ ($filters['sort'] ?? 'newest') === 'newest' ? 'selected' : '' }}>
                                Mới nhất
                            </option>
                            <option value="price_asc" {{ ($filters['sort'] ?? '') === 'price_asc' ? 'selected' : '' }}>
                                Giá: Thấp đến cao
                            </option>
                            <option value="price_desc" {{ ($filters['sort'] ?? '') === 'price_desc' ? 'selected' : '' }}>
                                Giá: Cao đến thấp
                            </option>
                        </select>
                    </div>

                    <div class="d-grid gap-2 pt-1">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-check2-circle me-1"></i>Áp dụng lọc
                        </button>
                        <a href="{{ route('products.search') }}" class="btn btn-outline-secondary btn-sm">
                            Đặt lại
                        </a>
                    </div>
                </div>
            </form>
        </aside>

        {{-- Kết quả --}}
        <div class="col-12 col-lg-9">
            @if ($products->isNotEmpty())
                <div class="row row-cols-2 row-cols-md-3 g-3">
                    @foreach ($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            @else
                <div class="alert alert-light border text-muted">
                    Không tìm thấy sản phẩm nào phù hợp với điều kiện lọc hiện tại.
                </div>
            @endif
        </div>
    </div>
@endsection

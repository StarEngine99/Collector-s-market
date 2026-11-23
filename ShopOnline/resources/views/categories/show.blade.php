@extends('layouts.app')

@section('title', $category->TenDanhMuc . ' - Chợ Figure & Thẻ Bài')

@section('content')
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item">
                <a href="{{ Route::has('home') ? route('home') : url('/') }}" class="text-decoration-none">Trang chủ</a>
            </li>
            @if ($parentCategory !== null)
                <li class="breadcrumb-item">
                    <a href="{{ route('categories.show', $parentCategory->DuongDan) }}" class="text-decoration-none">
                        {{ $parentCategory->TenDanhMuc }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $category->TenDanhMuc }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        {{-- Sidebar menu danh mục --}}
        <aside class="col-12 col-lg-3">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-grid me-2 text-primary"></i>Danh mục sản phẩm
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($menuCategories as $menuItem)
                        <a href="{{ route('categories.show', $menuItem->DuongDan) }}"
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $menuItem->MaDanhMuc === $category->MaDanhMuc ? 'active' : '' }}">
                            <span>{{ $menuItem->TenDanhMuc }}</span>
                        </a>
                        @foreach ($menuItem->children as $child)
                            <a href="{{ route('categories.show', $child->DuongDan) }}"
                               class="list-group-item list-group-item-action ps-4 small {{ $child->MaDanhMuc === $category->MaDanhMuc ? 'active' : '' }}">
                                <i class="bi bi-chevron-right me-1"></i>{{ $child->TenDanhMuc }}
                            </a>
                        @endforeach
                    @empty
                        <div class="list-group-item text-muted small">Chưa có danh mục.</div>
                    @endforelse
                </div>
            </div>
        </aside>

        {{-- Danh sách sản phẩm & phân trang --}}
        <div class="col-12 col-lg-9">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <h1 class="h4 fw-bold mb-1">{{ $category->TenDanhMuc }}</h1>
                    @if (!empty($category->MoTa))
                        <p class="text-muted small mb-0">{{ $category->MoTa }}</p>
                    @endif
                </div>
                <span class="badge bg-secondary-subtle text-dark align-self-start align-self-sm-center">
                    {{ $products->total() }} sản phẩm
                </span>
            </div>

            @if ($products->isNotEmpty())
                <div class="row row-cols-2 row-cols-md-3 g-3">
                    @foreach ($products as $product)
                        @include('partials.product-card', ['product' => $product])
                    @endforeach
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $products->withQueryString()->links() }}
                </div>
            @else
                <div class="alert alert-light border text-muted">
                    Hiện chưa có sản phẩm nào trong danh mục này.
                </div>
            @endif
        </div>
    </div>
@endsection

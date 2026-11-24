@extends('layouts.app')

@section('title', 'Kết quả tìm kiếm bằng hình ảnh - Chợ Figure & Thẻ Bài')

@section('content')
<div class="py-2">
    <div class="p-4 rounded-4 hero-animated-banner text-white shadow-sm mb-4">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <img src="{{ $uploadedImage }}"
                 alt="Ảnh tìm kiếm"
                 class="rounded-3 border border-white border-2 shadow"
                 style="width: 80px; height: 80px; object-fit: contain; background-color: #fff;">
            <div>
                <h1 class="h4 fw-bold mb-1">Kết quả tìm kiếm bằng hình ảnh</h1>
                <p class="mb-0 text-white-50">Đã tìm thấy {{ $totalCount }} sản phẩm có đặc điểm thị giác tương đồng nhất.</p>
            </div>
        </div>
    </div>

    @if ($products->isNotEmpty())
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 mb-5">
            @foreach ($products as $product)
                <div class="col position-relative">
                    {{-- Badge hiển thị độ tương đồng --}}
                    <div class="position-absolute top-0 end-0 m-2 me-3 z-3">
                        <span class="badge bg-success bg-opacity-90 shadow-sm border border-white">
                            <i class="bi bi-bullseye me-1"></i>Độ khớp: {{ $product->DoTuongDong }}%
                        </span>
                    </div>
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5 bg-white rounded-4 shadow-sm border p-4">
            <i class="bi bi-camera fs-1 text-muted d-block mb-2"></i>
            <h3 class="h5 fw-bold text-muted">Không tìm thấy sản phẩm tương đồng phù hợp</h3>
            <p class="text-muted small mb-3">Vui lòng thử lại với một góc chụp khác rõ nét hơn.</p>
            <a href="{{ route('home') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Về trang chủ
            </a>
        </div>
    @endif
</div>
@endsection

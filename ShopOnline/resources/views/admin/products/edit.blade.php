@extends('layouts.admin')

@section('title', 'Sửa sản phẩm - Quản trị')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 fw-bold mb-0">Sửa sản phẩm</h1>
        @can('products.delete')
            <form method="POST"
                  action="{{ route('admin.products.destroy', $product->MaSanPham) }}"
                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash me-1"></i>Xóa sản phẩm
                </button>
            </form>
        @endcan
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST"
                  action="{{ route('admin.products.update', $product->MaSanPham) }}"
                  enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.products._form')
            </form>
        </div>
    </div>
@endsection

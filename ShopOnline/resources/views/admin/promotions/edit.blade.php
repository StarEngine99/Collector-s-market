@extends('layouts.admin')

@section('title', 'Chỉnh sửa chương trình khuyến mãi - Quản trị')

@section('content')
    <h1 class="h4 fw-bold mb-4">Chỉnh sửa chương trình khuyến mãi #{{ $promotion->MaKhuyenMai }}</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.promotions.update', $promotion->MaKhuyenMai) }}">
                @csrf
                @method('PUT')
                @include('admin.promotions._form', ['promotion' => $promotion])
            </form>
        </div>
    </div>
@endsection

@extends('layouts.admin')

@section('title', 'Chỉnh sửa mã giảm giá - Quản trị')

@section('content')
    <h1 class="h4 fw-bold mb-4">Chỉnh sửa mã giảm giá: {{ $coupon->MaUuDai }}</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.coupons.update', $coupon->MaPhieuGiamGia) }}">
                @csrf
                @method('PUT')
                @include('admin.coupons._form', ['coupon' => $coupon])
            </form>
        </div>
    </div>
@endsection


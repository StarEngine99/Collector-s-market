@extends('layouts.admin')

@section('title', 'Thêm mã giảm giá - Quản trị')

@section('content')
    <h1 class="h4 fw-bold mb-4">Thêm mã giảm giá mới</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.coupons.store') }}">
                @csrf
                @include('admin.coupons._form')
            </form>
        </div>
    </div>
@endsection


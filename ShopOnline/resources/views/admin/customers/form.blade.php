@extends('layouts.admin')

@section('title', $heading . ' | Quản trị Collector\'s Market')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold mb-1">{{ $heading }}</h1>
        <p class="text-muted mb-0">Tài khoản được tạo ở vai trò khách hàng; quyền quản trị không thể thay đổi tại đây.</p>
    </div>
    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Quay lại danh sách</a>
</div>

@if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ $formAction }}" method="POST">
            @csrf
            @if($formMethod !== 'POST')
                @method($formMethod)
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="HoTen" class="form-label">Họ và tên</label>
                    <input id="HoTen" name="HoTen" value="{{ old('HoTen', $customer->HoTen ?? '') }}"
                           class="form-control" required maxlength="150" autocomplete="name">
                </div>
                <div class="col-md-6">
                    <label for="Email" class="form-label">Email</label>
                    <input id="Email" name="Email" type="email" value="{{ old('Email', $customer->Email ?? '') }}"
                           class="form-control" required maxlength="190" autocomplete="email">
                </div>
                <div class="col-md-6">
                    <label for="SoDienThoai" class="form-label">Số điện thoại</label>
                    <input id="SoDienThoai" name="SoDienThoai"
                           value="{{ old('SoDienThoai', $customer->SoDienThoai ?? '') }}"
                           class="form-control" maxlength="30" autocomplete="tel">
                </div>
                <div class="col-md-6">
                    <label for="MatKhau" class="form-label">
                        {{ $customer ? 'Mật khẩu mới (để trống nếu không đổi)' : 'Mật khẩu' }}
                    </label>
                    <input id="MatKhau" name="MatKhau" type="password" class="form-control"
                           {{ $customer ? '' : 'required' }} minlength="8" autocomplete="new-password">
                </div>
                <div class="col-md-6">
                    <label for="MatKhau_confirmation" class="form-label">Xác nhận mật khẩu</label>
                    <input id="MatKhau_confirmation" name="MatKhau_confirmation" type="password"
                           class="form-control" minlength="8" autocomplete="new-password">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Lưu tài khoản</button>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

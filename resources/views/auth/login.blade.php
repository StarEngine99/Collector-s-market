@extends('layouts.app')

@section('title', 'Đăng nhập - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="row justify-content-center py-3">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-2"
                             style="width: 56px; height: 56px;">
                            <i class="bi bi-box-arrow-in-right fs-3"></i>
                        </div>
                        <h1 class="h4 fw-bold mb-1">Đăng nhập tài khoản</h1>
                        <p class="text-muted small mb-0">Đăng nhập để quản lý giỏ hàng và theo dõi đơn hàng của bạn</p>
                    </div>

                    <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
                        @csrf

                        <div class="mb-3">
                            <label for="Email" class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                <input id="Email"
                                       type="email"
                                       name="Email"
                                       value="{{ old('Email') }}"
                                       class="form-control @error('Email') is-invalid @enderror"
                                       placeholder="nhapemail@example.com"
                                       maxlength="190"
                                       autocomplete="email"
                                       required
                                       autofocus>
                                @error('Email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="MatKhau" class="form-label fw-semibold mb-1">
                                    Mật khẩu <span class="text-danger">*</span>
                                </label>
                                <a href="{{ route('password.request') }}" class="small text-decoration-none">
                                    Quên mật khẩu?
                                </a>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                <input id="MatKhau"
                                       type="password"
                                       name="MatKhau"
                                       class="form-control @error('MatKhau') is-invalid @enderror"
                                       placeholder="Nhập mật khẩu của bạn"
                                       maxlength="72"
                                       autocomplete="current-password"
                                       required>
                                @error('MatKhau')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 form-check">
                            <input id="ghi_nho"
                                   type="checkbox"
                                   name="ghi_nho"
                                   value="1"
                                   class="form-check-input"
                                   {{ old('ghi_nho') ? 'checked' : '' }}>
                            <label for="ghi_nho" class="form-check-label small">
                                Ghi nhớ đăng nhập trên thiết bị này
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Đăng nhập
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center small">
                        Chưa có tài khoản?
                        <a href="{{ route('register') }}" class="fw-semibold text-decoration-none">
                            Đăng ký ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('title', 'Đăng ký tài khoản - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="row justify-content-center py-3">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-2"
                             style="width: 56px; height: 56px;">
                            <i class="bi bi-person-plus fs-3"></i>
                        </div>
                        <h1 class="h4 fw-bold mb-1">Tạo tài khoản mới</h1>
                        <p class="text-muted small mb-0">Trở thành thành viên của Chợ Figure &amp; Thẻ Bài để đặt hàng nhanh chóng</p>
                    </div>

                    <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="HoTen" class="form-label fw-semibold">
                                    Họ và tên <span class="text-danger">*</span>
                                </label>
                                <input id="HoTen"
                                       type="text"
                                       name="HoTen"
                                       value="{{ old('HoTen') }}"
                                       class="form-control @error('HoTen') is-invalid @enderror"
                                       placeholder="Ví dụ: Nguyễn Văn A"
                                       minlength="2"
                                       maxlength="150"
                                       autocomplete="name"
                                       required
                                       autofocus>
                                @error('HoTen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="Email" class="form-label fw-semibold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input id="Email"
                                       type="email"
                                       name="Email"
                                       value="{{ old('Email') }}"
                                       class="form-control @error('Email') is-invalid @enderror"
                                       placeholder="nhapemail@example.com"
                                       maxlength="190"
                                       autocomplete="email"
                                       required>
                                @error('Email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="SoDienThoai" class="form-label fw-semibold">
                                    Số điện thoại
                                </label>
                                <input id="SoDienThoai"
                                       type="tel"
                                       name="SoDienThoai"
                                       value="{{ old('SoDienThoai') }}"
                                       class="form-control @error('SoDienThoai') is-invalid @enderror"
                                       placeholder="0912 345 678"
                                       pattern="[0-9+\-\s().]{8,20}"
                                       maxlength="30"
                                       autocomplete="tel">
                                @error('SoDienThoai')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="MatKhau" class="form-label fw-semibold">
                                    Mật khẩu <span class="text-danger">*</span>
                                </label>
                                <input id="MatKhau"
                                       type="password"
                                       name="MatKhau"
                                       class="form-control @error('MatKhau') is-invalid @enderror"
                                       placeholder="Tối thiểu 8 ký tự"
                                       minlength="8"
                                       maxlength="72"
                                       autocomplete="new-password"
                                       required>
                                @error('MatKhau')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="MatKhau_confirmation" class="form-label fw-semibold">
                                    Xác nhận mật khẩu <span class="text-danger">*</span>
                                </label>
                                <input id="MatKhau_confirmation"
                                       type="password"
                                       name="MatKhau_confirmation"
                                       class="form-control"
                                       placeholder="Nhập lại mật khẩu"
                                       minlength="8"
                                       maxlength="72"
                                       autocomplete="new-password"
                                       required>
                                <div class="invalid-feedback" id="passwordConfirmFeedback">
                                    Xác nhận mật khẩu không khớp.
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                                    <i class="bi bi-check2-circle me-1"></i>Đăng ký tài khoản
                                </button>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center small">
                        Đã có tài khoản?
                        <a href="{{ route('login') }}" class="fw-semibold text-decoration-none">
                            Đăng nhập tại đây
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const passwordInput = document.getElementById('MatKhau');
            const confirmInput = document.getElementById('MatKhau_confirmation');

            if (!passwordInput || !confirmInput) {
                return;
            }

            function validatePasswordMatch() {
                if (confirmInput.value && passwordInput.value !== confirmInput.value) {
                    confirmInput.setCustomValidity('Xác nhận mật khẩu không khớp.');
                    confirmInput.classList.add('is-invalid');
                } else {
                    confirmInput.setCustomValidity('');
                    confirmInput.classList.remove('is-invalid');
                }
            }

            passwordInput.addEventListener('input', validatePasswordMatch);
            confirmInput.addEventListener('input', validatePasswordMatch);
        })();
    </script>
@endpush


@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="row justify-content-center py-3">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-2"
                             style="width: 56px; height: 56px;">
                            <i class="bi bi-shield-lock fs-3"></i>
                        </div>
                        <h1 class="h4 fw-bold mb-1">Đặt lại mật khẩu mới</h1>
                        <p class="text-muted small mb-0">Vui lòng nhập mật khẩu mới cho tài khoản của bạn</p>
                    </div>

                    <form method="POST" action="{{ route('password.update') }}" id="resetPasswordForm">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="mb-3">
                            <label for="Email" class="form-label fw-semibold">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input id="Email"
                                   type="email"
                                   name="Email"
                                   value="{{ old('Email', $email) }}"
                                   class="form-control @error('Email') is-invalid @enderror"
                                   maxlength="190"
                                   autocomplete="email"
                                   required>
                            @error('Email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="MatKhau" class="form-label fw-semibold">
                                Mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <input id="MatKhau"
                                   type="password"
                                   name="MatKhau"
                                   class="form-control @error('MatKhau') is-invalid @enderror"
                                   placeholder="Tối thiểu 8 ký tự"
                                   minlength="8"
                                   maxlength="72"
                                   autocomplete="new-password"
                                   required
                                   autofocus>
                            @error('MatKhau')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="MatKhau_confirmation" class="form-label fw-semibold">
                                Xác nhận mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <input id="MatKhau_confirmation"
                                   type="password"
                                   name="MatKhau_confirmation"
                                   class="form-control"
                                   placeholder="Nhập lại mật khẩu mới"
                                   minlength="8"
                                   maxlength="72"
                                   autocomplete="new-password"
                                   required>
                            <div class="invalid-feedback">
                                Xác nhận mật khẩu mới không khớp.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i>Cập nhật mật khẩu
                        </button>
                    </form>
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
                    confirmInput.setCustomValidity('Xác nhận mật khẩu mới không khớp.');
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


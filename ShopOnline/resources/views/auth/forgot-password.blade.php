@extends('layouts.app')

@section('title', 'Quên mật khẩu - Chợ Figure & Thẻ Bài')

@section('content')
    <div class="row justify-content-center py-3">
        <div class="col-12 col-md-8 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-2"
                             style="width: 56px; height: 56px;">
                            <i class="bi bi-key fs-3"></i>
                        </div>
                        <h1 class="h4 fw-bold mb-1">Quên mật khẩu?</h1>
                        <p class="text-muted small mb-0">
                            Nhập địa chỉ email đã đăng ký, chúng tôi sẽ gửi liên kết để bạn đặt lại mật khẩu mới.
                        </p>
                    </div>

                    @if (session('demo_reset_url'))
                        <div class="alert alert-warning small break-word mb-4" role="alert">
                            <div class="fw-bold mb-1">
                                <i class="bi bi-info-circle-fill me-1"></i>Chế độ Demo (PASSWORD_RESET_DEMO=true)
                            </div>
                            <div class="mb-2">Liên kết đặt lại mật khẩu của bạn (hiệu lực 60 phút):</div>
                            <a href="{{ session('demo_reset_url') }}" class="fw-semibold text-break">
                                {{ session('demo_reset_url') }}
                            </a>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="Email" class="form-label fw-semibold">
                                Email đã đăng ký <span class="text-danger">*</span>
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

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="bi bi-send me-1"></i>Gửi liên kết đặt lại mật khẩu
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="text-center small">
                        <a href="{{ route('login') }}" class="text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i>Quay lại trang đăng nhập
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


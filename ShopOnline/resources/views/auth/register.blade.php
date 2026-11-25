@extends('layouts.app')

@section('title', 'Đăng ký tài khoản - Chợ Figure & Thẻ Bài')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth-lamp.css') }}">
<style>
body > header,
body > footer { display: none !important; }
body.d-flex.flex-column main.flex-grow-1 {
    min-height: 100vh;
    padding: 0 !important;
}
body.d-flex.flex-column main.flex-grow-1 > .container {
    max-width: none;
    width: 100%;
    min-height: 100vh;
    padding: 0;
}
/* ============================================================
   Auth Card – Glassmorphism (shared with login)
   ============================================================ */
.auth-glass-card {
    background: rgba(15, 23, 42, 0.72);
    backdrop-filter: blur(22px);
    -webkit-backdrop-filter: blur(22px);
    border: 1px solid rgba(255, 255, 255, 0.10);
    border-radius: 20px;
    box-shadow:
        0 24px 60px rgba(0, 0, 0, 0.55),
        0 0 0 1px rgba(192, 132, 252, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.07);
}

.auth-icon-wrap {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #38bdf8 0%, #818cf8 50%, #c084fc 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 28px rgba(192, 132, 252, 0.38);
    margin-bottom: 1rem;
}

.auth-title {
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #f0f4ff;
    margin-bottom: .3rem;
}

.auth-subtitle {
    font-size: .86rem;
    color: rgba(190, 200, 220, 0.72);
    margin-bottom: 0;
}

.auth-label {
    font-size: .82rem;
    font-weight: 600;
    color: rgba(190, 200, 220, 0.85);
    margin-bottom: .4rem;
    display: block;
}

/* Input group */
.auth-input-group .input-group-text {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-right: none;
    color: rgba(148, 163, 184, 0.9);
    border-radius: 10px 0 0 10px;
}

.auth-input-group .form-control {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-left: none;
    border-radius: 0 10px 10px 0;
    color: #f0f4ff;
    font-size: .91rem;
    padding: .68rem 1rem;
    transition: border-color .25s ease, box-shadow .25s ease, background .25s ease;
}

.auth-input-group.has-toggle .form-control {
    border-radius: 0;
}

.auth-input-group .form-control::placeholder {
    color: rgba(100, 116, 139, 0.75);
}

.auth-input-group .form-control:focus {
    background: rgba(255, 255, 255, 0.08);
    border-color: #c084fc;
    box-shadow: 0 0 0 3px rgba(192, 132, 252, 0.15), 0 0 20px rgba(192, 132, 252, 0.08);
    color: #f0f4ff;
    outline: none;
}

.auth-input-group .form-control.is-invalid {
    border-color: #f87171;
    box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.15);
}

.auth-eye-btn {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-left: none;
    border-radius: 0 10px 10px 0;
    color: rgba(148, 163, 184, 0.8);
    padding: 0 .75rem;
    cursor: pointer;
    transition: color .2s ease, background .2s ease;
    display: flex;
    align-items: center;
}

.auth-eye-btn:hover {
    color: #c084fc;
    background: rgba(192, 132, 252, 0.08);
}

/* Section header divider trong form */
.form-section-label {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .1em;
    color: rgba(148, 163, 184, 0.55);
    text-transform: uppercase;
    margin-bottom: .85rem;
    padding-bottom: .5rem;
    border-bottom: 1px solid rgba(255,255,255,.07);
}

/* Password strength bar */
.pw-strength-bar {
    height: 3px;
    border-radius: 999px;
    background: rgba(255,255,255,.1);
    margin-top: 6px;
    overflow: hidden;
}

.pw-strength-fill {
    height: 100%;
    border-radius: 999px;
    width: 0%;
    transition: width .35s ease, background .35s ease;
}

.pw-strength-text {
    font-size: .72rem;
    font-weight: 500;
    color: rgba(148,163,184,.7);
    margin-top: 4px;
    min-height: 1em;
}

/* Submit button */
.auth-submit-btn {
    width: 100%;
    padding: .72rem 1rem;
    font-size: .95rem;
    font-weight: 700;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%);
    color: #fff;
    letter-spacing: .01em;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 22px rgba(124, 58, 237, 0.38);
    transition: transform .2s ease, box-shadow .2s ease;
}

.auth-submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(124, 58, 237, 0.52);
}

.auth-submit-btn:active {
    transform: translateY(0);
}

/* Alert */
.auth-alert {
    background: rgba(248, 113, 113, 0.12);
    border: 1px solid rgba(248, 113, 113, 0.3);
    border-radius: 10px;
    color: #fca5a5;
    font-size: .86rem;
    padding: .65rem 1rem;
    margin-bottom: 1rem;
}

/* Bottom link */
.auth-bottom-text a {
    font-weight: 700;
    color: #c084fc;
    text-decoration: none;
    transition: color .2s ease;
}

.auth-bottom-text a:hover {
    color: #e879f9;
    text-decoration: underline;
}

/* Badge */
.auth-brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(192, 132, 252, 0.1);
    border: 1px solid rgba(192, 132, 252, 0.22);
    border-radius: 999px;
    padding: 3px 12px;
    font-size: .75rem;
    font-weight: 600;
    color: #c084fc;
    letter-spacing: .03em;
    margin-bottom: .75rem;
}

/* Back to login link */
.auth-back-link {
    display: block;
    width: 100%;
    text-align: center;
    padding: .55rem 1rem;
    background: rgba(192,132,252,.07);
    border: 1px solid rgba(192,132,252,.2);
    border-radius: 10px;
    font-weight: 700;
    color: #c084fc;
    text-decoration: none;
    transition: all .2s ease;
}

.auth-back-link:hover {
    background: rgba(192,132,252,.14);
    color: #e879f9;
}
</style>
@endpush

@section('content')
<section class="lamp-auth register-lamp-auth" data-auth-lamp data-lamp-target="#registerCard" data-lamp-on="{{ $errors->any() ? 'true' : 'false' }}" aria-label="Đăng ký tài khoản">
    @include('auth.partials.interactive-lamp', ['prompt' => 'Kéo dây để mở biểu mẫu'])
    <div class="login-card-column">
        <div class="auth-glass-card p-4 p-md-5 register-lamp-card" id="registerCard" aria-hidden="false">

            {{-- Header --}}
            <div class="text-center mb-4">
                <div class="auth-brand-badge">
                    <i class="bi bi-stars"></i>
                    Chợ Figure & Thẻ Bài
                </div>
                <div class="auth-icon-wrap mx-auto">
                    <i class="bi bi-person-plus text-white fs-3"></i>
                </div>
                <h1 class="auth-title">Tạo tài khoản mới</h1>
                <p class="auth-subtitle">Trở thành thành viên để đặt hàng và theo dõi đơn hàng dễ dàng</p>
            </div>

            {{-- Flash errors --}}
            @if ($errors->any())
                <div class="auth-alert">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
                @csrf

                {{-- ── Thông tin cá nhân ── --}}
                <p class="form-section-label"><i class="bi bi-person-circle me-1"></i>Thông tin cá nhân</p>

                {{-- Họ tên --}}
                <div class="mb-3">
                    <label for="HoTen" class="auth-label">
                        Họ và tên <span class="text-danger">*</span>
                    </label>
                    <div class="input-group auth-input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person-fill" style="font-size:.9rem;"></i>
                        </span>
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
                </div>

                {{-- Email & SĐT --}}
                <div class="row g-3 mb-3">
                    <div class="col-12 col-sm-7">
                        <label for="Email" class="auth-label">
                            Email <span class="text-danger">*</span>
                        </label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text">
                                <i class="bi bi-envelope-fill" style="font-size:.9rem;"></i>
                            </span>
                            <input id="Email"
                                   type="email"
                                   name="Email"
                                   value="{{ old('Email') }}"
                                   class="form-control @error('Email') is-invalid @enderror"
                                   placeholder="email@example.com"
                                   maxlength="190"
                                   autocomplete="email"
                                   required>
                            @error('Email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12 col-sm-5">
                        <label for="SoDienThoai" class="auth-label">Số điện thoại</label>
                        <div class="input-group auth-input-group">
                            <span class="input-group-text">
                                <i class="bi bi-telephone-fill" style="font-size:.9rem;"></i>
                            </span>
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
                    </div>
                </div>

                {{-- ── Mật khẩu ── --}}
                <p class="form-section-label mt-4"><i class="bi bi-shield-lock me-1"></i>Bảo mật tài khoản</p>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6">
                        <label for="MatKhau" class="auth-label">
                            Mật khẩu <span class="text-danger">*</span>
                        </label>
                        <div class="input-group auth-input-group has-toggle">
                            <span class="input-group-text">
                                <i class="bi bi-lock-fill" style="font-size:.9rem;"></i>
                            </span>
                            <input id="MatKhau"
                                   type="password"
                                   name="MatKhau"
                                   class="form-control @error('MatKhau') is-invalid @enderror"
                                   placeholder="Tối thiểu 8 ký tự"
                                   minlength="8"
                                   maxlength="72"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" class="auth-eye-btn" id="toggleRegPw1" aria-label="Hiện/Ẩn mật khẩu">
                                <i class="bi bi-eye" id="eyeReg1"></i>
                            </button>
                            @error('MatKhau')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        {{-- Strength bar --}}
                        <div class="pw-strength-bar"><div class="pw-strength-fill" id="pwStrengthFill"></div></div>
                        <p class="pw-strength-text" id="pwStrengthText"></p>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="MatKhau_confirmation" class="auth-label">
                            Xác nhận mật khẩu <span class="text-danger">*</span>
                        </label>
                        <div class="input-group auth-input-group has-toggle">
                            <span class="input-group-text">
                                <i class="bi bi-shield-check" style="font-size:.9rem;"></i>
                            </span>
                            <input id="MatKhau_confirmation"
                                   type="password"
                                   name="MatKhau_confirmation"
                                   class="form-control"
                                   placeholder="Nhập lại mật khẩu"
                                   minlength="8"
                                   maxlength="72"
                                   autocomplete="new-password"
                                   required>
                            <button type="button" class="auth-eye-btn" id="toggleRegPw2" aria-label="Hiện/Ẩn xác nhận">
                                <i class="bi bi-eye" id="eyeReg2"></i>
                            </button>
                            <div class="invalid-feedback" id="passwordConfirmFeedback">
                                Xác nhận mật khẩu không khớp.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="auth-submit-btn btn-ripple">
                    <i class="bi bi-check2-circle me-2"></i>Đăng ký tài khoản
                </button>
            </form>

            {{-- Back to login --}}
            <div class="mt-4">
                <a href="{{ route('login') }}" class="auth-back-link">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Đã có tài khoản? Đăng nhập tại đây
                </a>
            </div>

        </div>{{-- /.auth-glass-card --}}
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/auth-lamp.js') }}"></script>
<script>
(function () {
    /* ---- Hiện/Ẩn mật khẩu ---- */
    function makeToggle(btnId, inputId, iconId) {
        const btn   = document.getElementById(btnId);
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (!btn || !input) return;
        btn.addEventListener('click', function () {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }
    makeToggle('toggleRegPw1', 'MatKhau', 'eyeReg1');
    makeToggle('toggleRegPw2', 'MatKhau_confirmation', 'eyeReg2');

    /* ---- Password strength indicator ---- */
    const pwInput  = document.getElementById('MatKhau');
    const fill     = document.getElementById('pwStrengthFill');
    const label    = document.getElementById('pwStrengthText');

    const levels = [
        { min: 0,  max: 3,  pct: '20%',  color: '#f87171', text: 'Rất yếu' },
        { min: 4,  max: 5,  pct: '45%',  color: '#fb923c', text: 'Yếu' },
        { min: 6,  max: 7,  pct: '65%',  color: '#facc15', text: 'Trung bình' },
        { min: 8,  max: 10, pct: '85%',  color: '#34d399', text: 'Mạnh' },
        { min: 11, max: 99, pct: '100%', color: '#38bdf8', text: 'Rất mạnh 🔒' },
    ];

    function scorePassword(pw) {
        let score = 0;
        if (!pw) return 0;
        if (pw.length >= 8)  score += 2;
        if (pw.length >= 12) score += 2;
        if (/[a-z]/.test(pw)) score += 1;
        if (/[A-Z]/.test(pw)) score += 2;
        if (/[0-9]/.test(pw)) score += 1;
        if (/[^a-zA-Z0-9]/.test(pw)) score += 3;
        return score;
    }

    if (pwInput && fill && label) {
        pwInput.addEventListener('input', function () {
            const score = scorePassword(this.value);
            const level = levels.find(l => score >= l.min && score <= l.max) || levels[0];
            fill.style.width     = this.value ? level.pct : '0%';
            fill.style.background = level.color;
            label.textContent    = this.value ? level.text : '';
            label.style.color    = level.color;
        });
    }

    /* ---- Confirm password validation ---- */
    const confirmInput = document.getElementById('MatKhau_confirmation');
    const confirmFb    = document.getElementById('passwordConfirmFeedback');

    function validatePasswordMatch() {
        if (!confirmInput || !pwInput) return;
        if (confirmInput.value && pwInput.value !== confirmInput.value) {
            confirmInput.setCustomValidity('Xác nhận mật khẩu không khớp.');
            confirmInput.classList.add('is-invalid');
        } else {
            confirmInput.setCustomValidity('');
            confirmInput.classList.remove('is-invalid');
        }
    }

    if (pwInput)      pwInput.addEventListener('input', validatePasswordMatch);
    if (confirmInput) confirmInput.addEventListener('input', validatePasswordMatch);

    /* ---- Scroll reveal ---- */
    const cards = document.querySelectorAll('.reveal-item');
    const io = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-revealed'); io.unobserve(e.target); } });
    }, { threshold: 0.1 });
    cards.forEach(el => io.observe(el));
})();
</script>
@endpush


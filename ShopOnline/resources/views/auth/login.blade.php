@extends('layouts.app')

@section('title', 'Đăng nhập - Chợ Figure & Thẻ Bài')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/auth-lamp.css') }}">
<style>
body > header,
body > footer { display: none !important; }
body.d-flex.flex-column main.flex-grow-1 {
    min-height: 100vh;
    padding: 0 !important;
    overflow: hidden;
}
body.d-flex.flex-column main.flex-grow-1 > .container {
    max-width: none;
    width: 100%;
    height: 100%;
    padding: 0;
}
.login-card {
    width: 100%;
    padding: 32px 30px 28px;
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 24px;
    background: linear-gradient(155deg, rgba(24,27,35,.92), rgba(17,19,24,.84));
    backdrop-filter: blur(20px);
    box-shadow: 0 24px 60px rgba(0,0,0,.65), 0 0 48px rgba(245,165,36,.06), inset 0 1px 0 rgba(255,255,255,.07);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transform: translateX(48px) scale(.98);
    transition: opacity .45s ease, transform .6s cubic-bezier(.22,1,.36,1), visibility 0s linear .45s;
}
.lamp-auth.is-on .login-card {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: none;
    transition: opacity .55s ease .16s, transform .6s cubic-bezier(.22,1,.36,1) .16s, visibility 0s;
}
.login-card-header { text-align: center; margin-bottom: 20px; }
.login-card-icon {
    width: 52px;
    height: 52px;
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    background: linear-gradient(135deg,#f5a524,#f43f78);
    box-shadow: 0 10px 24px rgba(245,165,36,.22);
}
.login-card h1 { margin: 0 0 5px; color: #f4f6fb; font-size: 1.55rem; letter-spacing: -.025em; }
.login-card-header p { margin: 0; color: #98a0b3; font-size: .88rem; }
.auth-alert {
    margin-bottom: 14px;
    padding: 10px 12px;
    border: 1px solid rgba(248,113,113,.28);
    border-radius: 10px;
    background: rgba(248,113,113,.1);
    color: #fca5a5;
    font-size: .84rem;
}
.lamp-field { margin-bottom: 14px; }
.lamp-field-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 7px;
    color: #98a0b3;
    font-size: .82rem;
    font-weight: 600;
}
.lamp-field-label a { color: #f5a524; text-decoration: none; }
.lamp-input {
    position: relative;
    display: flex;
    align-items: center;
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 13px;
    background: rgba(9,11,16,.72);
    transition: .25s ease;
}
.lamp-input:focus-within {
    border-color: #f5a524;
    box-shadow: 0 0 0 3px rgba(245,165,36,.14), 0 0 18px rgba(245,165,36,.08);
}
.lamp-input > i { position: absolute; left: 14px; color: #646c80; }
.lamp-input input {
    width: 100%;
    height: 46px;
    border: 0;
    outline: 0;
    padding: 0 44px 0 42px;
    background: transparent;
    color: #f4f6fb;
}
.lamp-input input::placeholder { color: #646c80; }
.lamp-input.is-invalid { border-color: #f87171; }
.password-toggle {
    position: absolute;
    right: 8px;
    width: 32px;
    height: 32px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #646c80;
}
.lamp-check { display: flex; align-items: center; gap: 8px; margin: 2px 0 18px; color: #98a0b3; font-size: .82rem; }
.lamp-submit {
    position: relative;
    width: 100%;
    height: 46px;
    overflow: hidden;
    border: 0;
    border-radius: 13px;
    background: linear-gradient(135deg,#f5a524,#e8891b);
    color: #090a0f;
    font-weight: 800;
    box-shadow: 0 8px 22px rgba(245,165,36,.28);
    transition: transform .22s ease, box-shadow .22s ease;
}
.lamp-submit::after {
    content: "";
    position: absolute;
    inset: 0;
    transform: translateX(-120%) skewX(-20deg);
    background: linear-gradient(90deg,transparent,rgba(255,255,255,.32),transparent);
}
.lamp-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(245,165,36,.38); }
.lamp-submit:hover::after { animation: buttonSheen .7s ease; }
@keyframes buttonSheen { to { transform: translateX(120%) skewX(-20deg); } }
.lamp-divider {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 16px 0 12px;
    color: #646c80;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
}
.lamp-divider::before,.lamp-divider::after { content:""; flex:1; height:1px; background:rgba(255,255,255,.07); }
.lamp-register {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 42px;
    border: 1px solid rgba(245,165,36,.22);
    border-radius: 11px;
    background: rgba(245,165,36,.07);
    color: #ffd479;
    font-weight: 700;
    text-decoration: none;
}
.lamp-register:hover { background: rgba(245,165,36,.12); color: #ffe29a; }
@media (max-width:860px) {
    .login-card-column { max-width:440px; margin:0 auto; }
}
@media (max-width:420px) {
    .login-card { padding:24px 18px 20px; border-radius:20px; }
}
@media (prefers-reduced-motion:reduce) {
    .lamp-beam,.login-card,.pull-tooltip,.lamp-submit::after { animation:none; transition:none; }
}
</style>
@endpush

@section('content')
<section class="lamp-auth" data-auth-lamp data-lamp-target="#loginCard" data-lamp-on="{{ $errors->any() ? 'true' : 'false' }}" aria-label="Đăng nhập tài khoản">
    @include('auth.partials.interactive-lamp', ['prompt' => 'Kéo dây để đăng nhập'])

    <div class="login-card-column">
        <section class="login-card" id="loginCard" aria-hidden="true" inert>
            <header class="login-card-header">
                <div class="login-card-icon"><i class="bi bi-box-arrow-in-right text-dark fs-5"></i></div>
                <h1>Chào mừng trở lại!</h1>
                <p>Đăng nhập để tiếp tục mua sắm</p>
            </header>

            @if ($errors->any())
                <div class="auth-alert" role="alert">
                    <i class="bi bi-exclamation-circle me-1"></i>{{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                <div class="lamp-field">
                    <label for="Email" class="lamp-field-label"><span>Email <span class="text-danger">*</span></span></label>
                    <div class="lamp-input @error('Email') is-invalid @enderror">
                        <i class="bi bi-envelope-fill"></i>
                        <input id="Email" type="email" name="Email" value="{{ old('Email') }}"
                               placeholder="email@example.com" maxlength="190" autocomplete="email" required autofocus>
                    </div>
                    @error('Email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="lamp-field">
                    <label for="MatKhau" class="lamp-field-label">
                        <span>Mật khẩu <span class="text-danger">*</span></span>
                        <a href="{{ route('password.request') }}">Quên mật khẩu?</a>
                    </label>
                    <div class="lamp-input @error('MatKhau') is-invalid @enderror">
                        <i class="bi bi-lock-fill"></i>
                        <input id="MatKhau" type="password" name="MatKhau" placeholder="Nhập mật khẩu"
                               maxlength="72" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" id="toggleLoginPw"
                                aria-label="Hiển thị mật khẩu" aria-pressed="false">
                            <i class="bi bi-eye" id="eyeIconLogin"></i>
                        </button>
                    </div>
                    @error('MatKhau')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <label class="lamp-check" for="ghi_nho">
                    <input id="ghi_nho" type="checkbox" name="ghi_nho" value="1"
                           class="form-check-input" {{ old('ghi_nho') ? 'checked' : '' }}>
                    Ghi nhớ đăng nhập
                </label>

                <button type="submit" class="lamp-submit btn-ripple">
                    Đăng nhập <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="lamp-divider"><span>CHƯA CÓ TÀI KHOẢN?</span></div>
            <a href="{{ route('register') }}" class="lamp-register">
                <i class="bi bi-person-plus me-2"></i>Tạo tài khoản mới
            </a>
        </section>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/auth-lamp.js') }}"></script>
<script>
(function () {
    const root = document.querySelector('[data-auth-lamp]');
    const canvas = document.getElementById('lampDust');
    const toggleBtn = document.getElementById('toggleLoginPw');
    const pwInput = document.getElementById('MatKhau');
    const eyeIcon = document.getElementById('eyeIconLogin');

    if (toggleBtn && pwInput) {
        toggleBtn.addEventListener('click', function () {
            const show = pwInput.type === 'password';
            pwInput.type = show ? 'text' : 'password';
            eyeIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
            this.setAttribute('aria-pressed', String(show));
            this.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiển thị mật khẩu');
        });
    }

    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    let particles = [];
    let frame = null;
    let lit = false;
    let previousFrame = 0;
    let spawnTime = 0;

    function resizeCanvas() {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(window.innerWidth * dpr);
        canvas.height = Math.round(window.innerHeight * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    function startParticles(next) {
        lit = next;
        if (lit && !frame) frame = requestAnimationFrame(drawParticles);
    }

    function drawParticles(now) {
        const dt = Math.min((now - previousFrame) / 16.67, 2);
        previousFrame = now;
        ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
        if (lit) {
            if (now - spawnTime > 180 && particles.length < 24) {
                spawnTime = now;
                particles.push({
                    x: window.innerWidth * .25 + (Math.random() - .5) * 150,
                    y: window.innerHeight * .40 + Math.random() * 180,
                    r: .8 + Math.random() * 1.8,
                    vx: .2 + Math.random() * .35,
                    vy: -.15 - Math.random() * .2,
                    life: 1
                });
            }
            particles.forEach(function (p) {
                p.x += p.vx * dt;
                p.y += p.vy * dt;
                p.life -= .004 * dt;
                ctx.globalAlpha = Math.max(0, p.life) * .7;
                ctx.fillStyle = '#ffd479';
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fill();
            });
            particles = particles.filter(function (p) { return p.life > 0; });
            frame = requestAnimationFrame(drawParticles);
        } else {
            particles = [];
            ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
            frame = null;
        }
    }

    root.addEventListener('auth-lamp-change', function (event) {
        startParticles(event.detail.on);
    });
    startParticles(root.classList.contains('is-on'));
})();
</script>
@endpush

@extends('layouts.app')

@section('title', 'Đăng nhập - Chợ Figure & Thẻ Bài')

@push('styles')
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
.lamp-auth {
    --gold: #f5a524;
    --pink: #f43f78;
    position: relative;
    z-index: 1;
    min-height: 100vh;
    display: grid;
    grid-template-columns: minmax(300px, 1fr) minmax(360px, 460px);
    align-items: center;
    gap: clamp(24px, 6vw, 90px);
    max-width: 1120px;
    margin: 0 auto;
    padding: 36px 28px;
    color: #f4f6fb;
}
.lamp-auth::before {
    content: "";
    position: fixed;
    inset: 0;
    z-index: -2;
    background:
        radial-gradient(circle at 24% 38%, rgba(245,165,36,.16), transparent 32%),
        radial-gradient(circle at 78% 50%, rgba(244,63,120,.06), transparent 30%),
        #07080c;
    transition: background .6s ease;
}
.lamp-auth.is-on::before {
    background:
        radial-gradient(circle at 24% 38%, rgba(245,165,36,.26), transparent 35%),
        radial-gradient(circle at 78% 50%, rgba(244,63,120,.09), transparent 30%),
        #07080c;
}
#lampDust {
    position: fixed;
    inset: 0;
    z-index: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}
.lamp-column {
    position: relative;
    z-index: 2;
    display: flex;
    justify-content: center;
    align-items: center;
}
.lamp-visual {
    position: relative;
    width: min(390px, 100%);
    aspect-ratio: 380/480;
    user-select: none;
}
.lamp-svg {
    position: relative;
    z-index: 2;
    width: 100%;
    height: 100%;
    overflow: visible;
}
.lamp-beam {
    position: absolute;
    z-index: 1;
    top: 39%;
    left: 48%;
    width: 310%;
    height: 230%;
    opacity: 0;
    transform-origin: 12% 0;
    transform: rotate(-44deg) scaleY(.86);
    clip-path: polygon(43% 0,57% 0,100% 100%,0 100%);
    background: linear-gradient(to bottom, rgba(255,231,171,.32), rgba(245,165,36,.13) 42%, transparent 100%);
    filter: blur(18px);
    transition: opacity .55s ease, transform .6s cubic-bezier(.22,1,.36,1);
}
.lamp-auth.is-on .lamp-beam {
    opacity: 1;
    transform: rotate(-44deg) scaleY(1);
}
.lamp-glow { opacity: 0; transition: opacity .4s ease; }
.lamp-auth.is-on .lamp-glow { opacity: 1; }
.pull-cord { cursor: grab; touch-action: none; }
.pull-cord:active { cursor: grabbing; }
.pull-tooltip {
    position: absolute;
    left: 66%;
    bottom: 18%;
    z-index: 4;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 12px;
    border: 1px solid rgba(245,165,36,.28);
    border-radius: 999px;
    background: rgba(17,19,24,.92);
    color: #ffd479;
    font-size: .74rem;
    white-space: nowrap;
    box-shadow: 0 8px 24px rgba(0,0,0,.5);
    pointer-events: none;
    animation: tooltipFloat 2.6s ease-in-out infinite;
    transition: opacity .35s ease, transform .35s ease;
}
.pull-tooltip i { font-size: 7px; color: var(--gold); filter: drop-shadow(0 0 5px var(--gold)); }
.lamp-auth.is-on .pull-tooltip { opacity: 0; transform: translateY(8px); animation: none; }
@keyframes tooltipFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(4px); } }
.brand-panel {
    position: absolute;
    left: -4%;
    top: 48%;
    z-index: 1;
    transform: translateY(-50%);
    opacity: 1;
    transition: opacity .4s ease;
}
.lamp-auth.is-on .brand-panel { opacity: 0; pointer-events: none; }
.brand-panel .brand-name {
    font-size: 1.5rem;
    font-weight: 800;
    letter-spacing: -.03em;
    background: linear-gradient(135deg,#f43f78,#7c3aed);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.brand-panel p { margin: 10px 0 0; color: #98a0b3; line-height: 1.6; }
.login-card-column {
    position: relative;
    z-index: 4;
    width: 100%;
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
    .lamp-auth { grid-template-columns:1fr; align-content:start; gap:8px; padding:16px 16px 30px; overflow-y:auto; }
    .lamp-visual { width:min(260px,68vw); }
    .brand-panel { display:none; }
    .login-card-column { max-width:440px; margin:0 auto; }
}
@media (max-width:420px) {
    .lamp-auth { padding:8px 12px 20px; }
    .lamp-visual { width:min(220px,62vw); }
    .login-card { padding:24px 18px 20px; border-radius:20px; }
}
@media (prefers-reduced-motion:reduce) {
    .lamp-beam,.login-card,.pull-tooltip,.lamp-submit::after { animation:none; transition:none; }
}
</style>
@endpush

@section('content')
<section class="lamp-auth" id="lampAuth" aria-label="Đăng nhập tài khoản">
    <canvas id="lampDust" aria-hidden="true"></canvas>

    <div class="lamp-column">
        <div class="lamp-visual" id="lampVisual">
            <div class="lamp-beam" aria-hidden="true"></div>
            <svg class="lamp-svg" viewBox="0 0 380 480" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Đèn bàn tương tác">
                <defs>
                    <linearGradient id="lampMetal" x1="0" y1="0" x2="1" y2="1">
                        <stop stop-color="#353b4d"/><stop offset=".55" stop-color="#171b24"/><stop offset="1" stop-color="#090b10"/>
                    </linearGradient>
                    <linearGradient id="lampGold" x1="0" y1="0" x2="1" y2="1">
                        <stop stop-color="#ffe29a"/><stop offset=".55" stop-color="#f5a524"/><stop offset="1" stop-color="#9e5a0c"/>
                    </linearGradient>
                    <radialGradient id="lampBulb">
                        <stop stop-color="#fff"/><stop offset=".45" stop-color="#ffe69c"/><stop offset="1" stop-color="#f5a524"/>
                    </radialGradient>
                </defs>

                <ellipse cx="128" cy="456" rx="88" ry="15" fill="#030406" opacity=".9"/>
                <g>
                    <ellipse cx="128" cy="446" rx="74" ry="16" fill="#0d0f16"/>
                    <path d="M54 446c0-14 32-20 74-20s74 6 74 20H54Z" fill="url(#lampMetal)"/>
                    <ellipse cx="128" cy="440" rx="68" ry="12" fill="url(#lampMetal)" stroke="#2f3647"/>
                    <rect x="119" y="416" width="18" height="22" rx="4" fill="url(#lampMetal)"/>
                    <ellipse cx="128" cy="434" rx="14" ry="4" fill="url(#lampGold)"/>
                </g>
                <line x1="128" y1="412" x2="76" y2="256" stroke="url(#lampMetal)" stroke-width="10" stroke-linecap="round"/>
                <line x1="76" y1="256" x2="194" y2="126" stroke="url(#lampMetal)" stroke-width="9" stroke-linecap="round"/>
                <g transform="translate(128 412)">
                    <circle r="12" fill="#12151e" stroke="#2c3345"/>
                    <circle r="9" fill="none" stroke="url(#lampGold)" stroke-width="2.4"/>
                    <circle r="3.4" fill="url(#lampGold)"/>
                </g>
                <g transform="translate(76 256)">
                    <circle r="12" fill="#12151e" stroke="#2c3345"/>
                    <circle r="9" fill="none" stroke="url(#lampGold)" stroke-width="2.4"/>
                    <circle r="3.4" fill="url(#lampGold)"/>
                </g>
                <g transform="translate(194 126)">
                    <circle r="10" fill="#12151e" stroke="#2c3345"/>
                    <circle r="8" fill="none" stroke="url(#lampGold)" stroke-width="2.2"/>
                </g>

                <path id="pullLine" d="M254 204Q254 268 254 332" stroke="#8e94a4" stroke-width="2" stroke-linecap="round"/>
                <g id="lampPull" class="pull-cord" role="switch" tabindex="0" aria-checked="false" aria-label="Kéo dây để bật đèn">
                    <circle cx="254" cy="332" r="32" fill="transparent"/>
                    <circle cx="254" cy="332" r="14" fill="#f5a524" opacity=".18"/>
                    <circle cx="254" cy="332" r="8" fill="url(#lampGold)"/>
                </g>

                <g transform="translate(202 130) rotate(-44)">
                    <rect x="-12" y="-8" width="24" height="22" rx="5" fill="url(#lampMetal)"/>
                    <rect x="-13" y="10" width="26" height="5" rx="2" fill="url(#lampGold)"/>
                    <path d="M-18 14C-24 38-50 66-68 92h136C50 66 24 38 18 14H-18Z" fill="url(#lampMetal)" stroke="#2b3141"/>
                    <ellipse cx="0" cy="92" rx="66" ry="16" fill="#090b10" stroke="#262c3b"/>
                    <ellipse class="lamp-glow" cx="0" cy="92" rx="64" ry="15" fill="#ffd479" stroke="#ffe29a" stroke-width="3"/>
                    <ellipse class="lamp-glow" cx="0" cy="83" rx="14" ry="12" fill="url(#lampBulb)"/>
                </g>
            </svg>
            <div class="brand-panel" aria-hidden="true">
                <div class="brand-name">Chợ Figure &amp; Thẻ</div>
                <p>Kéo dây đèn để<br>bắt đầu mua sắm</p>
            </div>
            <div class="pull-tooltip" aria-hidden="true">
                <i class="bi bi-circle-fill"></i> Kéo dây để đăng nhập
            </div>
        </div>
    </div>

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
<script>
(function () {
    const root = document.getElementById('lampAuth');
    const pull = document.getElementById('lampPull');
    const line = document.getElementById('pullLine');
    const card = document.getElementById('loginCard');
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

    let isOn = @if($errors->any()) true @else false @endif;
    let pulling = false;
    let dragged = false;
    let dy = 0;
    let velocity = 0;
    let startY = 0;
    let lastY = 0;
    let springFrame = null;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function setLamp(next) {
        isOn = next;
        root.classList.toggle('is-on', isOn);
        pull.setAttribute('aria-checked', String(isOn));
        pull.setAttribute('aria-label', isOn ? 'Kéo dây để tắt đèn' : 'Kéo dây để bật đèn');
        card.setAttribute('aria-hidden', String(!isOn));
        if (isOn) card.removeAttribute('inert');
        else card.setAttribute('inert', '');
        startParticles(isOn);
    }

    function renderPull(value) {
        dy = Math.max(0, Math.min(58, value));
        line.setAttribute('d', `M254 204Q254 ${268 + dy * .45} 254 ${332 + dy}`);
        pull.setAttribute('transform', `translate(0 ${dy})`);
    }

    function bounce() {
        if (reducedMotion) {
            renderPull(0);
            return;
        }
        cancelAnimationFrame(springFrame);
        let previousTime = performance.now();
        function settle(now) {
            const dt = Math.min((now - previousTime) / 1000, .032);
            previousTime = now;
            velocity += (-300 * dy - 24 * velocity) * dt;
            renderPull(Math.max(0, dy + velocity * dt));
            if (dy < .15 && Math.abs(velocity) < 1) {
                renderPull(0);
                springFrame = null;
                return;
            }
            springFrame = requestAnimationFrame(settle);
        }
        springFrame = requestAnimationFrame(settle);
    }

    function quickPull() {
        if (pulling) return;
        pulling = true;
        const from = dy;
        const start = performance.now();
        const duration = reducedMotion ? 0 : 150;
        function pullDown(now) {
            const progress = duration ? Math.min((now - start) / duration, 1) : 1;
            const eased = 1 - Math.pow(1 - progress, 3);
            renderPull(from + (44 - from) * eased);
            if (progress < 1) {
                requestAnimationFrame(pullDown);
                return;
            }
            setLamp(!isOn);
            velocity = -90;
            pulling = false;
            bounce();
        }
        requestAnimationFrame(pullDown);
    }

    pull.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            quickPull();
        }
    });

    pull.addEventListener('pointerdown', function (event) {
        if (event.button !== undefined && event.button !== 0) return;
        event.preventDefault();
        cancelAnimationFrame(springFrame);
        pulling = true;
        dragged = false;
        startY = event.clientY;
        lastY = startY;
        velocity = 0;
        pull.setPointerCapture(event.pointerId);
    });
    pull.addEventListener('pointermove', function (event) {
        if (!pulling) return;
        const scale = 480 / pull.ownerSVGElement.getBoundingClientRect().height;
        const nextY = event.clientY;
        const nextDy = (nextY - startY) * scale;
        dragged = dragged || Math.abs(nextY - startY) > 8;
        velocity = (nextY - lastY) * scale * 60;
        lastY = nextY;
        renderPull(nextDy > 48 ? 48 + (nextDy - 48) * .42 : nextDy);
    });
    pull.addEventListener('pointerup', function (event) {
        if (!pulling) return;
        if (!dragged) {
            pulling = false;
            quickPull();
            return;
        }
        if (dy >= 28) setLamp(!isOn);
        velocity = Math.min(velocity, -80);
        bounce();
        pulling = false;
    });
    pull.addEventListener('pointercancel', function () {
        if (!pulling) return;
        pulling = false;
        velocity = 0;
        bounce();
    });

    const ctx = canvas.getContext('2d');
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

    setLamp(isOn);
})();
</script>
@endpush

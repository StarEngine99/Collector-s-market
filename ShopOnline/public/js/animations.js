/**
 * =======================================================================
 * ShopOnline - Master Interactive Animation Engine
 * =======================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initHeaderImageSearch();

    // 1. Chuyển ảnh qua lại trên thẻ sản phẩm trang chính (< > & dots)
    initCardImageSlider();

    // 2. Hoạt ảnh nền động (Ambient Background Particle Canvas)
    initBackgroundParticles();

    // 3. Cuộn trang mượt mà tự xuất hiện (Scroll Reveal)
    initScrollReveal();

    // 4. Nghiêng 3D Thẻ Sản Phẩm (Holographic 3D Tilt)
    initCard3DTilt();

    // 5. Gợn sóng khi nhấp nút bấm (Ripple Effect)
    initButtonRipples();

    // 6. Nút cuộn lên đầu trang (Floating Back To Top)
    initBackToTopButton();

    // 7. Phóng to ảnh chi tiết sản phẩm (Zoom Lens)
    initProductImageZoom();
});

function initHeaderImageSearch() {
    const button = document.getElementById('headerImageSearchButton');
    const input = document.getElementById('headerImageSearchInput');
    const form = document.getElementById('headerImageSearchForm');
    if (!button || !input || !form) return;

    button.addEventListener('click', () => input.click());
    input.addEventListener('change', () => {
        if (input.files && input.files.length) form.submit();
    });
}

function initThemeToggle() {
    const button = document.getElementById('themeToggle');
    const storedTheme = localStorage.getItem('shopTheme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = storedTheme || (prefersDark ? 'dark' : 'light');

    function applyTheme(theme) {
        document.documentElement.dataset.theme = theme;
        document.documentElement.style.colorScheme = theme;
        if (button) {
            button.innerHTML = theme === 'dark'
                ? '<i class="bi bi-sun"></i>'
                : '<i class="bi bi-moon-stars"></i>';
            button.setAttribute('aria-label', theme === 'dark' ? 'Chuyển sang chế độ sáng' : 'Chuyển sang chế độ tối');
        }
    }

    applyTheme(initialTheme);
    if (button) {
        button.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('shopTheme', nextTheme);
            applyTheme(nextTheme);
        });
    }
}

/**
 * =======================================================================
 * 1. Chuyển đổi qua lại giữa các ảnh trên thẻ sản phẩm (Trang chính & Danh mục)
 * =======================================================================
 */
function initCardImageSlider() {
    const wrappers = document.querySelectorAll('.product-card-image-wrap');

    wrappers.forEach(wrap => {
        let images = [];
        try {
            images = JSON.parse(wrap.dataset.images || '[]');
        } catch (e) {
            images = [];
        }

        if (images.length <= 1) return;

        const imgEl = wrap.querySelector('.product-card-img');
        const prevBtn = wrap.querySelector('.btn-card-prev');
        const nextBtn = wrap.querySelector('.btn-card-next');
        const dots = wrap.querySelectorAll('.card-dot');

        if (!imgEl) return;

        let currentIndex = parseInt(wrap.dataset.currentIndex || '0', 10);

        function showCardImage(idx) {
            if (idx < 0) idx = images.length - 1;
            if (idx >= images.length) idx = 0;
            currentIndex = idx;
            wrap.dataset.currentIndex = currentIndex;

            // Hiệu ứng mờ nhẹ khi chuyển ảnh
            imgEl.style.opacity = '0.3';
            imgEl.style.transform = 'scale(0.96)';

            setTimeout(() => {
                imgEl.src = images[currentIndex];
                imgEl.style.opacity = '1';
                imgEl.style.transform = 'scale(1)';
            }, 120);

            // Cập nhật trạng thái dấu chấm (dots)
            dots.forEach((dot, i) => {
                dot.classList.toggle('active', i === currentIndex);
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                showCardImage(currentIndex - 1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                showCardImage(currentIndex + 1);
            });
        }

        dots.forEach((dot, idx) => {
            dot.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                showCardImage(idx);
            });
            dot.addEventListener('mouseenter', (e) => {
                e.stopPropagation();
                showCardImage(idx);
            });
        });
    });
}

/**
 * =======================================================================
 * 2. Hoạt ảnh nền động: Hạt bụi sao Stardust lấp lánh (Background Animation)
 * =======================================================================
 */
function initBackgroundParticles() {
    const canvas = document.getElementById('shopBgCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    let width = (canvas.width = window.innerWidth);
    let height = (canvas.height = window.innerHeight);

    window.addEventListener('resize', () => {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }, { passive: true });

    const mouse = { x: -1000, y: -1000 };
    window.addEventListener('mousemove', (e) => {
        mouse.x = e.clientX;
        mouse.y = e.clientY;
    }, { passive: true });

    class StarParticle {
        constructor() {
            this.reset(true);
        }

        reset(initial = false) {
            this.x = Math.random() * width;
            this.y = initial ? Math.random() * height : height + 10;
            this.radius = Math.random() * 2 + 1;
            this.speedY = Math.random() * 0.45 + 0.15;
            this.speedX = (Math.random() - 0.5) * 0.25;
            this.alpha = Math.random() * 0.55 + 0.25;
            this.alphaStep = (Math.random() * 0.01 + 0.005) * (Math.random() > 0.5 ? 1 : -1);
            this.color = Math.random() > 0.4 ? 'rgba(56, 189, 248,' : (Math.random() > 0.5 ? 'rgba(168, 85, 247,' : 'rgba(251, 191, 36,');
        }

        update() {
            this.y -= this.speedY;
            this.x += this.speedX;

            this.alpha += this.alphaStep;
            if (this.alpha <= 0.15 || this.alpha >= 0.75) {
                this.alphaStep *= -1;
            }

            // Đẩy nhẹ hạt khi chuột đến gần
            const dx = this.x - mouse.x;
            const dy = this.y - mouse.y;
            const dist = Math.hypot(dx, dy);
            if (dist < 90) {
                const force = (1 - dist / 90) * 1.5;
                this.x += (dx / dist) * force;
                this.y += (dy / dist) * force;
            }

            if (this.y < -10 || this.x < -10 || this.x > width + 10) {
                this.reset();
            }
        }

        draw() {
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fillStyle = `${this.color} ${this.alpha})`;
            ctx.shadowBlur = 4;
            ctx.shadowColor = this.color.replace('rgba', 'rgb').replace(',', ')');
            ctx.fill();
            ctx.shadowBlur = 0;
        }
    }

    const particles = [];
    const PARTICLE_COUNT = 38;
    for (let i = 0; i < PARTICLE_COUNT; i++) {
        particles.push(new StarParticle());
    }

    function renderBg() {
        if (!document.hidden) {
            ctx.clearRect(0, 0, width, height);
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();
            }
        }
        requestAnimationFrame(renderBg);
    }

    requestAnimationFrame(renderBg);
}

/**
 * =======================================================================
 * 3. Hiệu ứng Cuộn Trang Tự Động Xuất Hiện (Scroll Reveal)
 * =======================================================================
 */
function initScrollReveal() {
    const targets = document.querySelectorAll('section, .product-card, .card, footer');
    if (!targets.length || !('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                obs.unobserve(entry.target);
            }
        });
    }, {
        root: null,
        rootMargin: '0px 0px -40px 0px',
        threshold: 0.1
    });

    targets.forEach((el, index) => {
        el.classList.add('reveal-item');
        if (el.classList.contains('product-card')) {
            const staggerIndex = (index % 4) + 1;
            el.classList.add(`stagger-${staggerIndex}`);
        }
        observer.observe(el);
    });
}

/**
 * =======================================================================
 * 4. Hiệu ứng Nghiêng 3D Thẻ Sản Phẩm (Holographic 3D Tilt)
 * =======================================================================
 */
function initCard3DTilt() {
    if (window.matchMedia('(pointer: coarse)').matches) return;

    const cards = document.querySelectorAll('.product-card');

    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            // Không tilt mạnh khi chuột đang tương tác trên nút chuyển ảnh
            if (e.target.closest('.btn-card-nav, .card-dots')) return;

            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const centerX = rect.width / 2;
            const centerY = rect.height / 2;

            const rotateX = ((y - centerY) / centerY) * -6;
            const rotateY = ((x - centerX) / centerX) * 6;

            card.style.transform = `perspective(800px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-8px)`;
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'perspective(800px) rotateX(0deg) rotateY(0deg) translateY(0)';
        });
    });
}

/**
 * =======================================================================
 * 5. Hiệu ứng Gợn Sóng (Ripple Effect) cho các nút bấm
 * =======================================================================
 */
function initButtonRipples() {
    const buttons = document.querySelectorAll('.btn-primary, .btn-outline-primary, .btn-danger, .btn-success, .btn-dark');

    buttons.forEach(btn => {
        btn.classList.add('btn-ripple');
        btn.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            const circle = document.createElement('span');
            circle.classList.add('ripple-circle');
            circle.style.left = `${x}px`;
            circle.style.top = `${y}px`;

            const diameter = Math.max(rect.width, rect.height);
            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.marginLeft = circle.style.marginTop = `-${diameter / 2}px`;

            this.appendChild(circle);

            setTimeout(() => {
                circle.remove();
            }, 600);
        });
    });
}

/**
 * =======================================================================
 * 6. Nút nổi Cuộn lên đầu trang (Back to top)
 * =======================================================================
 */
function initBackToTopButton() {
    let btn = document.querySelector('.btn-back-to-top');

    if (!btn) {
        btn = document.createElement('button');
        btn.className = 'btn-back-to-top';
        btn.setAttribute('aria-label', 'Cuộn lên đầu trang');
        btn.innerHTML = '<i class="bi bi-chevron-up fs-5"></i>';
        document.body.appendChild(btn);
    }

    window.addEventListener('scroll', () => {
        if (window.scrollY > 300) {
            btn.classList.add('show');
        } else {
            btn.classList.remove('show');
        }
    }, { passive: true });

    btn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

/**
 * =======================================================================
 * 7. Phóng to ảnh sản phẩm khi hover trên trang chi tiết
 * =======================================================================
 */
function initProductImageZoom() {
    const mainImg = document.getElementById('mainProductImage');
    if (!mainImg) return;

    const parent = mainImg.closest('.product-detail-image-box');
    if (!parent) return;

    parent.addEventListener('mousemove', (e) => {
        if (e.target.closest('.btn-detail-nav, .detail-counter')) return;

        const rect = parent.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;

        mainImg.style.transformOrigin = `${x}% ${y}%`;
        mainImg.style.transform = 'scale(1.28)';
    });

    parent.addEventListener('mouseleave', () => {
        mainImg.style.transformOrigin = 'center center';
        mainImg.style.transform = 'scale(1)';
    });
}

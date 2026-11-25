(function () {
    const root = document.querySelector('[data-auth-lamp]');
    if (!root) return;

    const pull = root.querySelector('.pull-cord');
    const line = root.querySelector('.pull-line');
    const card = document.querySelector(root.dataset.lampTarget);
    if (!pull || !line || !card) return;

    let isOn = root.dataset.lampOn === 'true';
    let pulling = false;
    let dragged = false;
    let activePointerId = null;
    let dy = 0;
    let velocity = 0;
    let startY = 0;
    let lastY = 0;
    let springFrame = null;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    root.classList.add('auth-lamp-ready');

    function setLamp(next) {
        isOn = next;
        root.classList.toggle('is-on', isOn);
        pull.setAttribute('aria-checked', String(isOn));
        pull.setAttribute('aria-label', isOn ? 'Kéo dây để tắt đèn' : 'Kéo dây để bật đèn');
        card.setAttribute('aria-hidden', String(!isOn));
        if (isOn) card.removeAttribute('inert');
        else card.setAttribute('inert', '');
        root.dispatchEvent(new CustomEvent('auth-lamp-change', { detail: { on: isOn } }));
    }

    function renderPull(value) {
        dy = Math.max(0, Math.min(58, value));
        line.setAttribute('d', `M266 196Q266 ${264 + dy * .45} 266 ${332 + dy}`);
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
        if ((event.button !== undefined && event.button !== 0) || pulling) return;
        event.preventDefault();
        cancelAnimationFrame(springFrame);
        pulling = true;
        dragged = false;
        activePointerId = event.pointerId;
        startY = event.clientY;
        lastY = startY;
        velocity = 0;
        pull.setPointerCapture(event.pointerId);
    });
    pull.addEventListener('pointermove', function (event) {
        if (!pulling || event.pointerId !== activePointerId) return;
        const scale = 480 / pull.ownerSVGElement.getBoundingClientRect().height;
        const nextY = event.clientY;
        const nextDy = (nextY - startY) * scale;
        dragged = dragged || Math.abs(nextY - startY) > 8;
        velocity = (nextY - lastY) * scale * 60;
        lastY = nextY;
        renderPull(nextDy > 48 ? 48 + (nextDy - 48) * .42 : nextDy);
    });
    pull.addEventListener('pointerup', function (event) {
        if (!pulling || event.pointerId !== activePointerId) return;
        activePointerId = null;
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
    pull.addEventListener('pointercancel', function (event) {
        if (!pulling || event.pointerId !== activePointerId) return;
        activePointerId = null;
        pulling = false;
        velocity = 0;
        bounce();
    });

    setLamp(isOn);
})();

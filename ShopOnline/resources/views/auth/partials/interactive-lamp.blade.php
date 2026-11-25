<canvas id="lampDust" aria-hidden="true"></canvas>

<div class="lamp-column">
    <div class="lamp-visual">
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

            <path class="pull-line" d="M254 204Q254 268 254 332" stroke="#8e94a4" stroke-width="2" stroke-linecap="round"/>
            <g class="pull-cord" role="switch" tabindex="0" aria-checked="false" aria-label="Kéo dây để bật đèn">
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
        <div class="pull-tooltip" aria-hidden="true">
            <i class="bi bi-circle-fill"></i> {{ $prompt ?? 'Kéo dây để bật đèn' }}
        </div>
    </div>
    <div class="brand-panel" aria-hidden="true">
        <div class="brand-name">Chợ Figure &amp; Thẻ</div>
        <p>Kéo dây đèn để<br>bắt đầu mua sắm</p>
    </div>
</div>

{{--
    Honeycomb "scan grid" backdrop for dark sections. Anchored to the right
    edge and faded toward the left so text stays readable. Cell geometry:
    pointy-top hexes 56×66, rows every 50px, odd rows shifted by 28px.

    Params: $id (unique per page — SVG ids must not clash), $lit / $pulse
    ([column, row] cells to highlight, counted from the SVG's left edge),
    $sparks (true = every few seconds 4–6 random cells flash, lit left-to-right
    like a scan beam passing through them).
--}}
@php
    $id ??= 'hex';
    $lit ??= [[9, 1], [10, 2], [12, 3], [11, 4], [14, 2], [13, 6], [15, 5], [8, 5]];
    $pulse ??= [[10, 2], [13, 6]];
    $sparks ??= false;
    $origin = fn ($c, $r) => [$c * 56 - ($r % 2 ? 28 : 0), $r * 50];
@endphp
<svg class="hex-grid pointer-events-none absolute inset-y-0 right-0 h-full w-[1000px] max-w-none" aria-hidden="true" focusable="false">
    <defs>
        <path id="{{ $id }}-cell" d="M28 0L56 16L56 50L28 66L0 50L0 16Z" />
        <pattern id="{{ $id }}-pattern" width="56" height="100" patternUnits="userSpaceOnUse">
            <path d="M28 66L0 50L0 16L28 0L56 16L56 50L28 66L28 100" fill="none" stroke="#ffffff" stroke-opacity="0.09" stroke-width="1" />
        </pattern>
        <linearGradient id="{{ $id }}-fade" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stop-color="#fff" stop-opacity="0" />
            <stop offset="0.45" stop-color="#fff" stop-opacity="1" />
        </linearGradient>
        <mask id="{{ $id }}-mask">
            <rect width="100%" height="100%" fill="url(#{{ $id }}-fade)" />
        </mask>
    </defs>

    <g mask="url(#{{ $id }}-mask)">
        <rect width="100%" height="100%" fill="url(#{{ $id }}-pattern)" />

        @foreach ($lit as [$c, $r])
            @php [$x, $y] = $origin($c, $r); @endphp
            <use href="#{{ $id }}-cell" x="{{ $x }}" y="{{ $y }}"
                @class(['hex-lit', 'hex-pulse' => in_array([$c, $r], $pulse, true)])
                style="animation-delay: {{ ($c + $r) % 5 }}s" />
        @endforeach

        @if ($sparks)
            <g data-hex-sparks data-cell="{{ $id }}-cell"></g>
        @endif
    </g>
</svg>

@if ($sparks)
    <script>
        (() => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            const svg = document.currentScript.previousElementSibling;
            const layer = svg.querySelector('[data-hex-sparks]');
            const NS = 'http://www.w3.org/2000/svg';

            const burst = () => {
                if (document.hidden) return;

                // Only use cells that are actually on screen and past the left fade.
                const box = svg.getBoundingClientRect();
                const section = svg.parentElement.getBoundingClientRect();
                const minX = Math.max(430, section.left - box.left);
                const rows = Math.max(1, Math.floor(box.height / 50));
                const count = 4 + Math.floor(Math.random() * 3); // 4–6
                const cells = new Map();

                for (let tries = 0; cells.size < count && tries < 80; tries++) {
                    const r = Math.floor(Math.random() * rows);
                    const c = Math.floor(Math.random() * 19);
                    const x = c * 56 - (r % 2 ? 28 : 0);
                    if (x >= minX && x <= 944) cells.set(`${c},${r}`, [x, r * 50]);
                }

                // Light them in x order, so it reads as one beam sweeping across.
                const list = [...cells.values()].sort((a, b) => a[0] - b[0]);
                const startX = list.length ? list[0][0] : 0;

                list.forEach(([x, y]) => {
                    const cell = document.createElementNS(NS, 'use');
                    cell.setAttribute('href', '#' + layer.dataset.cell);
                    cell.setAttribute('x', x);
                    cell.setAttribute('y', y);
                    cell.setAttribute('class', 'hex-spark');
                    cell.style.animationDelay = `${Math.round((x - startX) * 1.6 + Math.random() * 120)}ms`;
                    cell.addEventListener('animationend', () => cell.remove());
                    layer.appendChild(cell);
                });
            };

            setTimeout(burst, 900);
            setInterval(burst, 3400);
        })();
    </script>
@endif

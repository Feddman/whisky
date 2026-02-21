@props([
    'animationDuration' => 4, // seconds
    'onComplete' => null,      // string: global function name, e.g. "logoDone"
    'class' => '',
])

@php
    // Generate a stable-ish unique id per render to scope CSS/DOM
    $uid = 'al_' . substr(md5(uniqid('', true)), 0, 8);
@endphp

<div
    id="{{ $uid }}"
    class="flex justify-center items-center mb-6 relative {{ $class }}"
    data-animation-duration="{{ $animationDuration }}"
    @if($onComplete) data-on-complete="{{ $onComplete }}" @endif
>
    <svg
        width="283"
        height="331"
        viewBox="0 0 283 331"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="w-48 h-56 md:w-64 md:h-72 lg:w-80 lg:h-96 drop-shadow-lg relative z-10"
        data-svg
    >
        <path
            data-path
            d="M281.056 102.408H230.056H225.056V126.408C225.056 126.408 232.556 118.408 237.056 118.408C241.556 118.408 248.056 126.408 248.056 126.408V253.408C248.056 253.408 241.056 266.408 230.056 268.408C219.056 270.408 184.056 259.408 175.056 253.408C166.056 247.408 150.056 229.408 150.056 212.408C150.056 195.408 147.056 196.408 161.056 157.408C175.056 118.408 198.056 102.408 198.056 102.408H112.056V136.408H150.056C150.056 136.408 110.81 160.749 104.056 186.408C100.377 200.381 104.056 223.408 104.056 223.408C108.056 257.408 132.056 276.408 132.056 276.408C90.3602 267.124 70.0556 246.408 63.0556 205.408C56.0556 164.408 64.0556 138.408 84.0556 109.408C104.056 80.4079 140.995 47.4646 198.056 53.4079C225.056 59.9079 234.005 56.1884 248.056 80.4079L281.056 1.40787C253.2 21.0358 252.556 21.9079 230.056 21.9079C207.556 21.9079 190.068 15.845 161.056 15.4079C132.043 14.9707 115.36 17.5172 84.0556 32.4079C32.6936 65.5788 14.283 92.2522 4.05559 157.408C4.05559 157.408 -3.94441 200.408 4.05559 231.408C12.0556 262.408 51.2039 301.882 112.056 329.408L150.056 288.408L198.056 316.408L281.056 281.408V102.408Z"
            stroke="currentColor"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
            fill="none"
            class="text-whisky-dark"
        />
        <g data-sparks></g>
    </svg>

    {{-- Scoped base animation styles --}}
    <style>
        #{{ $uid }} .spark-particle { opacity: 0; }
        #{{ $uid }} .draw-path {
            animation-name: {{ $uid }}_drawPath;
            animation-timing-function: ease-in-out;
            animation-fill-mode: forwards;
        }
        @keyframes {{ $uid }}_drawPath {
            to { stroke-dashoffset: 0; }
        }
    </style>

    <script>
        (function () {
            const root = document.getElementById(@json($uid));
            if (!root) return;

            const svg = root.querySelector('[data-svg]');
            const path = root.querySelector('[data-path]');
            const sparksLayer = root.querySelector('[data-sparks]');
            if (!svg || !path || !sparksLayer) return;

            const animationDuration = parseFloat(root.dataset.animationDuration || '4');
            const onCompleteFnName = root.dataset.onComplete || null;

            // --- Draw path setup ---
            const length = path.getTotalLength();
            path.style.strokeDasharray = String(length);
            path.style.strokeDashoffset = String(length);
            path.style.animationDuration = animationDuration + 's';
            path.classList.add('draw-path');

            // --- Sparks setup ---
            const numSparks = 30;
            const styleEl = document.createElement('style');
            document.head.appendChild(styleEl);

            function rand(min, max) { return min + Math.random() * (max - min); }

            // Create a spark group (3 circles) at a point
            function createSpark(x, y, animBaseName, delay, duration, dx, dy) {
                const ns = "http://www.w3.org/2000/svg";

                const g = document.createElementNS(ns, 'g');

                const c1 = document.createElementNS(ns, 'circle');
                c1.setAttribute('cx', x);
                c1.setAttribute('cy', y);
                c1.setAttribute('r', '2.5');
                c1.setAttribute('fill', '#FFA500');
                c1.classList.add('spark-particle');
                c1.style.animation = `${animBaseName} ${duration}s ease-out ${delay}s forwards`;

                const c2 = document.createElementNS(ns, 'circle');
                c2.setAttribute('cx', x);
                c2.setAttribute('cy', y);
                c2.setAttribute('r', '1.5');
                c2.setAttribute('fill', '#FFD700');
                c2.classList.add('spark-particle');
                c2.style.animation = `${animBaseName}-inner ${duration * 0.8}s ease-out ${delay + 0.05}s forwards`;

                const c3 = document.createElementNS(ns, 'circle');
                c3.setAttribute('cx', x);
                c3.setAttribute('cy', y);
                c3.setAttribute('r', '1');
                c3.setAttribute('fill', '#FFF');
                c3.classList.add('spark-particle');
                c3.style.animation = `${animBaseName}-core ${duration * 0.6}s ease-out ${delay + 0.1}s forwards`;

                g.appendChild(c1);
                g.appendChild(c2);
                g.appendChild(c3);

                sparksLayer.appendChild(g);

                // keyframes (scoped by unique base name)
                styleEl.textContent += `
@keyframes ${animBaseName} {
  0% { transform: translate(0,0) scale(1); opacity: 1; }
  50% { opacity: 1; }
  100% { transform: translate(${dx}px, ${dy}px) scale(0); opacity: 0; }
}
@keyframes ${animBaseName}-inner {
  0% { transform: translate(0,0) scale(1); opacity: 1; }
  50% { opacity: 1; }
  100% { transform: translate(${dx * 0.8}px, ${dy * 0.8}px) scale(0); opacity: 0; }
}
@keyframes ${animBaseName}-core {
  0% { transform: translate(0,0) scale(1); opacity: 1; }
  50% { opacity: 1; }
  100% { transform: translate(${dx * 0.6}px, ${dy * 0.6}px) scale(0); opacity: 0; }
}
`;
            }

            // Generate sparks along the path
            for (let i = 0; i < numSparks; i++) {
                const progress = Math.random(); // 0..1
                const pt = path.getPointAtLength(progress * length);

                const angle = Math.random() * Math.PI * 2;
                const delay = Math.random() * animationDuration;
                const distance = rand(25, 45);
                const dx = Math.cos(angle) * distance;
                const dy = Math.sin(angle) * distance;
                const duration = rand(0.6, 1.0);

                // Unique animation base name per spark (and per component)
                const animBaseName = `${@json($uid)}_spark_${i}`;

                createSpark(pt.x, pt.y, animBaseName, delay, duration, dx, dy);
            }

            // Completion callback
            if (onCompleteFnName && typeof window[onCompleteFnName] === 'function') {
                window.setTimeout(() => window[onCompleteFnName](), animationDuration * 1000);
            }

            // Cleanup when navigating (best effort)
            function cleanup() {
                if (styleEl && styleEl.parentNode) styleEl.parentNode.removeChild(styleEl);
                window.removeEventListener('beforeunload', cleanup);
            }
            window.addEventListener('beforeunload', cleanup);
        })();
    </script>
</div>

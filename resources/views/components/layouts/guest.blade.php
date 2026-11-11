@props(['title' => ''])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' - ' : '' }}CargoTrace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Satu momen animasi: rute pelayaran tergambar saat halaman dibuka. */
        @keyframes draw { to { stroke-dashoffset: 0; } }
        @keyframes appear { from { opacity: 0; } to { opacity: 1; } }
        .route { stroke-dasharray: 1; stroke-dashoffset: 1; animation: draw 2.2s cubic-bezier(.4, 0, .2, 1) forwards; }
        .port { opacity: 0; animation: appear .6s ease-out forwards; }

        @media (prefers-reduced-motion: reduce) {
            .route { animation: none; stroke-dashoffset: 0; }
            .port { animation: none; opacity: 1; }
            .ship { display: none; }
        }
    </style>
</head>
<body class="min-h-screen bg-foam text-slate-800 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">

    {{-- Sisi kiri: globe dengan rute pelayaran --}}
    <div class="relative hidden overflow-hidden bg-harbor-900 lg:block">
        <svg class="absolute inset-0 h-full w-full" viewBox="0 0 600 800" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
            <g fill="none" stroke="#ffffff" stroke-opacity=".12" stroke-width="1">
                <circle cx="300" cy="400" r="280"/>
                <ellipse cx="300" cy="400" rx="210" ry="280"/>
                <ellipse cx="300" cy="400" rx="140" ry="280"/>
                <ellipse cx="300" cy="400" rx="70" ry="280"/>
                <line x1="300" y1="120" x2="300" y2="680"/>
                <line x1="20" y1="400" x2="580" y2="400"/>
                <line x1="57" y1="260" x2="543" y2="260"/>
                <line x1="57" y1="540" x2="543" y2="540"/>
                <line x1="160" y1="157" x2="440" y2="157"/>
                <line x1="160" y1="643" x2="440" y2="643"/>
            </g>

            <g fill="none" stroke-width="2.5" stroke-linecap="round">
                <path class="route" pathLength="1" stroke="#f2a31b" d="M150 470 Q230 250 380 240" style="animation-delay:.3s"/>
                <path class="route" pathLength="1" stroke="#7cc6c9" d="M380 240 Q490 260 470 400" style="animation-delay:1s"/>
                <path class="route" pathLength="1" stroke="#7cc6c9" d="M150 470 Q300 640 470 400" style="animation-delay:1.5s"/>
                <path class="route" pathLength="1" stroke="#7cc6c9" stroke-opacity=".6" d="M240 330 Q360 400 330 560" style="animation-delay:2s"/>
            </g>

            <g fill="#ffffff">
                <circle class="port" cx="150" cy="470" r="5" style="animation-delay:.2s"/>
                <circle class="port" cx="380" cy="240" r="5" style="animation-delay:1s"/>
                <circle class="port" cx="470" cy="400" r="5" style="animation-delay:1.6s"/>
                <circle class="port" cx="240" cy="330" r="4" style="animation-delay:2s"/>
                <circle class="port" cx="330" cy="560" r="4" style="animation-delay:2.4s"/>
            </g>

            <circle class="ship" r="6" fill="#f2a31b">
                <animateMotion dur="9s" begin="2.6s" repeatCount="indefinite" path="M150 470 Q230 250 380 240"/>
            </circle>
        </svg>

        <div class="relative flex h-full flex-col justify-between p-12">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-tide-500">
                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="18" r="2"/><circle cx="19" cy="6" r="2"/><path d="M7 18c7 0 3-12 10-12"/></svg>
                </span>
                <span class="font-display text-2xl font-semibold text-white">CargoTrace</span>
            </div>

            <div class="max-w-md">
                <h2 class="font-display text-4xl font-semibold leading-tight text-white">Tahu persis di mana kirimanmu berada.</h2>
                <p class="mt-4 text-lg text-tide-100">Pantau status, ETA, dan keterlambatan shipment di seluruh dunia dari satu tempat.</p>
            </div>
        </div>
    </div>

    {{-- Sisi kanan: form --}}
    <div class="flex items-center justify-center p-6 sm:p-12">
        <div class="w-full max-w-sm">
            <div class="mb-10 flex items-center gap-3 lg:hidden">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-tide-500">
                    <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="18" r="2"/><circle cx="19" cy="6" r="2"/><path d="M7 18c7 0 3-12 10-12"/></svg>
                </span>
                <span class="font-display text-2xl font-semibold text-harbor-900">CargoTrace</span>
            </div>

            {{ $slot }}
        </div>
    </div>
</div>
</body>
</html>
@props(['title' => ''])

@php
    $user = auth()->user();
    $all = ['admin', 'operator', 'customer'];
    $staff = ['admin', 'operator'];

    // [label, nama route, pola route aktif, ikon, role yang boleh melihat]
    $menu = [
        ['Dashboard',  'dashboard',        'dashboard',    'dashboard',  $all],
        ['Shipments',  'shipments.index',  'shipments.*',  'shipments',  $all],
        ['Tracking',   'tracking.index',   'tracking.*',   'tracking',   $all],
        ['Exceptions', 'exceptions.index', 'exceptions.*', 'exceptions', $staff],
        ['Carriers',   'carriers.index',   'carriers.*',   'carriers',   ['admin']],
        ['Locations',  'locations.index',  'locations.*',  'locations',  ['admin']],
        ['Reports',    'reports.index',    'reports.*',    'reports',    $staff],
        ['Settings',   'settings.edit',    'settings.*',   'settings',   ['admin']],
    ];

    $icons = [
        'dashboard'  => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
        'shipments'  => '<path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/>',
        'tracking'   => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
        'exceptions' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
        'carriers'   => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
        'locations'  => '<circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>',
        'reports'    => '<path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="m19 9-5 5-4-4-3 3"/>',
        'settings'   => '<line x1="4" x2="4" y1="21" y2="14"/><line x1="4" x2="4" y1="10" y2="3"/><line x1="12" x2="12" y1="21" y2="12"/><line x1="12" x2="12" y1="8" y2="3"/><line x1="20" x2="20" y1="21" y2="16"/><line x1="20" x2="20" y1="12" y2="3"/><line x1="2" x2="6" y1="14" y2="14"/><line x1="10" x2="14" y1="8" y2="8"/><line x1="18" x2="22" y1="16" y2="16"/>',
        'logout'     => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
        'bell'       => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
        'search'     => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
    ];

    $unread = $user->unreadNotifications()->count();
    $initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 2));

    // Daftar mata uang untuk dropdown "Mata uang tampilan" (di-cache, dari tabel currencies).
    $currencyOptions = app(\App\Services\ExchangeRateService::class)->options();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' - ' : '' }}CargoTrace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-foam text-slate-800 antialiased">

    {{-- Sidebar: panel melayang di layar lebar, menjadi blok di atas pada layar kecil --}}
    <aside class="m-3 flex flex-col rounded-2xl bg-harbor-900 text-slate-300 lg:fixed lg:inset-y-3 lg:left-3 lg:m-0 lg:w-56">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-5 py-5">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-tide-500">
                <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="18" r="2"/><circle cx="19" cy="6" r="2"/><path d="M7 18c7 0 3-12 10-12"/></svg>
            </span>
            <span class="font-display text-xl font-semibold text-white">CargoTrace</span>
        </a>

        <nav class="flex flex-wrap gap-1 px-3 pb-3 lg:flex-1 lg:flex-col lg:flex-nowrap lg:overflow-y-auto">
            @foreach ($menu as [$label, $route, $pattern, $icon, $roles])
                @if (in_array($user->role, $roles, true))
                    @php($active = request()->routeIs($pattern))
                    <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tide-300 {{ $active ? 'bg-white text-harbor-900' : 'hover:bg-white/10 hover:text-white' }}">
                        <svg class="h-5 w-5 {{ $active ? 'text-tide-600' : '' }}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$icon] !!}</svg>
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-white/10 p-3">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tide-300">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons['logout'] !!}</svg>
                Log Out
            </button>
        </form>
    </aside>

    {{-- Konten --}}
    <div class="lg:pl-64">
        <header class="flex flex-wrap items-center justify-between gap-4 px-4 pt-2 lg:px-8 lg:pt-6">
            <h1 class="font-display text-2xl font-semibold text-harbor-900">{{ $title }}</h1>

            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('shipments.index') }}" class="relative">
                    <svg class="absolute left-3.5 top-2.5 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons['search'] !!}</svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search shipment..."
                           class="w-48 rounded-full border-0 bg-white py-2 pl-10 pr-4 text-sm ring-1 ring-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-tide-500 sm:w-64">
                </form>

                <div class="relative flex h-10 w-10 items-center justify-center rounded-full bg-white ring-1 ring-slate-200">
                    <svg class="h-5 w-5 text-slate-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons['bell'] !!}</svg>
                    @if ($unread > 0)
                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $unread }}</span>
                    @endif
                </div>

                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full bg-white py-1 pl-1 pr-4 ring-1 ring-slate-200">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-buoy-500 text-sm font-bold text-harbor-950">{{ $initials }}</span>
                        <span class="hidden text-sm font-medium sm:inline">{{ $user->name }}</span>
                    </summary>
                    <div class="absolute right-0 z-20 mt-2 w-56 rounded-xl bg-white p-4 text-sm shadow-lg ring-1 ring-slate-200">
                        <p class="font-semibold">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        <p class="mt-2 inline-block rounded-full bg-tide-50 px-2.5 py-0.5 text-xs font-semibold capitalize text-tide-600">{{ $user->role }}</p>

                        @if (count($currencyOptions))
                            <form method="POST" action="{{ route('preferences.currency') }}" class="mt-3 border-t border-slate-100 pt-3">
                                @csrf
                                <label for="pref_currency" class="block text-xs font-medium text-slate-500">Mata uang tampilan</label>
                                <select id="pref_currency" name="currency_code" onchange="this.form.submit()"
                                        class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-sm">
                                    @foreach ($currencyOptions as $code => $label)
                                        <option value="{{ $code }}" @selected($user->currency_code === $code)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                    </div>
                </details>
            </div>
        </header>

        <main class="px-4 pb-10 pt-6 lg:px-8">
            @if (session('success'))
                <div class="mb-5 rounded-xl bg-green-50 px-4 py-3 text-sm text-green-800 ring-1 ring-green-200">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-5 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 ring-1 ring-red-200">{{ session('error') }}</div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
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
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' - ' : '' }}CargoTrace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800">
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 flex w-60 flex-col bg-slate-900 text-slate-300">
        <div class="px-6 py-5 text-lg font-bold text-white">CargoTrace</div>

        <nav class="flex-1 space-y-1 px-3">
            @foreach ($menu as [$label, $route, $pattern, $icon, $roles])
                @if (in_array($user->role, $roles, true))
                    <a href="{{ route($route) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ request()->routeIs($pattern) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons[$icon] !!}</svg>
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="border-t border-slate-800 p-3">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons['logout'] !!}</svg>
                Log Out
            </button>
        </form>
    </aside>

    {{-- Konten --}}
    <div class="ml-60 flex-1">
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-8 py-4">
            <h1 class="text-xl font-bold">{{ $title }}</h1>

            <div class="flex items-center gap-5">
                <form method="GET" action="{{ route('shipments.index') }}" class="relative">
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons['search'] !!}</svg>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Search shipment..."
                           class="w-64 rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
                </form>

                <div class="relative">
                    <svg class="h-6 w-6 text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icons['bell'] !!}</svg>
                    @if ($unread > 0)
                        <span class="absolute -right-2 -top-2 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">{{ $unread }}</span>
                    @endif
                </div>

                <details class="relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">{{ $initials }}</span>
                        <span class="text-sm font-medium">{{ $user->name }}</span>
                    </summary>
                    <div class="absolute right-0 z-10 mt-2 w-48 rounded-lg border border-slate-200 bg-white p-3 text-sm shadow">
                        <p class="font-medium">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                        <p class="mt-1 inline-block rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold uppercase text-slate-600">{{ $user->role }}</p>
                    </div>
                </details>
            </div>
        </header>

        <main class="p-8">
            @if (session('success'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
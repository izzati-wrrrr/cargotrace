<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - CargoTrace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 p-8">
    <div class="max-w-xl bg-white rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
        <p class="mt-2 text-slate-600">
            Halo, {{ auth()->user()->name }}.
            Role kamu: <span class="font-semibold">{{ auth()->user()->role }}</span>
        </p>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="rounded-lg bg-red-600 text-white px-4 py-2 hover:bg-red-500">Keluar</button>
        </form>
    </div>
</body>
</html>
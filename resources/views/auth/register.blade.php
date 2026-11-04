<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - CargoTrace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold text-slate-800">CargoTrace</h1>
        <p class="text-slate-500 mb-6">Buat akun baru</p>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                <input id="password" type="password" name="password" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @error('password') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Ulangi password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            </div>
            <button type="submit" class="w-full rounded-lg bg-slate-900 text-white py-2 font-medium hover:bg-slate-700">
                Daftar
            </button>
        </form>

        <p class="text-sm text-slate-500 mt-6 text-center">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Masuk</a>
        </p>
    </div>
</body>
</html>
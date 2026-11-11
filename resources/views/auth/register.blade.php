<x-layouts.guest title="Daftar">
    <h1 class="font-display text-3xl font-semibold text-harbor-900">Buat akun</h1>
    <p class="mt-2 text-slate-500">Akun baru otomatis berperan sebagai customer.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                   class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 focus:border-tide-500 focus:outline-none focus:ring-2 focus:ring-tide-500/25">
            @error('name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 focus:border-tide-500 focus:outline-none focus:ring-2 focus:ring-tide-500/25">
            @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 focus:border-tide-500 focus:outline-none focus:ring-2 focus:ring-tide-500/25">
            <p class="mt-1.5 text-xs text-slate-500">Minimal 8 karakter.</p>
            @error('password') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Ulangi password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 focus:border-tide-500 focus:outline-none focus:ring-2 focus:ring-tide-500/25">
        </div>

        <button type="submit"
                class="w-full rounded-xl bg-harbor-900 py-3 font-semibold text-white transition-colors hover:bg-tide-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tide-500">
            Daftar
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:underline">Masuk</a>
    </p>
</x-layouts.guest>
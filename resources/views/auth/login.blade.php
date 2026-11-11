<x-layouts.guest title="Masuk">
    <h1 class="font-display text-3xl font-semibold text-harbor-900">Masuk</h1>
    <p class="mt-2 text-slate-500">Lanjutkan ke dashboard CargoTrace.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                   class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 focus:border-tide-500 focus:outline-none focus:ring-2 focus:ring-tide-500/25">
            @error('email') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 focus:border-tide-500 focus:outline-none focus:ring-2 focus:ring-tide-500/25">
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 accent-tide-600"> Ingat saya
        </label>

        <button type="submit"
                class="w-full rounded-xl bg-harbor-900 py-3 font-semibold text-white transition-colors hover:bg-tide-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-tide-500">
            Masuk
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:underline">Daftar</a>
    </p>

    {{-- Hanya tampil di komputer lokal. Hapus bagian @if jika ingin menampilkannya di situs demo. --}}
    @if (app()->environment('local'))
        <div class="mt-8 rounded-xl bg-white p-4 text-sm ring-1 ring-slate-200">
            <p class="font-semibold text-slate-700">Akun demo</p>
            <ul class="mt-2 space-y-1 text-slate-600">
                <li>admin@cargotrace.test</li>
                <li>operator@cargotrace.test</li>
                <li>customer@cargotrace.test</li>
            </ul>
            <p class="mt-2 text-xs text-slate-500">Password semuanya: password</p>
        </div>
    @endif
</x-layouts.guest>
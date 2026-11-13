@php($field = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2')

<x-layouts.app title="Settings">
    <div class="max-w-xl space-y-6">

        <div class="rounded-xl bg-white p-8 shadow">
            <h2 class="mb-1 font-semibold">Batas keterlambatan (delay)</h2>
            <p class="mb-5 text-sm text-slate-500">
                Selisih ETA dengan jadwal sampai batas LOW dianggap LOW, sampai batas MEDIUM dianggap MEDIUM, dan di atasnya HIGH.
                Setelah disimpan, tingkat keterlambatan semua shipment dihitung ulang.
            </p>

            <form method="POST" action="{{ route('settings.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="delay_low_hours" class="block text-sm font-medium text-slate-700">Batas LOW (jam)</label>
                    <input id="delay_low_hours" type="number" step="any" min="0" name="delay_low_hours" required
                           value="{{ old('delay_low_hours', $thresholds['low']) }}" class="{{ $field }}">
                    @error('delay_low_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="delay_medium_hours" class="block text-sm font-medium text-slate-700">Batas MEDIUM (jam)</label>
                    <input id="delay_medium_hours" type="number" step="any" min="0" name="delay_medium_hours" required
                           value="{{ old('delay_medium_hours', $thresholds['medium']) }}" class="{{ $field }}">
                    @error('delay_medium_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan</button>
            </form>
        </div>

        <div class="rounded-xl bg-white p-8 shadow">
            <h2 class="mb-1 font-semibold">Kurs mata uang</h2>
            <p class="mb-4 text-sm text-slate-500">
                Kurs diambil dari layanan eksternal dan disimpan di database. Kurs diperbarui otomatis paling sering sekali sehari.
                Kalau layanan gagal dihubungi, kurs terakhir yang tersimpan tetap dipakai.
            </p>

            <dl class="mb-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500">Terakhir diperbarui</dt>
                    <dd class="font-medium">{{ $rateStatus['updated_at']?->format('d M Y H:i') ?? 'Belum pernah' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Jumlah mata uang</dt>
                    <dd class="font-medium">{{ $rateStatus['count'] }}</dd>
                </div>
            </dl>

            @if ($rateStatus['stale'])
                <p class="mb-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800 ring-1 ring-amber-200">
                    Kurs sudah lebih dari 24 jam atau belum pernah diambil.
                </p>
            @endif

            <form method="POST" action="{{ route('settings.rates') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700">Perbarui kurs sekarang</button>
            </form>

            <p class="mt-3 text-xs text-slate-500">
                <a href="https://www.exchangerate-api.com" class="text-blue-600 hover:underline">Rates By Exchange Rate API</a>
            </p>
        </div>

        <div class="rounded-xl bg-white p-8 shadow">
            <h2 class="mb-1 font-semibold">Mata uang dasar</h2>
            <p class="mb-4 text-sm text-slate-500">Staf (admin dan operator) melihat biaya pengiriman juga dalam mata uang ini.</p>

            <form method="POST" action="{{ route('settings.currency') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label for="base_currency" class="block text-sm font-medium text-slate-700">Mata uang</label>
                    <select id="base_currency" name="base_currency" required class="{{ $field }}">
                        @forelse ($currencies as $code => $label)
                            <option value="{{ $code }}" @selected(old('base_currency', $baseCurrency) === $code)>{{ $label }}</option>
                        @empty
                            <option value="">Perbarui kurs terlebih dahulu</option>
                        @endforelse
                    </select>
                    @error('base_currency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan</button>
            </form>
        </div>
    </div>
</x-layouts.app>
<x-layouts.app title="Settings">
    <div class="max-w-xl rounded-xl bg-white p-8 shadow">
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
                       value="{{ old('delay_low_hours', $thresholds['low']) }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @error('delay_low_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="delay_medium_hours" class="block text-sm font-medium text-slate-700">Batas MEDIUM (jam)</label>
                <input id="delay_medium_hours" type="number" step="any" min="0" name="delay_medium_hours" required
                       value="{{ old('delay_medium_hours', $thresholds['medium']) }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
                @error('delay_medium_hours') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan</button>
        </form>
    </div>
</x-layouts.app>
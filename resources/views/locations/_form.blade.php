@php($location = $location ?? null)

<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama lokasi</label>
        <input id="name" type="text" name="name" value="{{ old('name', $location?->name) }}" required
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="city" class="block text-sm font-medium text-slate-700">Kota</label>
            <input id="city" type="text" name="city" value="{{ old('city', $location?->city) }}"
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('city') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="country_code" class="block text-sm font-medium text-slate-700">Kode negara (2 huruf)</label>
            <input id="country_code" type="text" name="country_code" maxlength="2" value="{{ old('country_code', $location?->country_code) }}" required
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 uppercase">
            @error('country_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <label for="type" class="block text-sm font-medium text-slate-700">Tipe</label>
        <select id="type" name="type" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            @foreach (['PORT', 'CITY', 'WAREHOUSE'] as $t)
                <option value="{{ $t }}" @selected(old('type', $location?->type) === $t)>{{ $t }}</option>
            @endforeach
        </select>
        @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <p class="block text-sm font-medium text-slate-700">Pilih titik di peta</p>
        <p class="mb-2 text-xs text-slate-500">Klik peta untuk menaruh penanda. Koordinat di bawah terisi otomatis.</p>
        <div id="location-picker" class="relative z-0 h-72 w-full rounded-lg border border-slate-300"></div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="latitude" class="block text-sm font-medium text-slate-700">Latitude</label>
            <input id="latitude" type="number" step="any" name="latitude" value="{{ old('latitude', $location?->latitude) }}" required
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('latitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="longitude" class="block text-sm font-medium text-slate-700">Longitude</label>
            <input id="longitude" type="number" step="any" name="longitude" value="{{ old('longitude', $location?->longitude) }}" required
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
            @error('longitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
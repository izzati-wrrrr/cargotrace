@php($carrier = $carrier ?? null)

<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nama carrier</label>
        <input id="name" type="text" name="name" value="{{ old('name', $carrier?->name) }}" required
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="code" class="block text-sm font-medium text-slate-700">Kode (huruf/angka, unik)</label>
        <input id="code" type="text" name="code" value="{{ old('code', $carrier?->code) }}" required maxlength="20"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 uppercase">
        @error('code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="contact_email" class="block text-sm font-medium text-slate-700">Email kontak (opsional)</label>
        <input id="contact_email" type="email" name="contact_email" value="{{ old('contact_email', $carrier?->contact_email) }}"
               class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        @error('contact_email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-slate-700">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $carrier?->is_active ?? true))>
        Carrier aktif
    </label>
</div>
@php
    $shipment = $shipment ?? null;
    $rows = old('items', $shipment
        ? $shipment->items->map(fn ($i) => ['description' => $i->description, 'quantity' => $i->quantity, 'weight_kg' => $i->weight_kg])->all()
        : [['description' => '', 'quantity' => 1, 'weight_kg' => '']]);
    $itemErrors = collect($errors->getMessages())->filter(fn ($m, $k) => str_starts_with($k, 'items'))->flatten();
    $field = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2';
    $selectedCurrency = old('currency_code', $shipment?->currency_code ?? auth()->user()->currency_code);
@endphp

<div class="space-y-5">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="customer_id" class="block text-sm font-medium text-slate-700">Customer</label>
            <select id="customer_id" name="customer_id" required class="{{ $field }}">
                <option value="">Pilih customer</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" @selected((string) old('customer_id', $shipment?->customer_id) === (string) $c->id)>{{ $c->name }} ({{ $c->email }})</option>
                @endforeach
            </select>
            @error('customer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="carrier_id" class="block text-sm font-medium text-slate-700">Carrier</label>
            <select id="carrier_id" name="carrier_id" required class="{{ $field }}">
                <option value="">Pilih carrier</option>
                @foreach ($carriers as $c)
                    <option value="{{ $c->id }}" @selected((string) old('carrier_id', $shipment?->carrier_id) === (string) $c->id)>{{ $c->name }} ({{ $c->code }})</option>
                @endforeach
            </select>
            @error('carrier_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="origin_location_id" class="block text-sm font-medium text-slate-700">Lokasi asal</label>
            <select id="origin_location_id" name="origin_location_id" required class="{{ $field }}">
                <option value="">Pilih lokasi</option>
                @foreach ($locations as $l)
                    <option value="{{ $l->id }}" @selected((string) old('origin_location_id', $shipment?->origin_location_id) === (string) $l->id)>{{ $l->name }}, {{ $l->country_code }}</option>
                @endforeach
            </select>
            @error('origin_location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="destination_location_id" class="block text-sm font-medium text-slate-700">Lokasi tujuan</label>
            <select id="destination_location_id" name="destination_location_id" required class="{{ $field }}">
                <option value="">Pilih lokasi</option>
                @foreach ($locations as $l)
                    <option value="{{ $l->id }}" @selected((string) old('destination_location_id', $shipment?->destination_location_id) === (string) $l->id)>{{ $l->name }}, {{ $l->country_code }}</option>
                @endforeach
            </select>
            @error('destination_location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label for="cargo_type" class="block text-sm font-medium text-slate-700">Jenis muatan</label>
            <input id="cargo_type" type="text" name="cargo_type" value="{{ old('cargo_type', $shipment?->cargo_type) }}" required class="{{ $field }}">
            @error('cargo_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="weight_kg" class="block text-sm font-medium text-slate-700">Berat total (kg)</label>
            <input id="weight_kg" type="number" step="any" min="0" name="weight_kg" value="{{ old('weight_kg', $shipment?->weight_kg) }}" required class="{{ $field }}">
            @error('weight_kg') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="volume_cbm" class="block text-sm font-medium text-slate-700">Volume (CBM, opsional)</label>
            <input id="volume_cbm" type="number" step="any" min="0" name="volume_cbm" value="{{ old('volume_cbm', $shipment?->volume_cbm) }}" class="{{ $field }}">
            @error('volume_cbm') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="expected_departure_at" class="block text-sm font-medium text-slate-700">Perkiraan berangkat</label>
            <input id="expected_departure_at" type="datetime-local" name="expected_departure_at"
                   value="{{ old('expected_departure_at', $shipment?->expected_departure_at?->format('Y-m-d\TH:i')) }}" required class="{{ $field }}">
            @error('expected_departure_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="expected_arrival_at" class="block text-sm font-medium text-slate-700">Perkiraan tiba</label>
            <input id="expected_arrival_at" type="datetime-local" name="expected_arrival_at"
                   value="{{ old('expected_arrival_at', $shipment?->expected_arrival_at?->format('Y-m-d\TH:i')) }}" required class="{{ $field }}">
            @error('expected_arrival_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="freight_amount" class="block text-sm font-medium text-slate-700">Biaya pengiriman</label>
            <input id="freight_amount" type="number" step="any" min="0" name="freight_amount"
                   value="{{ old('freight_amount', $shipment?->freight_amount) }}" required class="{{ $field }}">
            @error('freight_amount') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="currency_code" class="block text-sm font-medium text-slate-700">Mata uang pembayaran</label>
            <select id="currency_code" name="currency_code" required class="{{ $field }}">
                @forelse ($currencies as $code => $label)
                    <option value="{{ $code }}" @selected($selectedCurrency === $code)>{{ $label }}</option>
                @empty
                    <option value="">Kurs belum tersedia</option>
                @endforelse
            </select>
            @if (! count($currencies))
                <p class="mt-1 text-sm text-amber-700">Admin perlu menekan "Perbarui kurs sekarang" di menu Settings terlebih dahulu.</p>
            @endif
            @error('currency_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div>
        <div class="mb-2 flex items-center justify-between">
            <p class="text-sm font-medium text-slate-700">Barang dalam shipment</p>
            <button type="button" id="add-item" class="text-sm text-blue-600 hover:underline">+ Tambah barang</button>
        </div>
        <p class="mb-2 text-xs text-slate-500">Deskripsi, jumlah, dan berat (opsional) per barang.</p>

        <div id="items-list" class="space-y-2">
            @foreach ($rows as $i => $row)
                <div class="item-row grid grid-cols-12 gap-2">
                    <input type="text" name="items[{{ $i }}][description]" value="{{ $row['description'] ?? '' }}" placeholder="Deskripsi barang" required
                           class="col-span-6 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input type="number" min="1" name="items[{{ $i }}][quantity]" value="{{ $row['quantity'] ?? 1 }}" required
                           class="col-span-2 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <input type="number" step="any" min="0" name="items[{{ $i }}][weight_kg]" value="{{ $row['weight_kg'] ?? '' }}" placeholder="kg"
                           class="col-span-3 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button type="button" class="remove-item col-span-1 text-red-600 hover:text-red-800" title="Hapus baris">&times;</button>
                </div>
            @endforeach
        </div>

        @foreach ($itemErrors as $message)
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @endforeach
    </div>
</div>

<template id="item-template">
    <div class="item-row grid grid-cols-12 gap-2">
        <input type="text" name="items[__INDEX__][description]" placeholder="Deskripsi barang" required
               class="col-span-6 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <input type="number" min="1" name="items[__INDEX__][quantity]" value="1" required
               class="col-span-2 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <input type="number" step="any" min="0" name="items[__INDEX__][weight_kg]" placeholder="kg"
               class="col-span-3 rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <button type="button" class="remove-item col-span-1 text-red-600 hover:text-red-800" title="Hapus baris">&times;</button>
    </div>
</template>

<script>
    (() => {
        const list = document.getElementById('items-list');
        const template = document.getElementById('item-template');
        let next = 1000; // indeks baru dimulai jauh dari indeks yang dirender server

        document.getElementById('add-item').addEventListener('click', () => {
            list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', next++));
        });

        list.addEventListener('click', (event) => {
            if (!event.target.classList.contains('remove-item')) return;
            if (list.querySelectorAll('.item-row').length > 1) {
                event.target.closest('.item-row').remove(); // minimal satu barang tetap ada
            }
        });
    })();
</script>
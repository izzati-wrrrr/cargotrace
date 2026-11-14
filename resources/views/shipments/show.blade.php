@php
    $next = $shipment->current_status->allowedNext();
    $isDelivered = $shipment->current_status === \App\Enums\ShipmentStatus::Delivered;
    $canTrack = in_array($shipment->current_status, [\App\Enums\ShipmentStatus::PickedUp, \App\Enums\ShipmentStatus::InTransit], true);
    $lastPosition = $shipment->trackingEvents->filter(fn ($e) => $e->latitude !== null && $e->longitude !== null)->last();
    $field = 'mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm';
    $etaValue = ($shipment->estimated_arrival_at ?? $shipment->expected_arrival_at)->format('Y-m-d\TH:i');

    // Angka berformat Indonesia (titik ribuan, koma desimal), sama dengan format mata uang di kartu biaya.
    $num = fn ($value) => \Illuminate\Support\Number::format((float) $value, precision: 2, locale: 'id');
@endphp

<x-layouts.app :title="$shipment->shipment_number">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-6 shadow">
            <div class="flex items-center gap-3">
                <x-status-badge :status="$shipment->current_status" class="text-sm" />
                <x-status-badge :status="$shipment->delay_severity" class="text-sm" />
                <span class="text-sm text-slate-500">Dibuat {{ $shipment->created_at->format('d M Y H:i') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('shipments.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Kembali</a>
                @can('update', $shipment)
                    <a href="{{ route('shipments.edit', $shipment) }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Edit</a>
                @endcan
                @can('delete', $shipment)
                    <form method="POST" action="{{ route('shipments.destroy', $shipment) }}"
                          onsubmit="return confirm('Hapus shipment {{ $shipment->shipment_number }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500">Hapus</button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold">Detail</h2>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $shipment->customer->name }}</dd></div>
                        <div><dt class="text-slate-500">Carrier</dt><dd class="font-medium">{{ $shipment->carrier->name }}</dd></div>
                        <div><dt class="text-slate-500">Asal</dt><dd class="font-medium">{{ $shipment->origin->name }}, {{ $shipment->origin->country_code }}</dd></div>
                        <div><dt class="text-slate-500">Tujuan</dt><dd class="font-medium">{{ $shipment->destination->name }}, {{ $shipment->destination->country_code }}</dd></div>
                        <div><dt class="text-slate-500">Jenis muatan</dt><dd class="font-medium">{{ $shipment->cargo_type }}</dd></div>
                        <div><dt class="text-slate-500">Berat / Volume</dt><dd class="font-medium">{{ $num($shipment->weight_kg) }} kg / {{ $shipment->volume_cbm ? $num($shipment->volume_cbm).' CBM' : '-' }}</dd></div>
                        <div><dt class="text-slate-500">Perkiraan berangkat</dt><dd class="font-medium">{{ $shipment->expected_departure_at->format('d M Y H:i') }}</dd></div>
                        <div><dt class="text-slate-500">Perkiraan tiba</dt><dd class="font-medium">{{ $shipment->expected_arrival_at->format('d M Y H:i') }}</dd></div>
                        <div>
                            <dt class="text-slate-500">{{ $isDelivered ? 'Tiba aktual' : 'ETA terbaru' }}</dt>
                            <dd class="font-medium">{{ $shipment->estimated_arrival_at?->format('d M Y H:i') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Lokasi terakhir</dt>
                            <dd class="font-medium">
                                @if ($lastPosition)
                                    {{ $lastPosition->location?->name ?? number_format($lastPosition->latitude, 4).', '.number_format($lastPosition->longitude, 4) }}
                                    <span class="block text-xs font-normal text-slate-500">{{ $lastPosition->occurred_at->format('d M Y H:i') }}</span>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                @if ($cost)
                    <x-shipment-cost :cost="$cost" />
                @endif

                <div class="rounded-xl bg-white p-6 shadow">
                    <div class="mb-3 flex items-center gap-3">
                        <h2 class="font-semibold">ETA &amp; keterlambatan</h2>
                        <x-status-badge :status="$delay['severity']" />
                    </div>
                    <p class="text-sm text-slate-600">{{ $delay['reason'] }}</p>
                    <dl class="mt-4 grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <dt class="text-slate-500">Selisih</dt>
                            <dd class="font-medium">{{ $delay['delay_seconds'] > 0 ? \App\Services\DelayService::humanize($delay['delay_seconds']) : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Batas LOW</dt>
                            <dd class="font-medium">&le; {{ $delay['thresholds']['low'] }} jam</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500">Batas MEDIUM</dt>
                            <dd class="font-medium">&le; {{ $delay['thresholds']['medium'] }} jam</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="mb-3 font-semibold">Peta rute</h2>
                    {{-- relative z-0: supaya lapisan peta tidak menimpa dropdown di header --}}
                    <div id="shipment-map" data-map="{{ json_encode($mapData) }}" class="relative z-0 h-80 w-full rounded-lg"></div>
                    <p class="mt-2 text-xs text-slate-500">
                        Hijau = asal, merah = tujuan, biru = titik tracking. Garis putus-putus adalah garis lurus perkiraan menuju tujuan, bukan rute sebenarnya.
                    </p>
                </div>

                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold">Barang</h2>
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-2 font-medium">Deskripsi</th>
                                <th class="px-4 py-2 font-medium">Jumlah</th>
                                <th class="px-4 py-2 font-medium">Berat (kg)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($shipment->items as $item)
                                <tr>
                                    <td class="px-4 py-2">{{ $item->description }}</td>
                                    <td class="px-4 py-2">{{ $item->quantity }}</td>
                                    <td class="px-4 py-2">{{ $item->weight_kg ? $num($item->weight_kg) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-6">
                @can('changeStatus', $shipment)
                    @if ($next)
                        <div class="rounded-xl bg-white p-6 shadow">
                            <h2 class="mb-4 font-semibold">Perbarui status</h2>
                            <form method="POST" action="{{ route('shipments.status', $shipment) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="status" class="block text-sm font-medium text-slate-700">Status berikutnya</label>
                                    <select id="status" name="status" required class="{{ $field }}">
                                        @foreach ($next as $n)
                                            <option value="{{ $n->value }}">{{ str_replace('_', ' ', $n->value) }}</option>
                                        @endforeach
                                    </select>
                                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="location_id" class="block text-sm font-medium text-slate-700">Lokasi (opsional)</label>
                                    <select id="location_id" name="location_id" class="{{ $field }}">
                                        <option value="">Otomatis / tidak ada</option>
                                        @foreach ($locations as $l)
                                            <option value="{{ $l->id }}">{{ $l->name }}, {{ $l->country_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                                    <textarea id="description" name="description" rows="2" maxlength="500" class="{{ $field }}"></textarea>
                                </div>
                                <button type="submit" class="w-full rounded-lg bg-slate-900 py-2 text-sm font-medium text-white hover:bg-slate-700">Perbarui</button>
                            </form>
                        </div>
                    @endif
                @endcan

                @can('updateEta', $shipment)
                    <div class="rounded-xl bg-white p-6 shadow">
                        <h2 class="mb-4 font-semibold">Perbarui ETA</h2>
                        <form method="POST" action="{{ route('shipments.eta', $shipment) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="estimated_arrival_at" class="block text-sm font-medium text-slate-700">ETA baru</label>
                                <input id="estimated_arrival_at" type="datetime-local" name="estimated_arrival_at" required
                                       value="{{ old('estimated_arrival_at', $etaValue) }}" class="{{ $field }}">
                                @error('estimated_arrival_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="reason" class="block text-sm font-medium text-slate-700">Alasan perubahan</label>
                                <input id="reason" type="text" name="reason" maxlength="255" required value="{{ old('reason') }}"
                                       placeholder="Contoh: antrean di pelabuhan" class="{{ $field }}">
                                @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="w-full rounded-lg bg-slate-900 py-2 text-sm font-medium text-white hover:bg-slate-700">Perbarui ETA</button>
                        </form>
                    </div>
                @endcan

                @can('addEvent', $shipment)
                    @if ($canTrack)
                        <div class="rounded-xl bg-white p-6 shadow">
                            <h2 class="mb-4 font-semibold">Tambah event tracking</h2>
                            <form method="POST" action="{{ route('shipments.events.store', $shipment) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label for="event_type" class="block text-sm font-medium text-slate-700">Jenis event</label>
                                    <select id="event_type" name="event_type" required class="{{ $field }}">
                                        @foreach ($eventTypes as $t)
                                            <option value="{{ $t->value }}" @selected(old('event_type') === $t->value)>{{ str_replace('_', ' ', $t->value) }}</option>
                                        @endforeach
                                    </select>
                                    @error('event_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="occurred_at" class="block text-sm font-medium text-slate-700">Waktu kejadian</label>
                                    <input id="occurred_at" type="datetime-local" name="occurred_at" required
                                           value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}" class="{{ $field }}">
                                    @error('occurred_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="event_location_id" class="block text-sm font-medium text-slate-700">Lokasi</label>
                                    <select id="event_location_id" name="location_id" class="{{ $field }}">
                                        <option value="">Tidak memilih lokasi</option>
                                        @foreach ($locations as $l)
                                            <option value="{{ $l->id }}" @selected((string) old('location_id') === (string) $l->id)>{{ $l->name }}, {{ $l->country_code }}</option>
                                        @endforeach
                                    </select>
                                    @error('location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <p class="text-xs text-slate-500">Atau isi koordinat manual (diutamakan jika diisi):</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <input type="number" step="any" name="latitude" value="{{ old('latitude') }}" placeholder="Latitude" aria-label="Latitude" class="{{ $field }} mt-0">
                                        @error('latitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <input type="number" step="any" name="longitude" value="{{ old('longitude') }}" placeholder="Longitude" aria-label="Longitude" class="{{ $field }} mt-0">
                                        @error('longitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                <div>
                                    <label for="event_description" class="block text-sm font-medium text-slate-700">Deskripsi (opsional)</label>
                                    <textarea id="event_description" name="description" rows="2" maxlength="500" class="{{ $field }}">{{ old('description') }}</textarea>
                                </div>
                                <button type="submit" class="w-full rounded-lg bg-slate-900 py-2 text-sm font-medium text-white hover:bg-slate-700">Tambah event</button>
                            </form>
                        </div>
                    @endif
                @endcan

                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold">Exceptions</h2>

                    @forelse ($exceptions as $ex)
                        <div class="mb-3 rounded-lg border border-slate-200 p-3 text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold">{{ str_replace('_', ' ', $ex->type->value) }}</span>
                                <x-status-badge :status="$ex->severity" />
                                <x-status-badge :status="$ex->status" />
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                Terdeteksi {{ $ex->detected_at->format('d M Y H:i') }}
                                @if ($ex->resolved_at) &middot; selesai {{ $ex->resolved_at->format('d M Y H:i') }} @endif
                            </p>
                            @if ($ex->description)
                                <p class="mt-1 text-slate-600">{{ $ex->description }}</p>
                            @endif

                            @can('manageExceptions', $shipment)
                                @if ($ex->status->allowedNext())
                                    <form method="POST" action="{{ route('exceptions.status', $ex) }}" class="mt-3 space-y-2 border-t border-slate-100 pt-3">
                                        @csrf
                                        <input type="hidden" name="exception_id" value="{{ $ex->id }}">
                                        <select name="new_status" aria-label="Status exception berikutnya" class="{{ $field }} mt-0">
                                            @foreach ($ex->status->allowedNext() as $n)
                                                <option value="{{ $n->value }}">{{ $n->value }}</option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="note" maxlength="500" placeholder="Catatan (wajib saat RESOLVED)" aria-label="Catatan exception" class="{{ $field }} mt-0">
                                        @if ((string) old('exception_id') === (string) $ex->id)
                                            @error('new_status') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                            @error('note') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                                        @endif
                                        <button type="submit" class="w-full rounded-lg border border-slate-300 py-1.5 text-sm hover:bg-slate-50">Perbarui exception</button>
                                    </form>
                                @endif
                            @endcan
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Tidak ada exception.</p>
                    @endforelse

                    @can('manageExceptions', $shipment)
                        @if ($next)
                            <form method="POST" action="{{ route('shipments.exceptions.store', $shipment) }}" class="mt-4 space-y-3 border-t border-slate-200 pt-4">
                                @csrf
                                <p class="text-sm font-medium text-slate-700">Laporkan exception baru</p>
                                <div>
                                    <select name="type" required aria-label="Jenis exception" class="{{ $field }} mt-0">
                                        @foreach ($exceptionTypes as $t)
                                            <option value="{{ $t->value }}" @selected(old('type') === $t->value)>{{ str_replace('_', ' ', $t->value) }}</option>
                                        @endforeach
                                    </select>
                                    @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <select name="severity" required aria-label="Tingkat keparahan" class="{{ $field }} mt-0">
                                        @foreach (['LOW', 'MEDIUM', 'HIGH'] as $s)
                                            <option value="{{ $s }}" @selected(old('severity') === $s)>Severity {{ $s }}</option>
                                        @endforeach
                                    </select>
                                    @error('severity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <input type="datetime-local" name="detected_at" required aria-label="Waktu deteksi" value="{{ old('detected_at', now()->format('Y-m-d\TH:i')) }}" class="{{ $field }} mt-0">
                                    @error('detected_at') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <textarea name="details" rows="2" maxlength="500" placeholder="Keterangan (opsional)" aria-label="Keterangan exception" class="{{ $field }} mt-0">{{ old('details') }}</textarea>
                                    @error('details') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="w-full rounded-lg bg-red-600 py-2 text-sm font-medium text-white hover:bg-red-500">Laporkan exception</button>
                            </form>
                        @endif
                    @endcan
                </div>

                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold">Timeline</h2>
                    <ol class="space-y-4 border-l border-slate-200 pl-5">
                        @foreach ($shipment->trackingEvents as $event)
                            <li class="relative">
                                <span class="absolute -left-[26px] top-1 h-3 w-3 rounded-full {{ $event->is_exception ? 'bg-red-500' : ($loop->last ? 'bg-blue-600' : 'bg-slate-300') }}"></span>
                                <p class="text-sm font-semibold">
                                    {{ str_replace('_', ' ', $event->event_type) }}
                                    @if ($event->is_exception)
                                        <span class="ml-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700">EXCEPTION</span>
                                    @endif
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $event->occurred_at->format('d M Y H:i') }}
                                    @if ($event->location) &middot; {{ $event->location->name }}, {{ $event->location->country_code }} @endif
                                </p>
                                @if ($event->description)
                                    <p class="mt-1 text-sm text-slate-600">{{ $event->description }}</p>
                                @endif
                                @unless (auth()->user()->isCustomer())
                                    <p class="text-xs text-slate-400">oleh {{ $event->creator->name }}</p>
                                @endunless
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
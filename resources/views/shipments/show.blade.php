@php($next = $shipment->current_status->allowedNext())

<x-layouts.app :title="$shipment->shipment_number">
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-6 shadow">
            <div class="flex items-center gap-3">
                <x-status-badge :status="$shipment->current_status" class="text-sm" />
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
                        <div><dt class="text-slate-500">Berat / Volume</dt><dd class="font-medium">{{ number_format($shipment->weight_kg, 2) }} kg / {{ $shipment->volume_cbm ? number_format($shipment->volume_cbm, 2).' CBM' : '-' }}</dd></div>
                        <div><dt class="text-slate-500">Perkiraan berangkat</dt><dd class="font-medium">{{ $shipment->expected_departure_at->format('d M Y H:i') }}</dd></div>
                        <div><dt class="text-slate-500">Perkiraan tiba</dt><dd class="font-medium">{{ $shipment->expected_arrival_at->format('d M Y H:i') }}</dd></div>
                        <div><dt class="text-slate-500">ETA terbaru</dt><dd class="font-medium">{{ $shipment->estimated_arrival_at?->format('d M Y H:i') ?? '-' }}</dd></div>
                    </dl>
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
                                    <td class="px-4 py-2">{{ $item->weight_kg ? number_format($item->weight_kg, 2) : '-' }}</td>
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
                                    <select id="status" name="status" required class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                        @foreach ($next as $n)
                                            <option value="{{ $n->value }}">{{ str_replace('_', ' ', $n->value) }}</option>
                                        @endforeach
                                    </select>
                                    @error('status') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="location_id" class="block text-sm font-medium text-slate-700">Lokasi (opsional)</label>
                                    <select id="location_id" name="location_id" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                        <option value="">Otomatis / tidak ada</option>
                                        @foreach ($locations as $l)
                                            <option value="{{ $l->id }}">{{ $l->name }}, {{ $l->country_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="description" class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                                    <textarea id="description" name="description" rows="2" maxlength="500"
                                              class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                                </div>
                                <button type="submit" class="w-full rounded-lg bg-slate-900 py-2 text-sm font-medium text-white hover:bg-slate-700">Perbarui</button>
                            </form>
                        </div>
                    @endif
                @endcan

                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="mb-4 font-semibold">Timeline</h2>
                    <ol class="space-y-4 border-l border-slate-200 pl-5">
                        @foreach ($shipment->trackingEvents as $event)
                            <li class="relative">
                                <span class="absolute -left-[26px] top-1 h-3 w-3 rounded-full {{ $loop->last ? 'bg-blue-600' : 'bg-slate-300' }}"></span>
                                <p class="text-sm font-semibold">{{ str_replace('_', ' ', $event->event_type) }}</p>
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
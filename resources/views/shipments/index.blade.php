<x-layouts.app title="Shipments">
    <div class="rounded-xl bg-white p-6 shadow">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari no. shipment / customer..."
                       class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ str_replace('_', ' ', $s->value) }}</option>
                    @endforeach
                </select>
                <select name="carrier_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua carrier</option>
                    @foreach ($carriers as $carrier)
                        <option value="{{ $carrier->id }}" @selected((string) request('carrier_id') === (string) $carrier->id)>{{ $carrier->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Filter</button>
            </form>

            @can('create', App\Models\Shipment::class)
                <a href="{{ route('shipments.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    + Buat Shipment
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">No. Shipment</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Carrier</th>
                        <th class="px-4 py-3 font-medium">Asal</th>
                        <th class="px-4 py-3 font-medium">Tujuan</th>
                        <th class="px-4 py-3 font-medium">ETA</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Delay</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($shipments as $shipment)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('shipments.show', $shipment) }}" class="text-blue-600 hover:underline">{{ $shipment->shipment_number }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $shipment->customer->name }}</td>
                            <td class="px-4 py-3">{{ $shipment->carrier->name }}</td>
                            <td class="px-4 py-3">{{ $shipment->origin->name }}, {{ $shipment->origin->country_code }}</td>
                            <td class="px-4 py-3">{{ $shipment->destination->name }}, {{ $shipment->destination->country_code }}</td>
                            <td class="px-4 py-3">{{ $shipment->estimated_arrival_at?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$shipment->current_status" /></td>
                            <td class="px-4 py-3"><x-status-badge :status="$shipment->delay_severity" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada shipment yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $shipments->links() }}</div>
    </div>
</x-layouts.app><x-layouts.app title="Shipments">
    <div class="rounded-xl bg-white p-6 shadow">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari no. shipment / customer..."
                       class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ str_replace('_', ' ', $s->value) }}</option>
                    @endforeach
                </select>
                <select name="carrier_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua carrier</option>
                    @foreach ($carriers as $carrier)
                        <option value="{{ $carrier->id }}" @selected((string) request('carrier_id') === (string) $carrier->id)>{{ $carrier->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Filter</button>
            </form>

            @can('create', App\Models\Shipment::class)
                <a href="{{ route('shipments.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    + Buat Shipment
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">No. Shipment</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Carrier</th>
                        <th class="px-4 py-3 font-medium">Asal</th>
                        <th class="px-4 py-3 font-medium">Tujuan</th>
                        <th class="px-4 py-3 font-medium">ETA</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Delay</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($shipments as $shipment)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('shipments.show', $shipment) }}" class="text-blue-600 hover:underline">{{ $shipment->shipment_number }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $shipment->customer->name }}</td>
                            <td class="px-4 py-3">{{ $shipment->carrier->name }}</td>
                            <td class="px-4 py-3">{{ $shipment->origin->name }}, {{ $shipment->origin->country_code }}</td>
                            <td class="px-4 py-3">{{ $shipment->destination->name }}, {{ $shipment->destination->country_code }}</td>
                            <td class="px-4 py-3">{{ $shipment->estimated_arrival_at?->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$shipment->current_status" /></td>
                            <td class="px-4 py-3"><x-status-badge :status="$shipment->delay_severity" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada shipment yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $shipments->links() }}</div>
    </div>
</x-layouts.app>
<x-layouts.app title="Tracking">
    <div class="rounded-xl bg-white p-6 shadow">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-500">Shipment yang masih berjalan beserta posisi terakhirnya.</p>
            <form method="GET" class="flex items-center gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari no. shipment / customer..."
                       class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">No. Shipment</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Rute</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Posisi terakhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($shipments as $shipment)
                        @php($position = $shipment->latestPositionEvent)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-medium">
                                <a href="{{ route('shipments.show', $shipment) }}" class="text-blue-600 hover:underline">{{ $shipment->shipment_number }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $shipment->customer->name }}</td>
                            <td class="px-4 py-3">{{ $shipment->origin->name }} &rarr; {{ $shipment->destination->name }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$shipment->current_status" /></td>
                            <td class="px-4 py-3">
                                @if ($position)
                                    {{ $position->location?->name ?? number_format($position->latitude, 4).', '.number_format($position->longitude, 4) }}
                                    <span class="block text-xs text-slate-500">{{ $position->occurred_at->format('d M Y H:i') }}</span>
                                @else
                                    <span class="text-slate-400">Belum ada</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada shipment aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $shipments->links() }}</div>
    </div>
</x-layouts.app>
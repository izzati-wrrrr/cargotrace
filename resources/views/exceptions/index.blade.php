<x-layouts.app title="Exceptions">
    <div class="rounded-xl bg-white p-6 shadow">
        <form method="GET" class="mb-4 flex flex-wrap items-center gap-2">
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua status</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->value }}</option>
                @endforeach
            </select>
            <select name="severity" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua severity</option>
                @foreach ($severities as $s)
                    <option value="{{ $s->value }}" @selected(request('severity') === $s->value)>{{ $s->value }}</option>
                @endforeach
            </select>
            <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua jenis</option>
                @foreach ($types as $t)
                    <option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ str_replace('_', ' ', $t->value) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Shipment</th>
                        <th class="px-4 py-3 font-medium">Jenis</th>
                        <th class="px-4 py-3 font-medium">Severity</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Terdeteksi</th>
                        <th class="px-4 py-3 font-medium">Selesai</th>
                        <th class="px-4 py-3 font-medium">Dilaporkan oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($exceptions as $ex)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('shipments.show', $ex->shipment_id) }}" class="font-medium text-blue-600 hover:underline">{{ $ex->shipment->shipment_number }}</a>
                                <span class="block text-xs text-slate-500">{{ $ex->shipment->customer->name }}</span>
                            </td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', $ex->type->value) }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$ex->severity" /></td>
                            <td class="px-4 py-3"><x-status-badge :status="$ex->status" /></td>
                            <td class="px-4 py-3">{{ $ex->detected_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3">{{ $ex->resolved_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $ex->reporter->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada exception yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $exceptions->links() }}</div>
    </div>
</x-layouts.app>
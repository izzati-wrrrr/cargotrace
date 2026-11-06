<x-layouts.app title="Locations">
    {{-- relative z-0: supaya lapisan peta tidak menimpa dropdown di header --}}
    <div class="mb-6 rounded-xl bg-white p-4 shadow">
        <div id="locations-overview" data-points="{{ json_encode($mapPoints) }}"
             class="relative z-0 h-80 w-full rounded-lg"></div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, kota, negara..."
                       class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="type" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua tipe</option>
                    @foreach (['PORT', 'CITY', 'WAREHOUSE'] as $t)
                        <option value="{{ $t }}" @selected(request('type') === $t)>{{ $t }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Filter</button>
            </form>

            <a href="{{ route('locations.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                + Tambah Lokasi
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Kota</th>
                        <th class="px-4 py-3 font-medium">Negara</th>
                        <th class="px-4 py-3 font-medium">Tipe</th>
                        <th class="px-4 py-3 font-medium">Koordinat</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($locations as $location)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $location->name }}</td>
                            <td class="px-4 py-3">{{ $location->city ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $location->country_code }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">{{ $location->type }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ number_format($location->latitude, 4) }}, {{ number_format($location->longitude, 4) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('locations.edit', $location) }}" class="text-blue-600 hover:underline">Edit</a>
                                    @if (auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('locations.destroy', $location) }}"
                                              onsubmit="return confirm('Hapus lokasi {{ $location->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada lokasi yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $locations->links() }}</div>
    </div>
</x-layouts.app>
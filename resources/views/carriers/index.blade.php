<x-layouts.app title="Carriers">
    <div class="rounded-xl bg-white p-6 shadow">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode..."
                       class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <select name="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua status</option>
                    <option value="active" @selected(request('status') === 'active')>Aktif</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                </select>
                <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Filter</button>
            </form>

            <a href="{{ route('carriers.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                + Tambah Carrier
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th>
                        <th class="px-4 py-3 font-medium">Kode</th>
                        <th class="px-4 py-3 font-medium">Kontak</th>
                        <th class="px-4 py-3 font-medium">Shipments</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($carriers as $carrier)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $carrier->name }}</td>
                            <td class="px-4 py-3">{{ $carrier->code }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $carrier->contact_email ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $carrier->shipments_count }}</td>
                            <td class="px-4 py-3">
                                @if ($carrier->is_active)
                                    <span class="rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">AKTIF</span>
                                @else
                                    <span class="rounded bg-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-600">NONAKTIF</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('carriers.edit', $carrier) }}" class="text-blue-600 hover:underline">Edit</a>
                                    @if (auth()->user()->isAdmin())
                                        <form method="POST" action="{{ route('carriers.destroy', $carrier) }}"
                                              onsubmit="return confirm('Hapus carrier {{ $carrier->name }}?')">
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
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada carrier yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $carriers->links() }}</div>
    </div>
</x-layouts.app>
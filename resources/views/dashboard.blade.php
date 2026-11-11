@php
    $first = \Illuminate\Support\Str::before(auth()->user()->name, ' ');
    $panel = 'rounded-2xl bg-white shadow-sm ring-1 ring-slate-200/70';

    // [label, nilai, warna titik, warna angka saat ada masalah]
    $cards = [
        ['Total Shipments', $kpi['total'], '#0E2F40', null],
        ['In Transit', $kpi['in_transit'], '#0E7C86', null],
        ['Delivered', $kpi['delivered'], '#1E9E6A', null],
        ['Delayed', $kpi['delayed'], '#EA7A2F', 'text-orange-600'],
        ['Open Exceptions', $kpi['open_exceptions'], '#D64545', 'text-red-600'],
    ];
@endphp

<x-layouts.app title="Dashboard">
    <div class="space-y-5">

        {{-- Ringkasan satu kalimat --}}
        <div>
            <h2 class="font-display text-3xl font-semibold text-harbor-900">Halo, {{ $first }}.</h2>
            <p class="mt-1 text-slate-500">
                @if ($kpi['delayed'] + $kpi['open_exceptions'] === 0)
                    Semua shipment berjalan sesuai jadwal.
                @else
                    {{ $kpi['delayed'] }} shipment terlambat, {{ $kpi['open_exceptions'] }} exception menunggu penanganan.
                @endif
                @if (auth()->user()->isCustomer())
                    Data di bawah hanya untuk shipment milikmu.
                @endif
            </p>
        </div>

        {{-- Strip KPI: satu panel, bukan lima kartu terpisah --}}
        <section class="grid grid-cols-2 overflow-hidden {{ $panel }} sm:grid-cols-3 lg:grid-cols-5">
            @foreach ($cards as [$label, $value, $color, $alertClass])
                <div class="border-b border-r border-slate-100 p-5 lg:border-b-0 last:border-r-0">
                    <p class="flex items-center gap-2 text-sm text-slate-500">
                        <span class="h-2 w-2 rounded-full" style="background: {{ $color }}"></span>{{ $label }}
                    </p>
                    <p class="mt-2 font-display text-4xl font-semibold tabular-nums {{ $alertClass && $value > 0 ? $alertClass : 'text-harbor-900' }}">{{ $value }}</p>
                </div>
            @endforeach
        </section>

        {{-- Peta sebagai sorotan utama, donut dan KPI operasional di sampingnya --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <section class="relative overflow-hidden {{ $panel }} xl:col-span-2">
                {{-- relative z-0: supaya lapisan peta tidak menimpa dropdown di header --}}
                <div id="dashboard-map" data-shipments="{{ json_encode($mapData) }}" class="relative z-0 h-[26rem] w-full"></div>

                <div class="pointer-events-none absolute right-4 top-4 z-10 rounded-xl bg-white/95 px-4 py-2.5 shadow ring-1 ring-slate-200/70">
                    <p class="font-display font-semibold text-harbor-900">Shipment Map</p>
                    <p class="text-xs text-slate-500">{{ count($mapData) }} shipment di perjalanan</p>
                </div>

                <div class="pointer-events-none absolute bottom-4 left-4 z-10 rounded-xl bg-white/95 px-3.5 py-2.5 text-xs text-slate-600 shadow ring-1 ring-slate-200/70">
                    <p class="mb-1.5 font-semibold text-slate-700">Posisi terakhir</p>
                    <ul class="space-y-1">
                        <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background:#16a34a"></span>Tepat waktu</li>
                        <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background:#eab308"></span>Terlambat LOW</li>
                        <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background:#f97316"></span>Terlambat MEDIUM</li>
                        <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background:#dc2626"></span>Terlambat HIGH</li>
                    </ul>
                    <p class="mt-1.5 text-slate-500">Garis putus-putus: rute perkiraan.</p>
                </div>
            </section>

            <div class="flex flex-col gap-5">
                <section class="{{ $panel }} p-6">
                    <h2 class="font-display text-lg font-semibold text-harbor-900">Delay Rate by Carrier</h2>
                    <p class="mb-3 text-sm text-slate-500">Porsi shipment terlambat per carrier.</p>
                    @if (count($carrierChart['values']))
                        <div class="relative h-56">
                            <canvas id="carrier-chart" data-chart="{{ json_encode($carrierChart) }}"></canvas>
                            <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center pb-9">
                                <span class="font-display text-3xl font-semibold text-harbor-900">{{ $overallDelayRate }}%</span>
                                <span class="text-xs text-slate-500">Rata-rata delay</span>
                            </div>
                        </div>
                    @else
                        <p class="flex h-56 items-center justify-center text-center text-sm text-slate-500">Belum ada shipment yang terlambat.</p>
                    @endif
                </section>

                <section class="{{ $panel }} p-6">
                    <h2 class="font-display text-lg font-semibold text-harbor-900">Kinerja operasional</h2>
                    <dl class="mt-2 divide-y divide-slate-100">
                        <div class="flex items-center justify-between gap-4 py-3">
                            <div>
                                <dt class="text-sm font-medium">On-time delivery rate</dt>
                                <dd class="text-xs text-slate-500">Dari {{ $ops['delivered_total'] }} shipment yang sudah tiba</dd>
                            </div>
                            <dd class="font-display text-2xl font-semibold text-harbor-900">{{ $ops['on_time_rate'] !== null ? $ops['on_time_rate'] . '%' : '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <div>
                                <dt class="text-sm font-medium">Rata-rata keterlambatan</dt>
                                <dd class="text-xs text-slate-500">Hanya shipment tiba yang terlambat</dd>
                            </div>
                            <dd class="text-right font-display text-xl font-semibold text-harbor-900">{{ $ops['avg_delay'] ?? '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-3">
                            <div>
                                <dt class="text-sm font-medium">Waktu penyelesaian exception</dt>
                                <dd class="text-xs text-slate-500">Dari terdeteksi sampai RESOLVED</dd>
                            </div>
                            <dd class="text-right font-display text-xl font-semibold text-harbor-900">{{ $ops['resolution'] ?? '-' }}</dd>
                        </div>
                    </dl>
                </section>
            </div>
        </div>

        {{-- Grafik status dan shipment terbaru --}}
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
            <section class="{{ $panel }} p-6">
                <h2 class="font-display text-lg font-semibold text-harbor-900">Shipments by Status</h2>
                <p class="mb-3 text-sm text-slate-500">Jumlah shipment di tiap status.</p>
                <div class="relative h-64">
                    <canvas id="status-chart" data-chart="{{ json_encode($statusChart) }}"></canvas>
                </div>
            </section>

            <section class="{{ $panel }} p-6 xl:col-span-2">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="font-display text-lg font-semibold text-harbor-900">Recent Shipments</h2>
                        <p class="text-sm text-slate-500">8 shipment terbaru.</p>
                    </div>
                    <form method="GET" class="flex items-center gap-2">
                        <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-tide-500">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $s)
                                <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ str_replace('_', ' ', $s->value) }}</option>
                            @endforeach
                        </select>
                        <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Filter</button>
                        <a href="{{ route('shipments.index') }}" class="text-sm font-medium text-blue-600 hover:underline">Lihat semua</a>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-slate-500">
                            <tr class="border-b border-slate-200">
                                <th class="py-2 pr-4 font-medium">No. Shipment</th>
                                <th class="py-2 pr-4 font-medium">Customer</th>
                                <th class="py-2 pr-4 font-medium">Carrier</th>
                                <th class="py-2 pr-4 font-medium">Asal</th>
                                <th class="py-2 pr-4 font-medium">Tujuan</th>
                                <th class="py-2 pr-4 font-medium">ETA</th>
                                <th class="py-2 pr-4 font-medium">Status</th>
                                <th class="py-2 font-medium">Delay</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recent as $shipment)
                                <tr class="hover:bg-slate-50">
                                    <td class="py-3 pr-4 font-medium">
                                        <a href="{{ route('shipments.show', $shipment) }}" class="text-blue-600 hover:underline">{{ $shipment->shipment_number }}</a>
                                    </td>
                                    <td class="py-3 pr-4">{{ $shipment->customer->name }}</td>
                                    <td class="py-3 pr-4">{{ $shipment->carrier->name }}</td>
                                    <td class="py-3 pr-4">{{ $shipment->origin->name }}, {{ $shipment->origin->country_code }}</td>
                                    <td class="py-3 pr-4">{{ $shipment->destination->name }}, {{ $shipment->destination->country_code }}</td>
                                    <td class="py-3 pr-4">{{ $shipment->estimated_arrival_at?->format('Y-m-d') ?? '-' }}</td>
                                    <td class="py-3 pr-4"><x-status-badge :status="$shipment->current_status" /></td>
                                    <td class="py-3"><x-status-badge :status="$shipment->delay_severity" /></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-500">Belum ada shipment yang cocok. Buat shipment pertama dari menu Shipments.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-layouts.app>
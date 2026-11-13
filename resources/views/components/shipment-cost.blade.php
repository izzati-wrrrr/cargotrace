@props(['cost'])

<div class="rounded-xl bg-white p-6 shadow">
    <h2 class="mb-3 font-semibold">Biaya pengiriman</h2>
    <p class="font-display text-3xl font-semibold text-harbor-900">{{ $cost['original'] }}</p>
    <p class="text-sm text-slate-500">Dibayar dalam {{ $cost['from_name'] }} ({{ $cost['from'] }})</p>

    @if ($cost['conversions'])
        <ul class="mt-4 divide-y divide-slate-100 text-sm">
            @foreach ($cost['conversions'] as $row)
                <li class="flex items-center justify-between gap-4 py-2.5">
                    <span class="text-slate-600">Setara dalam {{ $row['name'] }} ({{ $row['code'] }})</span>
                    @if ($row['text'])
                        <span class="font-semibold">&asymp; {{ $row['text'] }}</span>
                    @else
                        <span class="text-slate-400">kurs belum tersedia</span>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    <p class="mt-3 text-xs text-slate-500">
        @if ($cost['status']['updated_at'])
            Kurs diperbarui {{ $cost['status']['updated_at']->format('d M Y H:i') }}.
            @if ($cost['status']['stale'])
                Kurs mungkin sudah tidak terbaru.
            @endif
        @else
            Kurs belum tersedia. Admin dapat memperbarui kurs di menu Settings.
        @endif
        <a href="https://www.exchangerate-api.com" class="text-blue-600 hover:underline">Rates By Exchange Rate API</a>
    </p>
</div>
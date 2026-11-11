@props(['status'])
@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;

    // [warna pil, warna titik]
    [$pill, $dot] = match ($value) {
        'CREATED' => ['bg-slate-100 text-slate-600', 'bg-slate-400'],
        'BOOKED' => ['bg-indigo-50 text-indigo-700', 'bg-indigo-500'],
        'PICKED_UP', 'INVESTIGATING' => ['bg-amber-50 text-amber-800', 'bg-amber-500'],
        'IN_TRANSIT' => ['bg-blue-50 text-blue-700', 'bg-blue-500'],
        'DELIVERED', 'NONE', 'RESOLVED' => ['bg-green-50 text-green-700', 'bg-green-500'],
        'LOW' => ['bg-yellow-50 text-yellow-800', 'bg-yellow-500'],
        'MEDIUM' => ['bg-orange-50 text-orange-700', 'bg-orange-500'],
        'CANCELLED', 'HIGH', 'OPEN' => ['bg-red-50 text-red-700', 'bg-red-500'],
        default => ['bg-slate-100 text-slate-600', 'bg-slate-400'],
    };
    $label = $value === 'NONE' ? 'ON TIME' : str_replace('_', ' ', $value);
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold {$pill}"]) }}>
    <span class="h-1.5 w-1.5 rounded-full {{ $dot }}"></span>{{ $label }}
</span>
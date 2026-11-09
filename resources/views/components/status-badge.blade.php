@props(['status'])
@php
    $classes = match ($status->value) {
        'CREATED' => 'bg-slate-200 text-slate-700',
        'BOOKED' => 'bg-indigo-100 text-indigo-700',
        'PICKED_UP' => 'bg-amber-100 text-amber-700',
        'IN_TRANSIT' => 'bg-blue-100 text-blue-700',
        'DELIVERED' => 'bg-green-100 text-green-700',
        'CANCELLED' => 'bg-red-100 text-red-700',
    };
@endphp
<span {{ $attributes->merge(['class' => "rounded px-2 py-0.5 text-xs font-semibold {$classes}"]) }}>{{ str_replace('_', ' ', $status->value) }}</span>
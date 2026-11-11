@props(['status'])
@php
    $value = $status instanceof \BackedEnum ? $status->value : (string) $status;
    $classes = match ($value) {
        'CREATED' => 'bg-slate-200 text-slate-700',
        'BOOKED' => 'bg-indigo-100 text-indigo-700',
        'PICKED_UP' => 'bg-amber-100 text-amber-700',
        'IN_TRANSIT' => 'bg-blue-100 text-blue-700',
        'DELIVERED' => 'bg-green-100 text-green-700',
        'CANCELLED' => 'bg-red-100 text-red-700',
        'NONE' => 'bg-green-100 text-green-700',
        'LOW' => 'bg-yellow-100 text-yellow-800',
        'MEDIUM' => 'bg-orange-100 text-orange-700',
        'HIGH' => 'bg-red-100 text-red-700',
        'OPEN' => 'bg-red-100 text-red-700',
        'INVESTIGATING' => 'bg-amber-100 text-amber-700',
        'RESOLVED' => 'bg-green-100 text-green-700',
        default => 'bg-slate-200 text-slate-700',
    };
    $label = $value === 'NONE' ? 'ON TIME' : str_replace('_', ' ', $value);
@endphp
<span {{ $attributes->merge(['class' => "rounded px-2 py-0.5 text-xs font-semibold {$classes}"]) }}>{{ $label }}</span>
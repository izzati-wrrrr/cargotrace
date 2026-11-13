<?php

namespace App\Models;

use App\Enums\DelaySeverity;
use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use SoftDeletes;

    // current_status dan delay_severity sengaja TIDAK ada di sini.
    // Keduanya hanya boleh diubah lewat service (validasi transisi status).
    protected $fillable = [
        'shipment_number',
        'customer_id',
        'carrier_id',
        'origin_location_id',
        'destination_location_id',
        'created_by',
        'cargo_type',
        'weight_kg',
        'volume_cbm',
        'freight_amount',
        'currency_code',
        'expected_departure_at',
        'expected_arrival_at',
        'estimated_arrival_at',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'volume_cbm' => 'decimal:2',
            'freight_amount' => 'decimal:2',
            'expected_departure_at' => 'datetime',
            'expected_arrival_at' => 'datetime',
            'estimated_arrival_at' => 'datetime',
            'current_status' => ShipmentStatus::class,
            'delay_severity' => DelaySeverity::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('occurred_at');
    }

    /**
     * Event terbaru yang punya koordinat = posisi terakhir shipment.
     */
    public function latestPositionEvent(): HasOne
    {
        return $this->hasOne(TrackingEvent::class)->ofMany(
            ['occurred_at' => 'max', 'id' => 'max'],
            fn ($query) => $query->whereNotNull('latitude')->whereNotNull('longitude'),
        );
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class);
    }
}

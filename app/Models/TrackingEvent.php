<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrackingEvent extends Model
{
    protected $fillable = [
        'shipment_id',
        'location_id',
        'created_by',
        'event_type',
        'occurred_at',
        'latitude',
        'longitude',
        'description',
        'is_exception',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'is_exception' => 'boolean',
        ];
    }

    // Riwayat tracking bersifat append-only: event lama tidak boleh diubah atau dihapus.
    protected static function booted(): void
    {
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
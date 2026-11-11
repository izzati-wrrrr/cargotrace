<?php

namespace App\Models;

use App\Enums\DelaySeverity;
use App\Enums\ExceptionStatus;
use App\Enums\ExceptionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentException extends Model
{
    protected $fillable = [
        'shipment_id',
        'reported_by',
        'resolved_by',
        'type',
        'severity',
        'status',
        'description',
        'detected_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExceptionType::class,
            'severity' => DelaySeverity::class,
            'status' => ExceptionStatus::class,
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function shipment(): BelongsTo
    {
        // withTrashed: exception tetap terbaca walaupun shipment-nya sudah di-soft-delete.
        return $this->belongsTo(Shipment::class)->withTrashed();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
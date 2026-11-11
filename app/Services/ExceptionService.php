<?php

namespace App\Services;

use App\Enums\ExceptionStatus;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExceptionService
{
    public function __construct(private ShipmentService $shipments)
    {
    }

    /** $data: type (ExceptionType), severity (DelaySeverity), detected_at, description. */
    public function open(Shipment $shipment, array $data, User $user): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $data, $user) {
            $shipment = Shipment::whereKey($shipment->id)->lockForUpdate()->firstOrFail();

            // Shipment yang sudah DELIVERED atau CANCELLED tidak punya status berikutnya.
            if ($shipment->current_status->allowedNext() === []) {
                throw ValidationException::withMessages([
                    'type' => 'Exception tidak bisa ditambahkan ke shipment yang sudah selesai atau dibatalkan.',
                ]);
            }

            $detectedAt = Carbon::parse($data['detected_at']);
            $last = TrackingEvent::where('shipment_id', $shipment->id)->max('occurred_at');

            // Urutan waktu timeline harus tetap kronologis.
            if ($last && $detectedAt->lt(Carbon::parse($last))) {
                throw ValidationException::withMessages([
                    'detected_at' => 'Waktu deteksi tidak boleh lebih awal dari event terakhir ('
                        . Carbon::parse($last)->format('d M Y H:i') . ').',
                ]);
            }

            $exception = $shipment->exceptions()->create([
                'reported_by' => $user->id,
                'type' => $data['type'],
                'severity' => $data['severity'],
                'status' => ExceptionStatus::Open,
                'description' => $data['description'] ?? null,
                'detected_at' => $detectedAt,
            ]);

            // Event bertanda exception supaya terlihat beda di timeline (slide 7).
            $this->shipments->recordEvent($shipment, $data['type']->value, $user, [
                'occurred_at' => $detectedAt,
                'is_exception' => true,
                'description' => trim(sprintf(
                    'Exception dilaporkan (severity %s). %s',
                    $data['severity']->value,
                    $data['description'] ?? '',
                )),
            ]);

            return $exception;
        });
    }

    public function updateStatus(ShipmentException $exception, ExceptionStatus $next, ?string $note, User $user): ShipmentException
    {
        return DB::transaction(function () use ($exception, $next, $note, $user) {
            $exception = ShipmentException::whereKey($exception->id)->lockForUpdate()->firstOrFail();

            if (! $exception->status->canTransitionTo($next)) {
                throw ValidationException::withMessages([
                    'new_status' => "Perubahan status exception {$exception->status->value} ke {$next->value} tidak diperbolehkan.",
                ]);
            }

            $exception->status = $next;

            if ($next === ExceptionStatus::Resolved) {
                $exception->resolved_at = now();
                $exception->resolved_by = $user->id;
            }

            $exception->save();

            $this->shipments->recordEvent($exception->shipment, 'EXCEPTION_' . $next->value, $user, [
                'description' => trim(sprintf(
                    'Exception %s: %s. %s',
                    str_replace('_', ' ', $exception->type->value),
                    $next->value,
                    $note ?? '',
                )),
            ]);

            return $exception;
        });
    }
}
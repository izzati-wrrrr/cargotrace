<?php

namespace App\Services;

use App\Enums\ShipmentStatus;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ShipmentService
{
    public function create(array $data, array $items, User $creator): Shipment
    {
        return DB::transaction(function () use ($data, $items, $creator) {
            $shipment = new Shipment($data);
            $shipment->created_by = $creator->id;
            $shipment->current_status = ShipmentStatus::Created;
            $shipment->estimated_arrival_at = $shipment->expected_arrival_at;
            // Nomor sementara yang pasti unik. Nomor asli dibuat dari ID di bawah,
            // jadi dua operator yang menyimpan bersamaan tidak akan bentrok.
            $shipment->shipment_number = (string) Str::uuid();
            $shipment->save();

            $shipment->update([
                'shipment_number' => sprintf('SHP-%s-%06d', now()->format('Y'), $shipment->id),
            ]);

            $shipment->items()->createMany($items);

            $this->recordEvent($shipment, ShipmentStatus::Created->value, $creator, [
                'location_id' => $shipment->origin_location_id,
                'description' => 'Shipment dibuat.',
            ]);

            return $shipment;
        });
    }

    public function update(Shipment $shipment, array $data, array $items): Shipment
    {
        return DB::transaction(function () use ($shipment, $data, $items) {
            // Belum ada keterlambatan yang tercatat, jadi ETA mengikuti jadwal.
            $shipment->update($data + ['estimated_arrival_at' => $data['expected_arrival_at']]);

            $shipment->items()->delete();
            $shipment->items()->createMany($items);

            return $shipment;
        });
    }

    public function changeStatus(
        Shipment $shipment,
        ShipmentStatus $next,
        User $user,
        ?int $locationId = null,
        ?string $description = null,
    ): Shipment {
        return DB::transaction(function () use ($shipment, $next, $user, $locationId, $description) {
            // Kunci baris: dua operator tidak bisa mengubah status shipment yang sama bersamaan.
            $shipment = Shipment::whereKey($shipment->id)->lockForUpdate()->firstOrFail();
            $current = $shipment->current_status;

            if (! $current->canTransitionTo($next)) {
                throw ValidationException::withMessages([
                    'status' => "Perubahan status {$current->value} ke {$next->value} tidak diperbolehkan.",
                ]);
            }

            $shipment->current_status = $next;
            $shipment->save();

            // Kalau lokasi tidak diisi, pakai lokasi yang masuk akal untuk status itu.
            $locationId ??= match ($next) {
                ShipmentStatus::PickedUp => $shipment->origin_location_id,
                ShipmentStatus::Delivered => $shipment->destination_location_id,
                default => null,
            };

            $this->recordEvent($shipment, $next->value, $user, [
                'location_id' => $locationId,
                'description' => $description,
            ]);

            return $shipment;
        });
    }

    public function recordEvent(Shipment $shipment, string $type, User $user, array $attrs = []): TrackingEvent
    {
        $location = isset($attrs['location_id']) ? Location::find($attrs['location_id']) : null;

        return $shipment->trackingEvents()->create([
            'location_id' => $location?->id,
            'created_by' => $user->id,
            'event_type' => $type,
            'occurred_at' => $attrs['occurred_at'] ?? now(),
            // Koordinat disalin dari lokasi supaya konsisten (slide 9).
            'latitude' => $location?->latitude,
            'longitude' => $location?->longitude,
            'description' => $attrs['description'] ?? null,
            'is_exception' => false,
        ]);
    }
}
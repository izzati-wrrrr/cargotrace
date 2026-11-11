<?php

namespace App\Services;

use App\Enums\DelaySeverity;
use App\Models\Setting;
use App\Models\Shipment;

class DelayService
{
    public const DEFAULT_LOW_HOURS = 6;
    public const DEFAULT_MEDIUM_HOURS = 24;

    /** Batas delay (jam) dari tabel settings, dengan nilai bawaan dari slide 8. */
    public function thresholds(): array
    {
        return [
            'low' => (float) Setting::getValue('delay_low_hours', self::DEFAULT_LOW_HOURS),
            'medium' => (float) Setting::getValue('delay_medium_hours', self::DEFAULT_MEDIUM_HOURS),
        ];
    }

    /** Aturan: <= 0 on time; sampai batas LOW = LOW; sampai batas MEDIUM = MEDIUM; sisanya HIGH. */
    public function classify(int $delaySeconds, array $thresholds): DelaySeverity
    {
        if ($delaySeconds <= 0) {
            return DelaySeverity::None;
        }
        if ($delaySeconds <= $thresholds['low'] * 3600) {
            return DelaySeverity::Low;
        }
        if ($delaySeconds <= $thresholds['medium'] * 3600) {
            return DelaySeverity::Medium;
        }

        return DelaySeverity::High;
    }

    /** Menghitung delay beserta alasan dan nilai yang dipakai (slide 8). */
    public function evaluate(Shipment $shipment, ?array $thresholds = null): array
    {
        $thresholds ??= $this->thresholds();

        $expected = $shipment->expected_arrival_at;
        $estimated = $shipment->estimated_arrival_at ?? $expected;

        // Selisih dihitung dari timestamp supaya tanda plus/minusnya pasti benar.
        $delaySeconds = $estimated->getTimestamp() - $expected->getTimestamp();
        $severity = $this->classify($delaySeconds, $thresholds);

        $estimatedText = $estimated->format('d M Y H:i');
        $expectedText = $expected->format('d M Y H:i');

        if ($delaySeconds <= 0) {
            $reason = "ETA {$estimatedText} tidak lebih lambat dari jadwal {$expectedText}, jadi tidak ada keterlambatan.";
        } else {
            $reason = sprintf(
                'ETA %s lebih lambat %s dari jadwal %s. Batas: LOW sampai %s jam, MEDIUM sampai %s jam, di atasnya HIGH. Hasil: %s.',
                $estimatedText,
                self::humanize($delaySeconds),
                $expectedText,
                $thresholds['low'],
                $thresholds['medium'],
                $severity->value,
            );
        }

        return [
            'delay_seconds' => $delaySeconds,
            'severity' => $severity,
            'thresholds' => $thresholds,
            'reason' => $reason,
        ];
    }

    /** Menyimpan tingkat keterlambatan terbaru ke shipment. */
    public function refresh(Shipment $shipment, ?array $thresholds = null): DelaySeverity
    {
        $severity = $this->evaluate($shipment, $thresholds)['severity'];

        if ($shipment->delay_severity !== $severity) {
            $shipment->delay_severity = $severity;
            $shipment->save();
        }

        return $severity;
    }

    /** Menghitung ulang semua shipment, dipanggil setelah batas delay diubah. */
    public function refreshAll(): int
    {
        $thresholds = $this->thresholds();
        $count = 0;

        Shipment::query()->chunkById(200, function ($shipments) use ($thresholds, &$count) {
            foreach ($shipments as $shipment) {
                $this->refresh($shipment, $thresholds);
                $count++;
            }
        });

        return $count;
    }

    public static function humanize(int $seconds): string
    {
        $minutes = intdiv(abs($seconds), 60);
        $days = intdiv($minutes, 1440);
        $hours = intdiv($minutes % 1440, 60);
        $mins = $minutes % 60;

        $parts = [];
        if ($days) {
            $parts[] = "{$days} hari";
        }
        if ($hours) {
            $parts[] = "{$hours} jam";
        }
        if ($mins) {
            $parts[] = "{$mins} menit";
        }

        return $parts ? implode(' ', $parts) : '0 menit';
    }
}
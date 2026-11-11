<?php

namespace App\Http\Controllers;

use App\Enums\DelaySeverity;
use App\Enums\ShipmentStatus;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\DelayService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const CARRIER_COLORS = ['#3b82f6', '#f97316', '#64748b', '#22c55e', '#a855f7', '#ec4899', '#14b8a6', '#eab308'];

    public function index(Request $request)
    {
        $user = $request->user();

        // Semua angka dihitung dari shipment yang boleh dilihat user ini (customer: miliknya saja).
        $scope = fn ($q) => $user->isCustomer() ? $q->where('shipments.customer_id', $user->id) : $q;
        $shipments = fn () => $scope(Shipment::query());
        $exceptions = fn () => ShipmentException::query()
            ->whereHas('shipment', fn ($q) => $scope($q->whereNull('shipments.deleted_at')));

        // 1) Jumlah per status (satu query).
        $byStatus = $shipments()
            ->selectRaw('current_status, COUNT(*) as total')
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        // 2) Per carrier: total dan jumlah yang terlambat (satu query, shipment batal tidak dihitung).
        // Catatan: "delayed" adalah kata cadangan MySQL, jadi alias-nya memakai late_count.
        $carrierRows = $shipments()
            ->join('carriers', 'carriers.id', '=', 'shipments.carrier_id')
            ->where('shipments.current_status', '!=', ShipmentStatus::Cancelled->value)
            ->selectRaw("carriers.name, COUNT(*) as total, SUM(CASE WHEN shipments.delay_severity <> 'NONE' THEN 1 ELSE 0 END) as late_count")
            ->groupBy('carriers.id', 'carriers.name')
            ->orderBy('carriers.name')
            ->get();

        $activeTotal = (int) $carrierRows->sum('total');
        $delayedTotal = (int) $carrierRows->sum('late_count');

        $kpi = [
            'total' => (int) $byStatus->sum(),
            'in_transit' => (int) ($byStatus[ShipmentStatus::InTransit->value] ?? 0),
            'delivered' => (int) ($byStatus[ShipmentStatus::Delivered->value] ?? 0),
            'delayed' => $delayedTotal,
            'open_exceptions' => $exceptions()->whereIn('shipment_exceptions.status', ['OPEN', 'INVESTIGATING'])->count(),
        ];

        // 3) KPI operasional dari brief.
        $delivered = fn () => $shipments()->where('shipments.current_status', ShipmentStatus::Delivered->value);
        $deliveredTotal = $delivered()->count();
        $deliveredOnTime = $delivered()->where('shipments.delay_severity', DelaySeverity::None->value)->count();

        $avgDelayMinutes = $delivered()
            ->whereColumn('shipments.estimated_arrival_at', '>', 'shipments.expected_arrival_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, shipments.expected_arrival_at, shipments.estimated_arrival_at)) as avg_minutes')
            ->value('avg_minutes');

        $resolutionMinutes = $exceptions()
            ->where('shipment_exceptions.status', 'RESOLVED')
            ->whereNotNull('shipment_exceptions.resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, shipment_exceptions.detected_at, shipment_exceptions.resolved_at)) as avg_minutes')
            ->value('avg_minutes');

        $ops = [
            'on_time_rate' => $deliveredTotal ? round($deliveredOnTime / $deliveredTotal * 100, 1) : null,
            'delivered_total' => $deliveredTotal,
            'avg_delay' => $avgDelayMinutes !== null ? DelayService::humanize((int) round($avgDelayMinutes * 60)) : null,
            'resolution' => $resolutionMinutes !== null ? DelayService::humanize((int) round($resolutionMinutes * 60)) : null,
        ];

        // 4) Data grafik.
        $statusOrder = [
            ShipmentStatus::Created->value => '#94a3b8',
            ShipmentStatus::Booked->value => '#6366f1',
            ShipmentStatus::PickedUp->value => '#f59e0b',
            ShipmentStatus::InTransit->value => '#3b82f6',
            ShipmentStatus::Delivered->value => '#22c55e',
            ShipmentStatus::Cancelled->value => '#ef4444',
        ];
        $statusChart = [
            'labels' => array_map(fn ($s) => str_replace('_', ' ', $s), array_keys($statusOrder)),
            'values' => array_map(fn ($s) => (int) ($byStatus[$s] ?? 0), array_keys($statusOrder)),
            'colors' => array_values($statusOrder),
        ];

        $slices = $carrierRows->filter(fn ($row) => (int) $row->late_count > 0)->values();
        $carrierChart = [
            'labels' => $slices->pluck('name')->all(),
            'values' => $slices->map(fn ($row) => (int) $row->late_count)->all(),
            'totals' => $slices->map(fn ($row) => (int) $row->total)->all(),
            'rates' => $slices->map(fn ($row) => round($row->late_count / $row->total * 100, 1))->all(),
            'colors' => $slices->keys()->map(fn ($i) => self::CARRIER_COLORS[$i % count(self::CARRIER_COLORS)])->all(),
        ];
        $overallDelayRate = $activeTotal ? round($delayedTotal / $activeTotal * 100, 1) : 0;

        // 5) Peta: posisi terakhir semua shipment yang masih berjalan.
        $mapData = $shipments()
            ->whereNotIn('shipments.current_status', [ShipmentStatus::Delivered->value, ShipmentStatus::Cancelled->value])
            ->with(['origin', 'destination', 'latestPositionEvent.location'])
            ->latest('shipments.id')
            ->limit(100)
            ->get()
            ->map(function (Shipment $s) {
                $position = $s->latestPositionEvent;

                return [
                    'number' => $s->shipment_number,
                    'url' => route('shipments.show', $s),
                    'status' => str_replace('_', ' ', $s->current_status->value),
                    'delay' => $s->delay_severity->value,
                    'delay_label' => $s->delay_severity === DelaySeverity::None
                        ? 'Tepat waktu'
                        : 'Terlambat (' . $s->delay_severity->value . ')',
                    'route' => $s->origin->name . ' → ' . $s->destination->name,
                    'time' => $position?->occurred_at?->format('d M Y H:i'),
                    'origin' => [$s->origin->latitude, $s->origin->longitude],
                    'destination' => [$s->destination->latitude, $s->destination->longitude],
                    'current' => [$position?->latitude ?? $s->origin->latitude, $position?->longitude ?? $s->origin->longitude],
                ];
            })
            ->values();

        // 6) Tabel shipment terbaru, bisa difilter berdasarkan status.
        $recent = $shipments()
            ->with(['customer:id,name', 'carrier:id,name', 'origin:id,name,country_code', 'destination:id,name,country_code'])
            ->when($request->filled('status'), fn ($q) => $q->where('shipments.current_status', $request->input('status')))
            ->latest('shipments.id')
            ->limit(8)
            ->get();

        return view('dashboard', [
            'kpi' => $kpi,
            'ops' => $ops,
            'statusChart' => $statusChart,
            'carrierChart' => $carrierChart,
            'overallDelayRate' => $overallDelayRate,
            'mapData' => $mapData,
            'recent' => $recent,
            'statuses' => ShipmentStatus::cases(),
        ]);
    }
}
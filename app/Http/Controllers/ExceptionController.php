<?php

namespace App\Http\Controllers;

use App\Enums\DelaySeverity;
use App\Enums\ExceptionStatus;
use App\Enums\ExceptionType;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ExceptionController extends Controller
{
    public function __construct(private ExceptionService $service)
    {
    }

    public function index(Request $request)
    {
        $exceptions = ShipmentException::query()
            ->with(['shipment.customer:id,name', 'reporter:id,name'])
            // Relasi shipment memakai withTrashed, jadi shipment yang sudah dihapus disaring manual.
            ->whereHas('shipment', fn ($q) => $q->whereNull('shipments.deleted_at'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('severity'), fn ($q) => $q->where('severity', $request->input('severity')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->orderByRaw("FIELD(status, 'OPEN', 'INVESTIGATING', 'RESOLVED')")
            ->latest('detected_at')
            ->paginate(10)
            ->withQueryString();

        return view('exceptions.index', [
            'exceptions' => $exceptions,
            'statuses' => ExceptionStatus::cases(),
            'types' => ExceptionType::cases(),
            'severities' => [DelaySeverity::Low, DelaySeverity::Medium, DelaySeverity::High],
        ]);
    }

    public function store(Request $request, Shipment $shipment): RedirectResponse
    {
        Gate::authorize('manageExceptions', $shipment);

        $data = $request->validate([
            'type' => ['required', Rule::enum(ExceptionType::class)],
            'severity' => ['required', Rule::in(['LOW', 'MEDIUM', 'HIGH'])],
            'detected_at' => ['required', 'date', 'before_or_equal:now'],
            'details' => ['nullable', 'string', 'max:500'],
        ], [
            'detected_at.before_or_equal' => 'Waktu deteksi tidak boleh di masa depan.',
        ]);

        $this->service->open($shipment, [
            'type' => ExceptionType::from($data['type']),
            'severity' => DelaySeverity::from($data['severity']),
            'detected_at' => $data['detected_at'],
            'description' => $data['details'] ?? null,
        ], $request->user());

        return back()->with('success', 'Exception dilaporkan.');
    }

    public function updateStatus(Request $request, ShipmentException $exception): RedirectResponse
    {
        Gate::authorize('manageExceptions', $exception->shipment);

        $data = $request->validate([
            'new_status' => ['required', Rule::enum(ExceptionStatus::class)],
            'note' => ['nullable', 'string', 'max:500', 'required_if:new_status,RESOLVED'],
        ], [
            'note.required_if' => 'Catatan penyelesaian wajib diisi saat exception diselesaikan.',
        ]);

        $this->service->updateStatus(
            $exception,
            ExceptionStatus::from($data['new_status']),
            $data['note'] ?? null,
            $request->user(),
        );

        return back()->with('success', 'Status exception diperbarui.');
    }
}
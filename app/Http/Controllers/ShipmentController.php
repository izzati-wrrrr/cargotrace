<?php

namespace App\Http\Controllers;

use App\Enums\ShipmentStatus;
use App\Http\Requests\ShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ShipmentController extends Controller
{
    public function __construct(private ShipmentService $service)
    {
    }

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Shipment::class);
        $user = $request->user();

        $shipments = Shipment::query()
            ->with(['customer:id,name', 'carrier:id,name', 'origin:id,name,country_code', 'destination:id,name,country_code'])
            // Customer hanya boleh melihat shipment miliknya sendiri.
            ->when($user->isCustomer(), fn ($q) => $q->where('customer_id', $user->id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->input('q');
                $q->where(fn ($w) => $w->where('shipment_number', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%")));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('current_status', $request->input('status')))
            ->when($request->filled('carrier_id'), fn ($q) => $q->where('carrier_id', $request->input('carrier_id')))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('shipments.index', [
            'shipments' => $shipments,
            'carriers' => Carrier::orderBy('name')->get(['id', 'name']),
            'statuses' => ShipmentStatus::cases(),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Shipment::class);

        return view('shipments.create', $this->formData());
    }

    public function store(ShipmentRequest $request): RedirectResponse
    {
        $shipment = $this->service->create(
            $request->safe()->except('items'),
            $request->validated('items'),
            $request->user(),
        );

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} berhasil dibuat.");
    }

    public function show(Shipment $shipment)
    {
        Gate::authorize('view', $shipment);

        $shipment->load(['customer', 'carrier', 'origin', 'destination', 'items', 'trackingEvents.location', 'trackingEvents.creator']);

        return view('shipments.show', [
            'shipment' => $shipment,
            'locations' => Location::orderBy('country_code')->orderBy('name')->get(['id', 'name', 'city', 'country_code']),
        ]);
    }

    public function edit(Shipment $shipment)
    {
        Gate::authorize('update', $shipment);

        $shipment->load('items');

        return view('shipments.edit', ['shipment' => $shipment] + $this->formData());
    }

    public function update(ShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->service->update($shipment, $request->safe()->except('items'), $request->validated('items'));

        return redirect()->route('shipments.show', $shipment)->with('success', 'Shipment berhasil diperbarui.');
    }

    public function updateStatus(Request $request, Shipment $shipment): RedirectResponse
    {
        Gate::authorize('changeStatus', $shipment);

        $data = $request->validate([
            'status' => ['required', Rule::enum(ShipmentStatus::class)],
            'location_id' => ['nullable', 'exists:locations,id'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $this->service->changeStatus(
            $shipment,
            ShipmentStatus::from($data['status']),
            $request->user(),
            $data['location_id'] ?? null,
            $data['description'] ?? null,
        );

        return back()->with('success', 'Status shipment diperbarui.');
    }

    public function destroy(Shipment $shipment): RedirectResponse
    {
        Gate::authorize('delete', $shipment);

        $shipment->delete(); // soft delete: datanya tetap ada untuk audit

        return redirect()->route('shipments.index')->with('success', 'Shipment dihapus.');
    }

    private function formData(): array
    {
        return [
            'customers' => User::where('role', 'customer')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'carriers' => Carrier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'locations' => Location::orderBy('country_code')->orderBy('name')->get(['id', 'name', 'city', 'country_code']),
        ];
    }
}
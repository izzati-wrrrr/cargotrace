<?php

namespace App\Http\Controllers;

use App\Enums\ShipmentStatus;
use App\Http\Requests\TrackingEventRequest;
use App\Models\Shipment;
use App\Services\ShipmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(private ShipmentService $service)
    {
    }

    /** Daftar shipment yang masih berjalan, lengkap dengan posisi terakhirnya. */
    public function index(Request $request)
    {
        $user = $request->user();

        $shipments = Shipment::query()
            ->with([
                'customer:id,name',
                'origin:id,name,country_code',
                'destination:id,name,country_code',
                'latestPositionEvent.location:id,name,country_code',
            ])
            ->whereNotIn('current_status', [ShipmentStatus::Delivered->value, ShipmentStatus::Cancelled->value])
            // Customer hanya melihat shipment miliknya.
            ->when($user->isCustomer(), fn ($q) => $q->where('customer_id', $user->id))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = $request->input('q');
                $q->where(fn ($w) => $w->where('shipment_number', 'like', "%{$term}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%")));
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('tracking.index', compact('shipments'));
    }

    public function store(TrackingEventRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->service->addEvent($shipment, $request->validated(), $request->user());

        return back()->with('success', 'Event tracking ditambahkan.');
    }
}
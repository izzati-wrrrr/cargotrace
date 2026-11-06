<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationRequest;
use App\Models\Location;
use App\Models\TrackingEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->input('q');
                $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                    ->orWhere('city', 'like', "%{$q}%")
                    ->orWhere('country_code', 'like', "%{$q}%"));
            })
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->orderBy('country_code')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        // Semua lokasi (tanpa pagination) untuk penanda di peta ringkasan.
        $mapPoints = Location::get(['id', 'name', 'city', 'country_code', 'type', 'latitude', 'longitude']);

        return view('locations.index', compact('locations', 'mapPoints'));
    }

    public function create()
    {
        return view('locations.create');
    }

    public function store(LocationRequest $request): RedirectResponse
    {
        Location::create($request->validated());

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil ditambahkan.');
    }

    public function edit(Location $location)
    {
        return view('locations.edit', compact('location'));
    }

    public function update(LocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil diperbarui.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        // Lokasi yang sudah dipakai shipment atau riwayat tracking tidak boleh dihapus.
        $dipakai = $location->originShipments()->withTrashed()->exists()
            || $location->destinationShipments()->withTrashed()->exists()
            || TrackingEvent::where('location_id', $location->id)->exists();

        if ($dipakai) {
            return back()->with('error', 'Lokasi sudah dipakai shipment atau tracking dan tidak bisa dihapus.');
        }

        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Lokasi berhasil dihapus.');
    }
}
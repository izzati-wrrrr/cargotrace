<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarrierRequest;
use App\Models\Carrier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CarrierController extends Controller
{
    public function index(Request $request)
    {
        $carriers = Carrier::query()
            ->withCount('shipments')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->input('q');
                $query->where(fn ($w) => $w->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%"));
            })
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->input('status') === 'active'))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('carriers.index', compact('carriers'));
    }

    public function create()
    {
        return view('carriers.create');
    }

    public function store(CarrierRequest $request): RedirectResponse
    {
        Carrier::create($request->validated());

        return redirect()->route('carriers.index')->with('success', 'Carrier berhasil ditambahkan.');
    }

    public function edit(Carrier $carrier)
    {
        return view('carriers.edit', compact('carrier'));
    }

    public function update(CarrierRequest $request, Carrier $carrier): RedirectResponse
    {
        $carrier->update($request->validated());

        return redirect()->route('carriers.index')->with('success', 'Carrier berhasil diperbarui.');
    }

    public function destroy(Carrier $carrier): RedirectResponse
    {
        // withTrashed: shipment yang sudah di-soft-delete tetap merujuk carrier ini.
        if ($carrier->shipments()->withTrashed()->exists()) {
            return back()->with('error', 'Carrier sudah dipakai shipment dan tidak bisa dihapus. Nonaktifkan saja.');
        }

        $carrier->delete();

        return redirect()->route('carriers.index')->with('success', 'Carrier berhasil dihapus.');
    }
}
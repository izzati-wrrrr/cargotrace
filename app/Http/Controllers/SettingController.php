<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\DelayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private DelayService $delay)
    {
    }

    public function edit()
    {
        return view('settings.edit', ['thresholds' => $this->delay->thresholds()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'delay_low_hours' => ['required', 'numeric', 'gt:0', 'max:720'],
            'delay_medium_hours' => ['required', 'numeric', 'gt:delay_low_hours', 'max:8760'],
        ], [
            'delay_medium_hours.gt' => 'Batas MEDIUM harus lebih besar dari batas LOW.',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        // Nilai lama sudah tidak berlaku, jadi semua tingkat keterlambatan dihitung ulang.
        $count = $this->delay->refreshAll();

        return back()->with('success', "Batas delay disimpan. Tingkat keterlambatan {$count} shipment dihitung ulang.");
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\DelayService;
use App\Services\ExchangeRateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function __construct(private DelayService $delay, private ExchangeRateService $rates)
    {
    }

    public function edit()
    {
        return view('settings.edit', [
            'thresholds' => $this->delay->thresholds(),
            'baseCurrency' => Setting::getValue('base_currency', 'IDR'),
            'currencies' => $this->rates->options(),
            'rateStatus' => $this->rates->status(),
        ]);
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

    public function updateCurrency(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'base_currency' => ['required', 'string', 'size:3', Rule::exists('currencies', 'code')],
        ]);

        Setting::updateOrCreate(
            ['key' => 'base_currency'],
            ['value' => $data['base_currency'], 'description' => 'Mata uang dasar perusahaan untuk tampilan staf'],
        );

        return back()->with('success', 'Mata uang dasar diubah ke ' . $data['base_currency'] . '.');
    }

    public function refreshRates(): RedirectResponse
    {
        $result = $this->rates->refresh();

        return back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }
}
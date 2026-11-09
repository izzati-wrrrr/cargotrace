<?php

namespace App\Http\Requests;

use App\Models\Shipment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $shipment = $this->route('shipment'); // null saat buat, model saat edit

        return $shipment
            ? $this->user()->can('update', $shipment)
            : $this->user()->can('create', Shipment::class);
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', Rule::exists('users', 'id')->where('role', 'customer')->where('is_active', true)],
            'carrier_id' => ['required', Rule::exists('carriers', 'id')->where('is_active', true)],
            'origin_location_id' => ['required', 'exists:locations,id'],
            'destination_location_id' => ['required', 'exists:locations,id', 'different:origin_location_id'],
            'cargo_type' => ['required', 'string', 'max:100'],
            'weight_kg' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'volume_cbm' => ['nullable', 'numeric', 'gt:0', 'max:9999999999'],
            'expected_departure_at' => ['required', 'date'],
            'expected_arrival_at' => ['required', 'date', 'after:expected_departure_at'],

            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'items.*.weight_kg' => ['nullable', 'numeric', 'gt:0', 'max:9999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'destination_location_id.different' => 'Lokasi asal dan tujuan tidak boleh sama.',
            'expected_arrival_at.after' => 'Perkiraan tiba harus setelah perkiraan berangkat.',
            'items.required' => 'Minimal harus ada satu barang.',
            'items.min' => 'Minimal harus ada satu barang.',
        ];
    }
}
<?php

namespace App\Http\Requests;

use App\Enums\TrackingEventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackingEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('addEvent', $this->route('shipment'));
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', Rule::enum(TrackingEventType::class)],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
            // Wajib ada lokasi ATAU koordinat.
            'location_id' => ['nullable', 'exists:locations,id', 'required_without:latitude'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_without:location_id', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'occurred_at.before_or_equal' => 'Waktu kejadian tidak boleh di masa depan.',
            'location_id.required_without' => 'Pilih lokasi, atau isi koordinat latitude dan longitude.',
            'latitude.required_without' => 'Isi koordinat, atau pilih lokasi.',
            'latitude.required_with' => 'Latitude dan longitude harus diisi berpasangan.',
            'longitude.required_with' => 'Latitude dan longitude harus diisi berpasangan.',
        ];
    }
}
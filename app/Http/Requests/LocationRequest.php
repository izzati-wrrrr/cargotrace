<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Akses sudah dibatasi middleware role di routes.
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'country_code' => strtoupper((string) $this->input('country_code')),
            'type' => strtoupper((string) $this->input('type')),
        ]);
    }

    public function rules(): array
    {
        $location = $this->route('location'); // null saat tambah, model saat ubah

        return [
            'name' => [
                'required', 'string', 'max:255',
                // Nama boleh sama asal negaranya beda (kombinasi nama + negara harus unik).
                Rule::unique('locations', 'name')
                    ->where('country_code', $this->input('country_code'))
                    ->ignore($location?->id),
            ],
            'city' => ['nullable', 'string', 'max:120'],
            'country_code' => ['required', 'string', 'size:2', 'alpha'],
            'type' => ['required', Rule::in(['PORT', 'CITY', 'WAREHOUSE'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Lokasi dengan nama dan negara yang sama sudah ada.',
            'country_code.size' => 'Kode negara harus 2 huruf (contoh: ID, SG, NL).',
        ];
    }
}
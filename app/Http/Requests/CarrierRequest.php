<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CarrierRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Akses sudah dibatasi middleware role di routes.
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper((string) $this->input('code')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $carrier = $this->route('carrier'); // null saat tambah, model saat ubah

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'alpha_num', 'max:20', Rule::unique('carriers', 'code')->ignore($carrier?->id)],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
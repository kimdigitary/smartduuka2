<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'multiCurrency' => filter_var($this->multiCurrency, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:300'],
            'taxRegistrationNo' => ['sometimes', 'string', 'max:255'],
            'email'             => ['sometimes', 'email', 'max:255'],
            'phone'             => ['sometimes', 'string', 'max:50'],
            'address'           => ['sometimes', 'string', 'max:500'],
            'currencyId'        => ['required', 'integer', 'exists:ifrs_currencies,id'],
            'yearStart'         => ['required', 'integer', 'between:1,12'],
            'multiCurrency'     => ['required', 'boolean'],
        ];
    }
}

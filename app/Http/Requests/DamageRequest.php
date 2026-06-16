<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DamageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return TRUE;
    }

    public function rules(): array
    {
        return [
            'date'         => ['required', 'date'],
            'product_id'   => ['required', 'numeric'],
            'variation_id' => ['sometimes', 'numeric'],
            'quantity'     => ['required', 'numeric'],
            'reason'       => ['required', 'string'],
            'notes'        => ['sometimes', 'string'],
            'image'        => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
            'branch_id'    => ['required', 'integer', 'min:1'],
        ];
    }

}

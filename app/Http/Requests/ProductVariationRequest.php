<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductVariationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return TRUE;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'numeric', 'not_in:0'],
            'variations' => ['required', 'string'],
            'branch_id'  => ['required', 'integer', 'min:1'],
        ];
    }
}

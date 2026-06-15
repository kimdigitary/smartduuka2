<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LegacyDebtRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'debts'     => ['required', 'string'],
            'branch_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return TRUE;
    }
}

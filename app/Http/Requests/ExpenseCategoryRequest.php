<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseCategoryRequest extends FormRequest
{

    public function authorize(): bool
    {
        return TRUE;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'parent_id'   => ['nullable', 'numeric'],
            'branch_id'   => ['required', 'integer', 'min:1'],
            'status'      => ['required', 'integer'],
            'description' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['parent_id' => $this->parent_id == '0' ? NULL : $this->parent_id]);
    }
}

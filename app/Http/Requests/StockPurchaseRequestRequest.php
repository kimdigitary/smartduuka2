<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockPurchaseRequestRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'requester_name' => ['required'],
            'department'     => ['required', 'integer'],
            'priority'       => ['required', 'integer'],
            'supplier_id'    => ['required', 'numeric:'],
            'reason'         => ['required'],
            'items'          => ['required', 'string'],
            'branch_id'      => ['required', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return TRUE;
    }
}

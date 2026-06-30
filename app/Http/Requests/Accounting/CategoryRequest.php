<?php

    namespace App\Http\Requests\Accounting;

    use App\Support\Accounting\AccountTypes;
    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class CategoryRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        public function rules() : array
        {
            return [
                'name'         => [ 'required', 'string', 'max:300' ],
                'categoryType' => [ 'required', Rule::in( AccountTypes::ALL ) ],
            ];
        }
    }

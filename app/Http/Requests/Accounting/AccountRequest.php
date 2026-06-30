<?php

    namespace App\Http\Requests\Accounting;

    use App\Support\Accounting\AccountTypes;
    use Illuminate\Foundation\Http\FormRequest;
    use Illuminate\Validation\Rule;

    class AccountRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return TRUE;
        }

        protected function prepareForValidation() : void
        {
            // partyRef arrives as a JSON string (nested object serialized by the
            // frontend); flatten it for validation.
            $ref = $this->input( 'partyRef' );
            if ( is_string( $ref ) ) {
                $ref = json_decode( $ref, TRUE ) ?: [];
            }
            $ref = is_array( $ref ) ? $ref : [];

            $this->merge( [
                'isActive'        => $this->has( 'isActive' ) ? filter_var( $this->isActive, FILTER_VALIDATE_BOOLEAN ) : TRUE,
                'isPettyCash'     => filter_var( $this->isPettyCash, FILTER_VALIDATE_BOOLEAN ),
                'partyType'       => $ref[ 'type' ] ?? NULL,
                'partyExternalId' => isset( $ref[ 'externalId' ] ) ? (string) $ref[ 'externalId' ] : NULL,
            ] );
        }

        public function rules() : array
        {
            return [
                'name'            => [ 'required', 'string', 'max:300' ],
                'accountType'     => [ 'required', Rule::in( AccountTypes::ALL ) ],
                'code'            => [ 'nullable', 'string', 'max:50' ],
                'categoryId'      => [ 'nullable', 'integer', 'exists:ifrs_categories,id' ],
                'currencyId'      => [ 'nullable', 'integer', 'exists:ifrs_currencies,id' ],
                'description'     => [ 'nullable', 'string', 'max:1000' ],
                'isActive'        => [ 'boolean' ],
                'isPettyCash'     => [ 'boolean' ],
                'partyType'       => [ 'nullable', 'in:customer,supplier' ],
                'partyExternalId' => [ 'nullable', 'string', 'max:100' ],
            ];
        }
    }

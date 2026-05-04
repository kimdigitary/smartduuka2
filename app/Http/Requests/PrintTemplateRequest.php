<?php

    namespace App\Http\Requests;

    use Illuminate\Foundation\Http\FormRequest;

    class PrintTemplateRequest extends FormRequest
    {
        public function rules() : array
        {
            return [
                'name'             => [ 'required' ] ,
                'type'             => [ 'required' , 'integer' ] ,
                'design'           => [ 'required' , 'integer' ] ,
                'is_default'       => [ 'boolean' ] ,
                'store_name'       => [ 'required' ] ,
                'address'          => [ 'required' ] ,
                'phone'            => [ 'required' ] ,
                'show_logo'        => [ 'boolean' ] ,
                'logo_size'        => [ 'required' , 'integer' ] ,
                'show_quantity'    => [ 'boolean' ] ,
                'show_price'       => [ 'boolean' ] ,
                'show_tax'         => [ 'boolean' ] ,
                'footer_message'   => [ 'required' ] ,
                'show_barcode'     => [ 'boolean' ] ,
                'terms'            => [ 'nullable' ] ,
                'thermal_size'     => [ 'required' ] ,
                'has_borders'      => [ 'boolean' ] ,
                'text_bold'        => [ 'boolean' ] ,
                'large_text'       => [ 'boolean' ] ,
                'color_theme'      => [ 'required' ] ,
                'secondary_color'  => [ 'required' ] ,
                'page_orientation' => [ 'nullable' , 'integer' ] ,
            ];
        }

        public function authorize() : bool
        {
            return TRUE;
        }

        protected function prepareForValidation()
        {
            $this->merge( [
                'is_default'    => $this->toBoolean( $this->is_default ) ,
                'show_logo'     => $this->toBoolean( $this->show_logo ) ,
                'show_quantity' => $this->toBoolean( $this->show_quantity ) ,
                'show_price'    => $this->toBoolean( $this->show_price ) ,
                'show_tax'      => $this->toBoolean( $this->show_tax ) ,
                'show_barcode'  => $this->toBoolean( $this->show_barcode ) ,
                'has_borders'   => $this->toBoolean( $this->has_borders ) ,
                'text_bold'     => $this->toBoolean( $this->text_bold ) ,
                'large_text'    => $this->toBoolean( $this->large_text ) ,
            ] );
        }

        private function toBoolean($value)
        {
            return filter_var( $value , FILTER_VALIDATE_BOOLEAN , FILTER_NULL_ON_FAILURE );
        }
    }

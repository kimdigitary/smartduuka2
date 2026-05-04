<?php

    namespace App\Http\Resources;

    use App\Models\PrintTemplate;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin PrintTemplate */
    class PrintTemplateResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'               => $this->id ,
                'name'             => $this->name ,
                'type'             => new TemplateTypeResource( $this->whenLoaded( 'templateType' ) ) ,
                'design'           => new PrintDesignResource( $this->whenLoaded( 'templateDesign' ) ) ,
                'is_default'       => $this->is_default ,
                'store_name'       => $this->store_name ,
                'address'          => $this->address ,
                'phone'            => $this->phone ,
                'show_logo'        => $this->show_logo ,
                'logo_size'        => $this->logo_size ,
                'show_quantity'    => $this->show_quantity ,
                'show_price'       => $this->show_price ,
                'show_tax'         => $this->show_tax ,
                'footer_message'   => $this->footer_message ,
                'show_barcode'     => $this->show_barcode ,
                'page_orientation' => $this->page_orientation ,
                'terms'            => $this->terms ,
                'thermal_size'     => $this->thermal_size ,
                'has_borders'      => $this->has_borders ,
                'text_bold'        => $this->text_bold ,
                'large_text'       => $this->large_text ,
                'color_theme'      => $this->color_theme ,
                'secondary_color'  => $this->secondary_color ?? '' ,
            ];
        }
    }

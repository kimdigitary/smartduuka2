<?php

    namespace App\Models;

    use App\Enums\DesignStyle;
    use App\Enums\PageOrientation;
    use App\Enums\ThermalSize;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;

    class PrintTemplate extends Model
    {

        protected $fillable = [
            'name' ,
            'type' ,
            'design' ,
            'is_default' ,
            'store_name' ,
            'address' ,
            'phone' ,
            'show_logo' ,
            'logo_size' ,
            'show_quantity' ,
            'show_price' ,
            'show_tax' ,
            'footer_message' ,
            'show_barcode' ,
            'terms' ,
            'thermal_size' ,
            'has_borders' ,
            'text_bold' ,
            'large_text' ,
            'color_theme' ,
            'page_orientation' ,
            'secondary_color' ,
            'branch_id',
        ];

        protected function casts() : array
        {
            return [
                'thermal_size'     => ThermalSize::class ,
                'design'           => DesignStyle::class ,
                'page_orientation' => PageOrientation::class ,
                'is_default'       => 'boolean' ,
                'type'             => 'integer' ,
                'show_logo'        => 'boolean' ,
                'show_quantity'    => 'boolean' ,
                'show_price'       => 'boolean' ,
                'show_tax'         => 'boolean' ,
                'show_barcode'     => 'boolean' ,
                'has_borders'      => 'boolean' ,
                'text_bold'        => 'boolean' ,
                'large_text'       => 'boolean' ,
            ];
        }

        public function templateType() : BelongsTo
        {
            return $this->belongsTo( TemplateType::class , 'type' );
        }

        public function templateDesign()
        {
            return $this->belongsTo( PrintDesign::class , 'design' );
        }
    }

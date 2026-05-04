<?php

    namespace App\Enums;

    use JsonSerializable;

    enum DesignStyle : int implements JsonSerializable
    {
        case CLASSIC              = 1;
        case MODERN               = 2;
        case MINIMAL              = 3;
        case BORDERED_TABLE       = 4;
        case BOLD_COMPACT         = 5;
        case PROFESSIONAL_BLUE    = 6;
        case ECO_SAVER            = 7;
        case GRID_SYSTEM          = 8;
        case HEADER_FOCUS         = 9;
        case ELEGANT_SERIF        = 10;
        case INDUSTRIAL_MONO      = 11;
        case HIGH_CONTRAST        = 12;
        case OCEAN_WAVE_COLOR     = 13;
        case GREEN_LEAF_COLOR     = 14;
        case CRIMSON_LUXURY_COLOR = 15;
        case SUMMARY_REPORT       = 16;
        case DETAILED_ANALYTICS   = 17;
        case GRAPH_FOCUS          = 18;
        case INVENTORY_AUDIT      = 19;
        case SALES_VIBE           = 20;

        public function label() : string
        {
            return match ( $this ) {
                self::CLASSIC              => 'Classic' ,
                self::MODERN               => 'Modern' ,
                self::MINIMAL              => 'Minimal' ,
                self::BORDERED_TABLE       => 'Bordered Table' ,
                self::BOLD_COMPACT         => 'Bold Compact' ,
                self::PROFESSIONAL_BLUE    => 'Professional Blue' ,
                self::ECO_SAVER            => 'Eco Saver' ,
                self::GRID_SYSTEM          => 'Grid System' ,
                self::HEADER_FOCUS         => 'Header Focus' ,
                self::ELEGANT_SERIF        => 'Elegant Serif' ,
                self::INDUSTRIAL_MONO      => 'Industrial Mono' ,
                self::HIGH_CONTRAST        => 'High Contrast' ,
                self::OCEAN_WAVE_COLOR     => 'Ocean Wave Color' ,
                self::GREEN_LEAF_COLOR     => 'Green Leaf Color' ,
                self::CRIMSON_LUXURY_COLOR => 'Crimson Luxury Color' ,
                self::SUMMARY_REPORT       => 'Summary Report' ,
                self::DETAILED_ANALYTICS   => 'Detailed Analytics' ,
                self::GRAPH_FOCUS          => 'Graph Focus' ,
                self::INVENTORY_AUDIT      => 'Inventory Audit' ,
                self::SALES_VIBE           => 'Sales Vibe' ,
            };
        }

        public function jsonSerialize() : array
        {
            return [
                'value' => $this->value ,
                'label' => $this->label() ,
            ];
        }
    }

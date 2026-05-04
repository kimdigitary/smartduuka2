<?php

    namespace App\Enums;

    use JsonSerializable;

    enum TemplateType : int implements JsonSerializable
    {
        case THERMAL  = 1;
        case A4       = 2;
        case KITCHEN  = 3;
        case PREORDER = 4;
        case REPORT   = 5;

        public function label() : string
        {
            return match ( $this ) {
                self::THERMAL  => 'Thermal' ,
                self::A4       => 'A4' ,
                self::KITCHEN  => 'Kitchen' ,
                self::PREORDER => 'Preorder' ,
                self::REPORT   => 'Report' ,
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

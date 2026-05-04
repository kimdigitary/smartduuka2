<?php

    namespace App\Enums;

    use JsonSerializable;

    enum ThermalSize : int implements JsonSerializable
    {
        case EIGHTY_MM      = 1;
        case FIFTY_EIGHT_MM = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::EIGHTY_MM      => '80mm' ,
                self::FIFTY_EIGHT_MM => '58mm' ,
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

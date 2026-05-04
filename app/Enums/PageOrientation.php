<?php

    namespace App\Enums;

    use JsonSerializable;

    enum PageOrientation : int implements JsonSerializable
    {
        case Portrait  = 1;
        case Landscape = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::Portrait  => 'Portrait' ,
                self::Landscape => 'Landscape' ,
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

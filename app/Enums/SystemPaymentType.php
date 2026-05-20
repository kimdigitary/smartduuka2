<?php

    namespace App\Enums;

    use JsonSerializable;

    enum SystemPaymentType : int implements JsonSerializable
    {
        case SUBSCRIPTION = 1;
        case MODULE       = 2;

        public function label() : string
        {
            return match ( $this ) {
                self::SUBSCRIPTION => 'Subscription' ,
                self::MODULE       => 'Module' ,
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

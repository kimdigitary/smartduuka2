<?php

    namespace App\Enums;

    use JsonSerializable;

    enum SystemPaymentType : int implements JsonSerializable
    {
        case SUBSCRIPTION = 1;
        case MODULE       = 2;
        case BRANCH       = 3;
        case NEW_CLIENT   = 4;

        public function label() : string
        {
            return match ( $this ) {
                self::SUBSCRIPTION => 'Subscription' ,
                self::MODULE       => 'Module' ,
                self::BRANCH       => 'Branch' ,
                self::NEW_CLIENT   => 'New Client' ,
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

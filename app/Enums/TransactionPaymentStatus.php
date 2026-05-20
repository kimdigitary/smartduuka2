<?php

    namespace App\Enums;

    use JsonSerializable;

    enum TransactionPaymentStatus : int implements JsonSerializable
    {
        case PENDING    = 1;
        case FAILED     = 2;
        case SUCCESSFUL = 3;

        public function label() : string
        {
            return match ( $this ) {
                self::PENDING    => 'Pending' ,
                self::FAILED     => 'Failed' ,
                self::SUCCESSFUL => 'Successful' ,
            };
        }

        public function jsonSerialize() : array
        {
            return [
                'label' => $this->label() ,
                'value' => $this->value ,
            ];
        }
    }

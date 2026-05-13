<?php

    namespace App\Enums;

    use JsonSerializable;

    enum SubscriptionPlanType : int implements JsonSerializable
    {
        case Starter    = 1;
        case Existing   = 2;
        case Pro        = 3;
        case Enterprise = 4;

        public function label() : string
        {
            return match ( $this ) {
                self::Starter    => 'Starter' ,
                self::Existing   => 'Existing' ,
                self::Pro        => 'Pro' ,
                self::Enterprise => 'Enterprise' ,
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

<?php

    namespace App\Enums;

    enum ReservedTenantNames : string
    {
        case App         = 'app';
        case MainApp     = 'mainapp';
        case Admin       = 'admin';
        case Api         = 'api';
        case CashflowApp = 'cashflowapp';
        case Cashflow    = 'cashflow';

        public static function toArray() : array
        {
            return array_column( self::cases() , 'value' );
        }
    }

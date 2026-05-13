<?php

    namespace App\Support;

    use App\Enums\PlanFeature;

    class PlanFeatureMap
    {
        /**
         * Features available per plan name.
         * Higher plans inherit lower plan features.
         */
        public static array $map = [
            'Starter' => [
                PlanFeature::SALES_300 ,
                PlanFeature::ITEMS_100 ,
                PlanFeature::USERS_3 ,
                PlanFeature::DASHBOARD ,
                PlanFeature::BASIC_INVENTORY ,
                PlanFeature::STOCK ,
                PlanFeature::BASIC_PURCHASES ,
                PlanFeature::BASIC_REPORTS ,
                PlanFeature::BASIC_SALES_RPT ,
                PlanFeature::SUPPORT_24_7 ,
            ] ,

            'Professional' => [
                PlanFeature::SALES_2000 ,
                PlanFeature::ITEMS_500 ,
                PlanFeature::USERS_5 ,
                PlanFeature::WAREHOUSE ,
                PlanFeature::STOCK_TRANSFERS ,
                PlanFeature::SUPPLIER_MGT ,
                PlanFeature::ACCOUNTING ,
                PlanFeature::EFRIS ,
                PlanFeature::ADVANCED_REPORTS ,
                PlanFeature::SUPPORT_24_7 ,
            ] ,

            'Enterprise' => [
                PlanFeature::SALES_UNLIMITED ,
                PlanFeature::ITEMS_UNLIMITED ,
                PlanFeature::USERS_UNLIMITED ,
                PlanFeature::ALL_PRO ,
                PlanFeature::PRODUCTION ,
                PlanFeature::PROJECT_MGT ,
                PlanFeature::SUPPORT_24_7 ,
            ] ,
        ];

        /**
         * Check if a plan name has a given feature.
         */
        public static function has(string $planName , PlanFeature $feature) : bool
        {
            $features = self::$map[ $planName ] ?? [];

            return in_array( $feature , $features , strict: TRUE );
        }
    }
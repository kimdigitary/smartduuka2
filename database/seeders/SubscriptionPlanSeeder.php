<?php

    namespace Database\Seeders;

    use App\Enums\SubscriptionPlanType;
    use App\Models\SubscriptionPlan;
    use Illuminate\Database\Seeder;

    class SubscriptionPlanSeeder extends Seeder
    {

        public function run() : void
        {
            $plans = [
                /**
                 * Existing Customer Packages
                 */
                [
                    'name'        => 'Starter' ,
                    'description' => 'Ideal for new businesses.' ,
                    'base_amount' => 26000 ,
                    'position'    => 1 ,
                    'popular'     => FALSE ,
                    'type'        => SubscriptionPlanType::Existing ,
                    'features'    => [
                        'Max 300 sales / mo'                => TRUE ,
                        'Max 100 items (No variations)'     => TRUE ,
                        'Up to 3 Users'                     => TRUE ,
                        'Dashboard, Sales, Customers'       => TRUE ,
                        'Basic Inventory'                   => TRUE ,
                        'Stock List, Reconciliation'        => TRUE ,
                        'Basic Purchases'                   => TRUE ,
                        'Basic Reports, Expenses, Debt Mgt' => TRUE ,
                        'Basic Sales reports'               => TRUE ,
                        '24/7 support'                      => TRUE ,
                    ] ,
                ] ,
                [
                    'name'        => 'Professional' ,
                    'description' => 'For established businesses.' ,
                    'base_amount' => 52000 ,
                    'position'    => 2 ,
                    'popular'     => TRUE ,
                    'type'        => SubscriptionPlanType::Existing ,
                    'features'    => [
                        'Max 2,000 sales / mo'                                        => TRUE ,
                        'Max 500 items (With variations)'                             => TRUE ,
                        'Up to 5 Users'                                               => TRUE ,
                        'All Starter features'                                        => TRUE ,
                        'Basic Inventory, Warehouse & Storage'                        => TRUE ,
                        'Stock List, Reconciliation, Stock Transfers, Stock Requests' => TRUE ,
                        'Basic Purchases, Supplier Mgt, Purchase Returns'             => TRUE ,
                        'Basic Reports, Expenses, Debt Mgt, Accounting'               => TRUE ,
                        'Efris Intergrations'                                         => TRUE ,
                        'Advanced Reports'                                            => TRUE ,
                        '24/7 support'                                                => TRUE ,
                    ] ,
                ] ,
                [
                    'name'        => 'Enterprise' ,
                    'description' => 'For large businesses' ,
                    'base_amount' => 105_000 ,
                    'position'    => 3 ,
                    'popular'     => FALSE ,
                    'type'        => SubscriptionPlanType::Existing ,
                    'features'    => [
                        'Unlimited Sales'                                     => TRUE ,
                        'Unlimited Inventory'                                 => TRUE ,
                        'Unlimited Users'                                     => TRUE ,
                        'All Pro features'                                    => TRUE ,
                        'Production, HR, Asset Mgt, cashflow App'             => TRUE ,
                        'Advanced Reports, Project Mgt, Commissions, Loyalty' => TRUE ,
                        '24/7 support'                                        => TRUE ,
                    ] ,
                ] ,
                /**
                 * New Customer Packages
                 */
                [
                    'name'        => 'Starter' ,
                    'description' => 'For small shops & kiosks' ,
                    'base_amount' => 26000 ,
                    'position'    => 4 ,
                    'setup'       => 350_000 ,
                    'popular'     => FALSE ,
                    'type'        => SubscriptionPlanType::Starter ,
                    'features'    => [
                        'Max 300 sales / mo'                => TRUE ,
                        'Max 100 items (No variations)'     => TRUE ,
                        'Up to 3 Users'                     => TRUE ,
                        'Dashboard, Sales, Customers'       => TRUE ,
                        'Basic Inventory'                   => TRUE ,
                        'Stock List, Reconciliation'        => TRUE ,
                        'Basic Purchases'                   => TRUE ,
                        'Basic Reports, Expenses, Debt Mgt' => TRUE ,
                        'Basic Sales reports'               => TRUE ,
                        '24/7 support'                      => TRUE ,
                    ] ,
                ] ,
                [
                    'name'        => 'Professional' ,
                    'description' => 'For growing businesses' ,
                    'base_amount' => 52000 ,
                    'setup'       => 500_000 ,
                    'position'    => 5 ,
                    'popular'     => TRUE ,
                    'type'        => SubscriptionPlanType::Starter ,
                    'features'    => [
                        'Max 2,000 sales / mo'                                        => TRUE ,
                        'Max 500 items (With variations)'                             => TRUE ,
                        'Up to 5 Users'                                               => TRUE ,
                        'All Starter features'                                        => TRUE ,
                        'Basic Inventory, Warehouse & Storage'                        => TRUE ,
                        'Stock List, Reconciliation, Stock Transfers, Stock Requests' => TRUE ,
                        'Basic Purchases, Supplier Mgt, Purchase Returns'             => TRUE ,
                        'Basic Reports, Expenses, Debt Mgt, Accounting'               => TRUE ,
                        'Efris Intergrations'                                         => TRUE ,
                        'Advanced Reports'                                            => TRUE ,
                        '24/7 support'                                                => TRUE ,
                    ] ,
                ]
                , [
                    'name'        => 'Enterprise' ,
                    'description' => 'For large businesses' ,
                    'base_amount' => 105_000 ,
                    'setup'       => 1_000_000 ,
                    'position'    => 6 ,
                    'popular'     => FALSE ,
                    'type'        => SubscriptionPlanType::Starter ,
                    'features'    => [
                        'Unlimited Sales'                                     => TRUE ,
                        'Unlimited Inventory'                                 => TRUE ,
                        'Unlimited Users'                                     => TRUE ,
                        'All Pro features'                                    => TRUE ,
                        'Production, HR, Asset Mgt, cashflow App'             => TRUE ,
                        'Advanced Reports, Project Mgt, Commissions, Loyalty' => TRUE ,
                        '24/7 support'                                        => TRUE ,
                    ] ,
                ]
                ,
            ];
            foreach ( $plans as $plan ) {
                SubscriptionPlan::updateOrCreate( [ 'name' => $plan[ 'name' ] , 'type' => $plan[ 'type' ] ] , $plan );
            }
        }
    }

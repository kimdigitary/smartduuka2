<?php

    namespace Database\Seeders;

    use App\Models\ModuleCategory;
    use App\Models\ModuleFeature;
    use App\Models\SystemModule;
    use App\Models\SystemModuleFeature;
    use Illuminate\Database\Seeder;

    class SystemModuleSeeder extends Seeder
    {

        public function run() : void
        {
            $mockModules = [
                // --- CORE & SYSTEM ---
                [
                    'name'        => 'Dashboard' ,
                    'description' => 'Central hub for business performance and widgets.' ,
                    'icon'        => 'FaTachometerAlt' ,
                    'category'    => 'Core' ,
                    'enabled'     => TRUE ,
                    'subFeatures' => [
                        [ 'name' => 'Account Balances' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Expenses Chart' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Financial Stats (Receivable/Payable)' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Inventory Overview' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Invoices & Deposits' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Purchases Chart' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Quotation Performance' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Recent Activities' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Top Selling Products' , 'enabled' => TRUE ]
                    ]
                ] ,
                [
                    'name'        => 'Users' ,
                    'description' => 'System administrators and employees.' ,
                    'icon'        => 'FaUsersCog' ,
                    'category'    => 'Core' ,
                    'enabled'     => TRUE ,
                    'subFeatures' => [
                        [ 'name' => 'Administrators' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Employees' , 'enabled' => TRUE ]
                    ]
                ] ,
                [
                    'name'        => 'Modules' ,
                    'description' => 'Feature provisioning and system modules.' ,
                    'icon'        => 'FaPuzzlePiece' ,
                    'category'    => 'System' ,
                    'enabled'     => TRUE
                ] ,
                [
                    'name'        => 'Settings' ,
                    'description' => 'System-wide configuration.' ,
                    'icon'        => 'FaCogs' ,
                    'category'    => 'System' ,
                    'enabled'     => TRUE
                ] ,

                // --- OPERATIONS ---
                [
                    'name'        => 'Sales' ,
                    'description' => 'Manage customers, quotations, and returns.' ,
                    'icon'        => 'FaChartLine' ,
                    'category'    => 'Operations' ,
                    'enabled'     => TRUE ,
                    'subFeatures' => [
                        [ 'name' => 'POS' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Add Sale' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Quotation' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Sales' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Credit Sales' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Deposited Sales' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Pre-Orders' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Sales Returns' , 'enabled' => TRUE ]
                    ]
                ] ,
                [
                    'name'        => 'Customers' ,
                    'description' => 'Client profiles and loyalty programs.' ,
                    'icon'        => 'FaUsers' ,
                    'category'    => 'Operations' ,
                    'enabled'     => TRUE ,
                    'subFeatures' => [
                        [ 'name' => 'Customer List' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Loyalty / Membership' , 'enabled' => TRUE ]
                    ]
                ] ,
                [
                    'name'        => 'Services' ,
                    'description' => 'Service bookings and management.' ,
                    'icon'        => 'FaTools' ,
                    'category'    => 'Operations' ,
                    'enabled'     => FALSE
                ] ,
                [
                    'name'        => 'Inventory' ,
                    'description' => 'Item tracking and variants.' ,
                    'icon'        => 'FaBoxes' ,
                    'category'    => 'Operations' ,
                    'enabled'     => TRUE
                ] ,
                [
                    'name'        => 'Distribution' ,
                    'description' => 'Fleet and delivery routes.' ,
                    'icon'        => 'FaTruck' ,
                    'category'    => 'Operations' ,
                    'enabled'     => FALSE
                ] ,
                [
                    'name'         => 'Stock' ,
                    'description'  => 'Stock counts and adjustments.' ,
                    'icon'         => 'FaLayerGroup' ,
                    'category'     => 'Operations' ,
                    'enabled'      => TRUE ,
                    'dependencies' => [ 'inventory' ]
                ] ,
                [
                    'name'         => 'Warehouse & Storage' ,
                    'description'  => 'Multi-location storage.' ,
                    'icon'         => 'FaWarehouse' ,
                    'category'     => 'Operations' ,
                    'enabled'      => TRUE ,
                    'dependencies' => [ 'inventory' ]
                ] ,
                [
                    'name'         => 'Procurement' ,
                    'description'  => 'Purchase customers and suppliers.' ,
                    'icon'         => 'FaTruckLoading' ,
                    'category'     => 'Operations' ,
                    'enabled'      => TRUE ,
                    'dependencies' => [ 'inventory' ]
                ] ,
                [
                    'name'           => 'Production' ,
                    'description'    => 'Manufacturing and assembly.' ,
                    'icon'           => 'FaIndustry' ,
                    'category'       => 'Operations' ,
                    'enabled'        => FALSE ,
                    'priceIncrement' => 20000 ,
                    'dependencies'   => [ 'inventory' ] ,
                    'subFeatures'    => [
                        [ 'name' => 'Raw Materials' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Machinery' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Production Setup' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Production Processes' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Production Output' , 'enabled' => TRUE ]
                    ]
                ] ,
                [
                    'name'           => 'Projects' ,
                    'description'    => 'Track project milestones and tasks.' ,
                    'icon'           => 'FaTasks' ,
                    'category'       => 'Operations' ,
                    'enabled'        => FALSE ,
                    'priceIncrement' => 20000 ,
                    'subFeatures'    => [
                        [ 'name' => 'Projects List' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Tasks' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Timesheets' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Settings' , 'enabled' => TRUE ]
                    ]
                ] ,

                // --- FINANCE ---
                [
                    'name'         => 'Commission' ,
                    'description'  => 'Sales staff commissions.' ,
                    'icon'         => 'FaPercent' ,
                    'category'     => 'Finance' ,
                    'enabled'      => FALSE ,
                    'dependencies' => [ 'sales' ]
                ] ,
                [
                    'name'        => 'Expenses' ,
                    'description' => 'Operational costs.' ,
                    'icon'        => 'FaFileInvoiceDollar' ,
                    'category'    => 'Finance' ,
                    'enabled'     => TRUE
                ] ,
                [
                    'name'           => 'Asset Management' ,
                    'description'    => 'Equipment tracking and depreciation.' ,
                    'icon'           => 'FaBuilding' ,
                    'category'       => 'Finance' ,
                    'enabled'        => FALSE ,
                    'priceIncrement' => 20000
                ] ,
                [
                    'name'        => 'Accounting' ,
                    'description' => 'General ledger and banking.' ,
                    'icon'        => 'FaBook' ,
                    'category'    => 'Finance' ,
                    'enabled'     => TRUE ,
                    'subFeatures' => [
                        [ 'name' => 'Transactions' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Chart of Accounts' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Journal Entry' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Settings' , 'enabled' => TRUE ]
                    ]
                ] ,

                // --- HR & ADMIN ---
                [
                    'name'        => 'Branches' ,
                    'description' => 'Manage multiple outlets.' ,
                    'icon'        => 'FaStore' ,
                    'category'    => 'HR & Admin' ,
                    'enabled'     => TRUE
                ] ,
                [
                    'name'           => 'HR Mgt' ,
                    'description'    => 'Staff, payroll, and attendance.' ,
                    'icon'           => 'FaUserTie' ,
                    'category'       => 'HR & Admin' ,
                    'enabled'        => TRUE ,
                    'priceIncrement' => 20000 ,
                    'subFeatures'    => [
                        [ 'name' => 'Employee Mgt' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Payroll' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Leave Mgt' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Recruitment' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Performance' , 'enabled' => TRUE ]
                    ]
                ] ,
                [
                    'name'        => 'Reports' ,
                    'description' => 'Deep analytics and data export.' ,
                    'icon'        => 'FaChartPie' ,
                    'category'    => 'HR & Admin' ,
                    'enabled'     => TRUE ,
                    'subFeatures' => [
                        [ 'name' => 'Sales Reports' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Inventory Reports' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Production Reports' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Procurement Reports' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Accounting Reports' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Expenses Reports' , 'enabled' => TRUE ] ,
                        [ 'name' => 'HR Reports' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Product Reports' , 'enabled' => TRUE ]
                    ]
                ] ,
                [
                    'name'        => 'Subscriptions' ,
                    'description' => 'Manage billing plans.' ,
                    'icon'        => 'FaCreditCard' ,
                    'category'    => 'HR & Admin' ,
                    'enabled'     => TRUE
                ] ,
                [
                    'name'        => 'Activity Logs' ,
                    'description' => 'Track system usage and audit trails.' ,
                    'icon'        => 'FaHistory' ,
                    'category'    => 'HR & Admin' ,
                    'enabled'     => TRUE ,
                    'subFeatures' => [
                        [ 'name' => 'Audit Trails' , 'enabled' => TRUE ] ,
                        [ 'name' => 'Transaction Logs' , 'enabled' => TRUE ]
                    ]
                ]
            ];

            foreach ( $mockModules as $moduleData ) {
                $category = ModuleCategory::firstOrCreate( [ 'name' => $moduleData[ 'category' ] ] );

                $module = SystemModule::updateOrCreate(
                    [ 'name' => $moduleData[ 'name' ] ] ,
                    [
                        'description'        => $moduleData[ 'description' ] ,
                        'icon'               => $moduleData[ 'icon' ] ,
                        'module_category_id' => $category->id ,
                        'enabled'            => $moduleData[ 'enabled' ] ,
                    ]
                );

                if ( isset( $moduleData[ 'subFeatures' ] ) ) {
                    foreach ( $moduleData[ 'subFeatures' ] as $featureData ) {
                        $feature = ModuleFeature::updateOrCreate(
                            [ 'name' => $featureData[ 'name' ] ] ,
                            [ 'enabled' => $featureData[ 'enabled' ] ]
                        );
                        SystemModuleFeature::updateOrCreate( [
                            'system_module_id'  => $module->id ,
                            'module_feature_id' => $feature->id ,
                        ] );
                    }
                }
            }
        }
    }

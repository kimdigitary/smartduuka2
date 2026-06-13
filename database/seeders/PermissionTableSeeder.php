<?php

    namespace Database\Seeders;

    use App\Enums\Role as EnumRole;
    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;
    use Spatie\Permission\Models\Permission;
    use Spatie\Permission\Models\Role;
    use Spatie\Permission\PermissionRegistrar;

    class PermissionTableSeeder extends Seeder
    {
        public function run() : void
        {
            $data = [
                [
                    'group' => 'Dashboard' ,
                    'items' => [
                        [ 'title' => 'Dashboard' , 'name' => 'dashboard' , 'url' => '/' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Sales' ,
                    'items' => [
                        [ 'title' => 'POS' , 'name' => 'pos' , 'url' => 'pos' , 'items' => [] ] ,
                        [ 'title' => 'Add Sale' , 'name' => 'add_sale' , 'url' => 'addsale' , 'items' => [] ] ,
                        [
                            'title' => 'Quotation' ,
                            'name'  => 'quotation' ,
                            'url'   => 'quotation' ,
                            'items' => [
                                [ 'title' => 'Quotation Create' , 'name' => 'quotation_create' , 'url' => 'quotation/create' , 'items' => [] ] ,
                                [ 'title' => 'Quotation Edit' , 'name' => 'quotation_edit' , 'url' => 'quotation/edit' , 'items' => [] ] ,
                                [ 'title' => 'Quotation Delete' , 'name' => 'quotation_delete' , 'url' => 'quotation/delete' , 'items' => [] ] ,
                                [ 'title' => 'Quotation Show' , 'name' => 'quotation_show' , 'url' => 'quotation/show' , 'items' => [] ] ,
                            ]
                        ] ,
                        [ 'title' => 'Sales' , 'name' => 'sales' , 'url' => 'salesorders' , 'items' => [] ] ,
                        [ 'title' => 'Credit Sales' , 'name' => 'credit_sales' , 'url' => 'salesorders/credit' , 'items' => [] ] ,
                        [ 'title' => 'Deposited Sales' , 'name' => 'deposited_sales' , 'url' => 'salesorders/deposited' , 'items' => [] ] ,
                        [ 'title' => 'Pre-Orders' , 'name' => 'pre_orders' , 'url' => 'salesorders/preorders' , 'items' => [] ] ,
                        [ 'title' => 'Sales Returns' , 'name' => 'sales_returns' , 'url' => 'salesorders/salesreturns' , 'items' => [] ] ,
                        [ 'title' => 'Register' , 'name' => 'register' , 'url' => 'salesorders/register' , 'items' => [
//                            [ 'title' => 'Register Create' , 'name' => 'register_create' , 'url' => 'register/create' , 'items' => [] ] ,
//                            [ 'title' => 'Register Edit' , 'name' => 'register_edit' , 'url' => 'register/edit' , 'items' => [] ] ,
//                            [ 'title' => 'Register Delete' , 'name' => 'register_delete' , 'url' => 'register/delete' , 'items' => [] ] ,
                            [ 'title' => 'View Register' , 'name' => 'register_show' , 'url' => 'pos/register' , 'items' => [] ] ,
                        ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Commission' ,
                    'items' => [
                        [ 'title' => 'Commission' , 'name' => 'commission' , 'url' => 'commission' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Services' ,
                    'items' => [
                        [
                            'title' => 'Service List' ,
                            'name'  => 'service_list' ,
                            'url'   => 'services' ,
                            'items' => [
                                [ 'title' => 'Service List Create' , 'name' => 'service_list_create' , 'url' => 'services/create' , 'items' => [] ] ,
                                [ 'title' => 'Service List Edit' , 'name' => 'service_list_edit' , 'url' => 'services/edit' , 'items' => [] ] ,
                                [ 'title' => 'Service List Delete' , 'name' => 'service_list_delete' , 'url' => 'services/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Customers' ,
                    'items' => [
                        [
                            'title' => 'Customer List' ,
                            'name'  => 'customer_list' ,
                            'url'   => 'customers' ,
                            'items' => [
                                [ 'title' => 'Customer List Create' , 'name' => 'customer_list_create' , 'url' => 'customers/create' , 'items' => [] ] ,
                                [ 'title' => 'Customer List Edit' , 'name' => 'customer_list_edit' , 'url' => 'customers/edit' , 'items' => [] ] ,
                                [ 'title' => 'Customer List Delete' , 'name' => 'customer_list_delete' , 'url' => 'customers/delete' , 'items' => [] ] ,
                            ]
                        ] ,
                        [ 'title' => 'Loyalty / Membership' , 'name' => 'loyalty_membership' , 'url' => 'customers/loyalty' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Inventory' ,
                    'items' => [
                        [
                            'title' => 'Inventory List' ,
                            'name'  => 'inventory_list' ,
                            'url'   => 'inventory' ,
                            'items' => [
                                [ 'title' => 'Inventory List Create' , 'name' => 'inventory_list_create' , 'url' => 'inventory/create' , 'items' => [] ] ,
                                [ 'title' => 'Inventory List Edit' , 'name' => 'inventory_list_edit' , 'url' => 'inventory/edit' , 'items' => [] ] ,
                                [ 'title' => 'Inventory List Delete' , 'name' => 'inventory_list_delete' , 'url' => 'inventory/delete' , 'items' => [] ] ,
                                [ 'title' => 'Inventory List Show' , 'name' => 'inventory_list_show' , 'url' => 'inventory/show' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Distribution' ,
                    'items' => [
                        [
                            'title' => 'Distribution List' ,
                            'name'  => 'distribution_list' ,
                            'url'   => 'distribution' ,
                            'items' => [
                                [ 'title' => 'Distribution List Create' , 'name' => 'distribution_list_create' , 'url' => 'distribution/create' , 'items' => [] ] ,
                                [ 'title' => 'Distribution List Edit' , 'name' => 'distribution_list_edit' , 'url' => 'distribution/edit' , 'items' => [] ] ,
                                [ 'title' => 'Distribution List Delete' , 'name' => 'distribution_list_delete' , 'url' => 'distribution/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Stock' ,
                    'items' => [
                        [
                            'title' => 'Stock List' ,
                            'name'  => 'stock_list' ,
                            'url'   => 'stock' ,
                            'items' => [
                                [ 'title' => 'Stock List Create' , 'name' => 'stock_list_create' , 'url' => 'stock/create' , 'items' => [] ] ,
                                [ 'title' => 'Stock List Edit' , 'name' => 'stock_list_edit' , 'url' => 'stock/edit' , 'items' => [] ] ,
                                [ 'title' => 'Stock List Delete' , 'name' => 'stock_list_delete' , 'url' => 'stock/delete' , 'items' => [] ] ,
                                [ 'title' => 'Stock List Show' , 'name' => 'stock_list_show' , 'url' => 'stock/show' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Warehouse & Storage' ,
                    'items' => [
                        [
                            'title' => 'Warehouse & Storage List' ,
                            'name'  => 'warehouse_storage_list' ,
                            'url'   => 'warehouses&storage' ,
                            'items' => [
                                [ 'title' => 'Warehouse & Storage List Create' , 'name' => 'warehouse_storage_list_create' , 'url' => 'warehouses&storage/create' , 'items' => [] ] ,
                                [ 'title' => 'Warehouse & Storage List Edit' , 'name' => 'warehouse_storage_list_edit' , 'url' => 'warehouses&storage/edit' , 'items' => [] ] ,
                                [ 'title' => 'Warehouse & Storage List Delete' , 'name' => 'warehouse_storage_list_delete' , 'url' => 'warehouses&storage/delete' , 'items' => [] ] ,
                                [ 'title' => 'Warehouse & Storage List Show' , 'name' => 'warehouse_storage_list_show' , 'url' => 'warehouses&storage/show' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Procurement' ,
                    'items' => [
                        [
                            'title' => 'Procurement List' ,
                            'name'  => 'procurement_list' ,
                            'url'   => 'procurement' ,
                            'items' => [
                                [ 'title' => 'Procurement List Create' , 'name' => 'procurement_list_create' , 'url' => 'procurement/create' , 'items' => [] ] ,
                                [ 'title' => 'Procurement List Edit' , 'name' => 'procurement_list_edit' , 'url' => 'procurement/edit' , 'items' => [] ] ,
                                [ 'title' => 'Procurement List Delete' , 'name' => 'procurement_list_delete' , 'url' => 'procurement/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Production' ,
                    'items' => array_map( fn($item) => [
                        'title' => $item ,
                        'name'  => strtolower( str_replace( ' ' , '_' , $item ) ) ,
                        'url'   => strtolower( str_replace( ' ' , '-' , $item ) ) ,
                        'items' => ( $item !== 'Production Output' ) ? [
                            [ 'title' => "$item Create" , 'name' => strtolower( str_replace( ' ' , '_' , $item ) ) . '_create' , 'url' => strtolower( str_replace( ' ' , '-' , $item ) ) . '/create' , 'items' => [] ] ,
                            [ 'title' => "$item Edit" , 'name' => strtolower( str_replace( ' ' , '_' , $item ) ) . '_edit' , 'url' => strtolower( str_replace( ' ' , '-' , $item ) ) . '/edit' , 'items' => [] ] ,
                            [ 'title' => "$item Delete" , 'name' => strtolower( str_replace( ' ' , '_' , $item ) ) . '_delete' , 'url' => strtolower( str_replace( ' ' , '-' , $item ) ) . '/delete' , 'items' => [] ] ,
                        ] : []
                    ] , [ 'Raw Materials' , 'Machinery' , 'Production Setup' , 'Production Processes' , 'Production Output' ] )
                ] ,
                [
                    'group' => 'Projects' ,
                    'items' => array_map( function ($item) {
                        $name = $item[ 'name' ] ?? strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) );

                        return [
                            'title' => $item[ 'title' ] ,
                            'name'  => $name ,
                            'url'   => $item[ 'url' ] ,
                            'items' => [
                                [ 'title' => "{$item['title']} Create" , 'name' => $name . '_create' , 'url' => "{$item['url']}/create" , 'items' => [] ] ,
                                [ 'title' => "{$item['title']} Edit" , 'name' => $name . '_edit' , 'url' => "{$item['url']}/edit" , 'items' => [] ] ,
                                [ 'title' => "{$item['title']} Delete" , 'name' => $name . '_delete' , 'url' => "{$item['url']}/delete" , 'items' => [] ] ,
                            ]
                        ];
                    } , [
                        [ 'title' => 'Projects List' , 'url' => 'projects/list' ] ,
                        [ 'title' => 'Tasks' , 'url' => 'projects/tasks' ] ,
                        [ 'title' => 'Timesheets' , 'url' => 'projects/timesheets' ] ,
                        [ 'title' => 'Settings' , 'name' => 'project_settings' , 'url' => 'projects/settings' ]
                    ] )
                ] ,
                [
                    'group' => 'Expenses' ,
                    'items' => [
                        [
                            'title' => 'Expenses List' ,
                            'name'  => 'expenses_list' ,
                            'url'   => 'expenses' ,
                            'items' => [
                                [ 'title' => 'Expenses List Create' , 'name' => 'expenses_list_create' , 'url' => 'expenses/create' , 'items' => [] ] ,
                                [ 'title' => 'Expenses List Edit' , 'name' => 'expenses_list_edit' , 'url' => 'expenses/edit' , 'items' => [] ] ,
                                [ 'title' => 'Expenses List Delete' , 'name' => 'expenses_list_delete' , 'url' => 'expenses/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Branches' ,
                    'items' => [
                        [
                            'title' => 'Branch List' ,
                            'name'  => 'branch_list' ,
                            'url'   => 'branches' ,
                            'items' => [
                                [ 'title' => 'Branch List Create' , 'name' => 'branch_list_create' , 'url' => 'branches/create' , 'items' => [] ] ,
                                [ 'title' => 'Branch List Edit' , 'name' => 'branch_list_edit' , 'url' => 'branches/edit' , 'items' => [] ] ,
                                [ 'title' => 'Branch List Delete' , 'name' => 'branch_list_delete' , 'url' => 'branches/delete' , 'items' => [] ] ,
                            ]
                        ]
                    ]
                ] ,
                [
                    'group' => 'Asset Management' ,
                    'items' => [
                        [ 'title' => 'Asset Management' , 'name' => 'asset_management' , 'url' => 'assets' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Accounting' ,
                    'items' => array_map( function ($item) {
                        $name = $item[ 'name' ] ?? strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) );

                        return [
                            'title' => $item[ 'title' ] ,
                            'name'  => $name ,
                            'url'   => $item[ 'url' ] ,
                            'items' => [
                                [ 'title' => "{$item['title']} Create" , 'name' => $name . '_create' , 'url' => "{$item['url']}/create" , 'items' => [] ] ,
                                [ 'title' => "{$item['title']} Edit" , 'name' => $name . '_edit' , 'url' => "{$item['url']}/edit" , 'items' => [] ] ,
                                [ 'title' => "{$item['title']} Delete" , 'name' => $name . '_delete' , 'url' => "{$item['url']}/delete" , 'items' => [] ] ,
                            ]
                        ];
                    } , [
                        [ 'title' => 'Transactions' , 'url' => 'transactions' ] ,
                        [ 'title' => 'Chart of Accounts' , 'url' => 'chart-of-accounts' ] ,
                        [ 'title' => 'Journal Entry' , 'url' => 'journal-entries' ] ,
                        [ 'title' => 'Settings' , 'name' => 'accounting_settings' , 'url' => 'accounting-settings' ]
                    ] )
                ] ,
                [
                    'group' => 'HR Mgt' ,
                    'items' => array_map( fn($item) => [
                        'title' => $item[ 'title' ] ,
                        'name'  => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) ,
                        'url'   => $item[ 'url' ] ,
                        'items' => [
                            [ 'title' => "{$item['title']} Create" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_create' , 'url' => "{$item['url']}/create" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Edit" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_edit' , 'url' => "{$item['url']}/edit" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Delete" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_delete' , 'url' => "{$item['url']}/delete" , 'items' => [] ] ,
                        ]
                    ] , [
                        [ 'title' => 'Employee Mgt' , 'url' => 'hr/employees' ] ,
                        [ 'title' => 'Payroll' , 'url' => 'hr/payroll' ] ,
                        [ 'title' => 'Leave Mgt' , 'url' => 'hr/leave' ] ,
                        [ 'title' => 'Recruitment' , 'url' => 'hr/recruitment' ] ,
                        [ 'title' => 'Performance' , 'url' => 'hr/performance' ]
                    ] )
                ] ,
                [
                    'group' => 'Users' ,
                    'items' => array_map( fn($item) => [
                        'title' => $item[ 'title' ] ,
                        'name'  => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) ,
                        'url'   => $item[ 'url' ] ,
                        'items' => [
                            [ 'title' => "{$item['title']} Create" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_create' , 'url' => "{$item['url']}/create" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Edit" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_edit' , 'url' => "{$item['url']}/edit" , 'items' => [] ] ,
                            [ 'title' => "{$item['title']} Delete" , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) . '_delete' , 'url' => "{$item['url']}/delete" , 'items' => [] ] ,
                        ]
                    ] , [
                        [ 'title' => 'Administrators' , 'url' => 'administrators' ] ,
                        [ 'title' => 'Employees' , 'url' => 'employees' ]
                    ] )
                ] ,
                [
                    'group' => 'Reports' ,
                    'items' => array_map( fn($item) => [ 'title' => $item[ 'title' ] , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) , 'url' => $item[ 'url' ] , 'items' => [] ] , [
                        [ 'title' => 'Sales Reports' , 'url' => 'salesreports' ] ,
                        [ 'title' => 'Register Reports' , 'url' => 'report-register' ] ,
                        [ 'title' => 'Inventory Reports' , 'url' => 'inventoryreports' ] ,
                        [ 'title' => 'Production Reports' , 'url' => 'productionreports' ] ,
                        [ 'title' => 'Procurement Reports' , 'url' => 'procurementreports' ] ,
                        [ 'title' => 'Accounting Reports' , 'url' => 'accountingreports' ] ,
                        [ 'title' => 'Expenses Reports' , 'url' => 'expensesreports' ] ,
                        [ 'title' => 'HR Reports' , 'url' => 'hrreports' ] ,
                        [ 'title' => 'Product Reports' , 'url' => 'productsreports' ]
                    ] )
                ] ,
                [
                    'group' => 'Subscriptions' ,
                    'items' => [
                        [ 'title' => 'Subscriptions' , 'name' => 'subscriptions' , 'url' => 'subscriptions' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Activity Logs' ,
                    'items' => array_map( fn($item) => [ 'title' => $item[ 'title' ] , 'name' => strtolower( str_replace( ' ' , '_' , $item[ 'title' ] ) ) , 'url' => $item[ 'url' ] , 'items' => [] ] , [
                        [ 'title' => 'Audit Trails' , 'url' => 'audit-trails' ] ,
                        [ 'title' => 'Transaction Logs' , 'url' => 'transactions' ]
                    ] )
                ] ,
                [
                    'group' => 'Modules' ,
                    'items' => [
                        [ 'title' => 'Modules' , 'name' => 'modules' , 'url' => 'modules' , 'items' => [] ]
                    ]
                ] ,
                [
                    'group' => 'Settings' ,
                    'items' => [
                        [ 'title' => 'Settings' , 'name' => 'settings' , 'url' => 'settings' , 'items' => [] ]
                    ]
                ]
            ];

            $permissions = [];
            $now         = now();

            // First, process all parent/group permissions
            foreach ( $data as $group ) {
                $groupName = Str::slug( $group[ 'group' ] , '_' );
                $groupUrl  = Str::slug( $group[ 'group' ] , '-' );

                $parentPermission = [
                    'title'      => $group[ 'group' ] ,
                    'name'       => $groupName ,
                    'guard_name' => 'sanctum' ,
                    'url'        => $groupUrl ,
                    'created_at' => $now ,
                    'updated_at' => $now ,
                    'children'   => []
                ];

                foreach ( $group[ 'items' ] as $item ) {
                    // Prepare item structure for children
                    $itemNode = [
                        'title'      => $item[ 'title' ] ,
                        'name'       => $item[ 'name' ] ,
                        'guard_name' => 'sanctum' ,
                        'url'        => $item[ 'url' ] ,
                        'created_at' => $now ,
                        'updated_at' => $now ,
                    ];

                    // If this item has deep CRUD items (recursive items)
                    if ( ! empty( $item[ 'items' ] ) ) {
                        $itemNode[ 'children' ] = array_map( function ($subItem) use ($now) {
                            return [
                                'title'      => $subItem[ 'title' ] ,
                                'name'       => $subItem[ 'name' ] ,
                                'guard_name' => 'sanctum' ,
                                'url'        => $subItem[ 'url' ] ,
                                'created_at' => $now ,
                                'updated_at' => $now ,
                            ];
                        } , $item[ 'items' ] );
                    }

                    $parentPermission[ 'children' ][] = $itemNode;
                }

                $permissions[] = $parentPermission;
            }

//            $flattenedPermissions = AppLibrary::recursiveFlattenPermissions( $permissions );
//
//            foreach ( $flattenedPermissions as $perm ) {
//                Permission::firstOrCreate(
//                    [ 'name' => $perm[ 'name' ] , 'guard_name' => $perm[ 'guard_name' ] ] ,
//                    $perm
//                );
//            }
//
//            $adminRole = Role::where( 'name' , EnumRole::ADMIN )->first();
//            if ( $adminRole ) {
//                $allPermissions = Permission::all();
//                $adminRole->syncPermissions( $allPermissions );
//            }

            app( PermissionRegistrar::class )->forgetCachedPermissions();

            if ( DB::connection()->getDriverName() === 'pgsql' ) {
                DB::statement( "
                    SELECT setval(
                        pg_get_serial_sequence('permissions', 'id'),
                        COALESCE((SELECT MAX(id) FROM permissions), 1),
                        (SELECT COUNT(*) > 0 FROM permissions)
                    )
                " );
            }

            $savedPermissionNames = [];
            $savedPermissions     = [];

            $savePermissions = function (array $nodes , int $parentId = 0) use (&$savePermissions , &$savedPermissionNames , &$savedPermissions) : void {
                foreach ( $nodes as $node ) {
                    $children = $node[ 'children' ] ?? [];
                    $permissionKey = $node[ 'guard_name' ] . ':' . $node[ 'name' ];

                    if ( isset( $savedPermissions[ $permissionKey ] ) ) {
                        $permission = $savedPermissions[ $permissionKey ];
                    }
                    else {
                        $permission = Permission::updateOrCreate(
                            [ 'name' => $node[ 'name' ] , 'guard_name' => $node[ 'guard_name' ] ] ,
                            [
                                'title'  => $node[ 'title' ] ,
                                'url'    => $node[ 'url' ] ,
                                'parent' => $parentId ,
                            ]
                        );

                        $savedPermissions[ $permissionKey ] = $permission;
                    }

                    $savedPermissionNames[] = $permission->name;

                    if ( ! empty( $children ) ) {
                        $savePermissions( $children , $permission->id );
                    }
                }
            };

            $savePermissions( $permissions );

            Permission::whereIn( 'name' , [
                'service_create' ,
                'service_edit' ,
                'service_delete' ,
                'inventory_create' ,
                'inventory_edit' ,
                'inventory_delete' ,
                'inventory_show' ,
                'distribution_create' ,
                'distribution_edit' ,
                'distribution_delete' ,
                'stock_create' ,
                'stock_edit' ,
                'stock_delete' ,
                'stock_show' ,
                'warehouse_storage_create' ,
                'warehouse_storage_edit' ,
                'warehouse_storage_delete' ,
                'warehouse_storage_show' ,
                'procurement_create' ,
                'procurement_edit' ,
                'procurement_delete' ,
                'expenses_create' ,
                'expenses_edit' ,
                'expenses_delete' ,
                'branch_create' ,
                'branch_edit' ,
                'branch_delete' ,
                'settings_create' ,
                'settings_edit' ,
                'settings_delete' ,
            ] )->delete();

            app( PermissionRegistrar::class )->forgetCachedPermissions();

            $adminRole = Role::where( 'name' , EnumRole::ADMIN )->first();
            if ( $adminRole ) {
                $adminRole->givePermissionTo( $savedPermissionNames );
            }
        }
    }

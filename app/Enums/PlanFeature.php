<?php

    namespace App\Enums;

    enum PlanFeature : string
    {
        // Sales
        case SALES_300       = 'Max 300 sales / mo';
        case SALES_2000      = 'Max 2,000 sales / mo';
        case SALES_UNLIMITED = 'Unlimited Sales';

        // Inventory
        case ITEMS_100       = 'Max 100 items (No variations)';
        case ITEMS_500       = 'Max 500 items (With variations)';
        case ITEMS_UNLIMITED = 'Unlimited Inventory';
        case BASIC_INVENTORY = 'Basic Inventory';

        // Users
        case USERS_3         = 'Up to 3 Users';
        case USERS_5         = 'Up to 5 Users';
        case USERS_UNLIMITED = 'Unlimited Users';

        // Core modules
        case DASHBOARD       = 'Dashboard, Sales, Customers';
        case STOCK           = 'Stock List, Reconciliation';
        case BASIC_PURCHASES = 'Basic Purchases';
        case BASIC_REPORTS   = 'Basic Reports, Expenses, Debt Mgt';
        case BASIC_SALES_RPT = 'Basic Sales reports';
        case SUPPORT_24_7    = '24/7 support';

        // Pro modules
        case WAREHOUSE        = 'Basic Inventory, Warehouse & Storage';
        case STOCK_TRANSFERS  = 'Stock List, Reconciliation, Stock Transfers, Stock Requests';
        case SUPPLIER_MGT     = 'Basic Purchases, Supplier Mgt, Purchase Returns';
        case ACCOUNTING       = 'Basic Reports, Expenses, Debt Mgt, Accounting';
        case EFRIS            = 'Efris Intergrations';
        case ADVANCED_REPORTS = 'Advanced Reports';

        // Enterprise modules
        case PRODUCTION  = 'Production, HR, Asset Mgt, cashflow App';
        case PROJECT_MGT = 'Advanced Reports, Project Mgt, Commissions, Loyalty';
        case ALL_PRO     = 'All Pro features';
    }
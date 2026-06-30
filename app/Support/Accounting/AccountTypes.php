<?php

    namespace App\Support\Accounting;

    /**
     * The 18 eloquent-ifrs account types (string values match IFRS\Models\Account
     * constants verbatim, and the frontend AccountType enum) plus the reporting
     * metadata the reports need (group, financial statement, normal balance).
     */
    final class AccountTypes
    {
        public const ALL = [
            // Assets
            'NON_CURRENT_ASSET', 'CONTRA_ASSET', 'INVENTORY', 'BANK', 'CURRENT_ASSET', 'RECEIVABLE',
            // Liabilities
            'NON_CURRENT_LIABILITY', 'CONTROL', 'CURRENT_LIABILITY', 'PAYABLE',
            // Equity
            'EQUITY', 'RECONCILIATION',
            // Income
            'OPERATING_REVENUE', 'NON_OPERATING_REVENUE',
            // Expenses
            'OPERATING_EXPENSE', 'DIRECT_EXPENSE', 'OVERHEAD_EXPENSE', 'OTHER_EXPENSE',
        ];

        /** group => [types] */
        public const GROUPS = [
            'ASSETS'      => [ 'NON_CURRENT_ASSET', 'CONTRA_ASSET', 'INVENTORY', 'BANK', 'CURRENT_ASSET', 'RECEIVABLE' ],
            'LIABILITIES' => [ 'NON_CURRENT_LIABILITY', 'CONTROL', 'CURRENT_LIABILITY', 'PAYABLE' ],
            'EQUITY'      => [ 'EQUITY', 'RECONCILIATION' ],
            'INCOME'      => [ 'OPERATING_REVENUE', 'NON_OPERATING_REVENUE' ],
            'EXPENSE'     => [ 'OPERATING_EXPENSE', 'DIRECT_EXPENSE', 'OVERHEAD_EXPENSE', 'OTHER_EXPENSE' ],
        ];

        /** Types whose normal balance sits on the credit side. */
        private const NORMAL_CREDIT = [
            'CONTRA_ASSET', 'NON_CURRENT_LIABILITY', 'CONTROL', 'CURRENT_LIABILITY', 'PAYABLE',
            'EQUITY', 'RECONCILIATION', 'OPERATING_REVENUE', 'NON_OPERATING_REVENUE',
        ];

        public static function group(string $type) : string
        {
            foreach ( self::GROUPS as $group => $types ) {
                if ( in_array( $type, $types, TRUE ) ) {
                    return $group;
                }
            }

            return 'ASSETS';
        }

        public static function statement(string $type) : string
        {
            return in_array( self::group( $type ), [ 'INCOME', 'EXPENSE' ], TRUE )
                ? 'INCOME_STATEMENT'
                : 'BALANCE_SHEET';
        }

        /** 'D' or 'C' — the side the account's balance normally sits on. */
        public static function normalBalance(string $type) : string
        {
            return in_array( $type, self::NORMAL_CREDIT, TRUE ) ? 'C' : 'D';
        }
    }

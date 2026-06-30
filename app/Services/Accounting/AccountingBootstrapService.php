<?php

    namespace App\Services\Accounting;

    use App\Models\User;
    use IFRS\Models\Account;
    use IFRS\Models\Category;
    use IFRS\Models\Currency;
    use IFRS\Models\Entity;
    use IFRS\Models\ReportingPeriod;
    use IFRS\Models\Vat;
    use Illuminate\Support\Facades\Auth;
    use Smartisan\Settings\Facades\Settings;

    /**
     * Ensures the CURRENT tenant has a usable eloquent-ifrs accounting setup:
     *   - one Entity shared by every user in the tenant,
     *   - a reporting Currency on that Entity,
     *   - an open ReportingPeriod for the current calendar year.
     *
     * Idempotent: safe to run repeatedly (on tenant seed and as a backfill
     * command for existing tenants).
     */
    class AccountingBootstrapService
    {
        public function bootstrap() : Entity
        {
            $entity = $this->ensureEntity();
            $this->actAsEntityUser( $entity );   // so IFRS models scope to this entity
            $this->ensureReportingCurrency( $entity );
            $this->ensureCurrentPeriod( $entity );
            $this->ensureChartOfAccounts( $entity );

            return $entity->refresh();
        }

        private function ensureEntity() : Entity
        {
            // Reuse the tenant's existing entity if one is already present.
            $entity = Entity::query()->first();

            if ( ! $entity ) {
                $name = Settings::group( 'company' )->get( 'company_name' ) ?: 'My Business';

                $entity                 = new Entity();
                $entity->name           = $name;
                $entity->multi_currency = FALSE;
                $entity->year_start     = 1; // January; fiscal calendar is configurable later
                $entity->save();
            }

            $this->shareEntityWithUsers( $entity );

            return $entity;
        }

        /** Every user in the tenant operates under the same business entity. */
        private function shareEntityWithUsers(Entity $entity) : void
        {
            User::query()->whereNull( 'entity_id' )->update( [ 'entity_id' => $entity->id ] );
        }

        /** Authenticate as a user of this entity so IFRS segregation resolves. */
        private function actAsEntityUser(Entity $entity) : void
        {
            if ( Auth::check() && (int) Auth::user()->entity_id === (int) $entity->id ) {
                return;
            }

            $user = User::query()->where( 'entity_id', $entity->id )->first();
            if ( $user ) {
                Auth::setUser( $user );
            }
        }

        private function ensureReportingCurrency(Entity $entity) : void
        {
            if ( $entity->currency_id ) {
                return;
            }

            [ $code, $label ] = $this->reportingCurrency();

            $currency = Currency::create( [
                'currency_code' => $code,
                'name'          => $label,
                'entity_id'     => $entity->id,
            ] );

            $entity->currency_id = $currency->id;
            $entity->save();
        }

        private function ensureCurrentPeriod(Entity $entity) : void
        {
            ReportingPeriod::firstOrCreate(
                [
                    'entity_id'     => $entity->id,
                    'calendar_year' => (int) date( 'Y' ),
                ],
                [
                    'period_count' => 1,
                    'status'       => ReportingPeriod::OPEN,
                ],
            );
        }

        /**
         * Seed a sensible default chart of accounts (categories + accounts), wire
         * the operational default-accounts map, and create default taxes. Runs once
         * per tenant (skipped when accounts already exist).
         */
        private function ensureChartOfAccounts(Entity $entity) : void
        {
            if ( Account::query()->exists() ) {
                return;
            }

            $categories = [];
            foreach ( $this->categoryDefinitions() as $handle => [ $name, $type ] ) {
                $category                = new Category();
                $category->name          = $name;
                $category->category_type = $type;
                $category->save();
                $categories[ $handle ] = $category->id;
            }

            $byCode = [];
            foreach ( $this->accountDefinitions() as $def ) {
                $account                = new Account();
                $account->name          = $def[ 'name' ];
                $account->account_type  = $def[ 'type' ];
                $account->code          = $def[ 'code' ];
                $account->category_id   = isset( $def[ 'cat' ] ) ? ( $categories[ $def[ 'cat' ] ] ?? NULL ) : NULL;
                $account->currency_id   = $entity->currency_id;
                $account->is_active     = TRUE;
                $account->is_petty_cash = $def[ 'petty' ] ?? FALSE;
                $account->save();
                $byCode[ $def[ 'code' ] ] = $account->id;
            }

            // Operational posting map: key => real account id.
            $map = [];
            foreach ( $this->defaultAccountCodes() as $key => $code ) {
                if ( isset( $byCode[ $code ] ) ) {
                    $map[ $key ] = $byCode[ $code ];
                }
            }
            Settings::group( 'accounting' )->set( [ 'default_accounts' => $map ] );

            // Default taxes (VAT + withholding).
            foreach ( $this->vatDefinitions() as $v ) {
                $vat              = new Vat();
                $vat->name        = $v[ 'name' ];
                $vat->code        = $v[ 'code' ];
                $vat->rate        = $v[ 'rate' ];
                $vat->account_id  = isset( $v[ 'acc' ] ) ? ( $byCode[ $v[ 'acc' ] ] ?? NULL ) : NULL;
                $vat->tax_type    = $v[ 'taxType' ];
                $vat->is_compound = FALSE;
                $vat->save();
            }
        }

        /** @return array<string, array{0:string,1:string}> handle => [name, type] */
        private function categoryDefinitions() : array
        {
            return [
                'bank'     => [ 'Cash & Bank', 'BANK' ],
                'ar'       => [ 'Trade Debtors', 'RECEIVABLE' ],
                'stock'    => [ 'Stock on Hand', 'INVENTORY' ],
                'fixed'    => [ 'Property & Equipment', 'NON_CURRENT_ASSET' ],
                'ap'       => [ 'Trade Creditors', 'PAYABLE' ],
                'tax'      => [ 'Tax Control', 'CONTROL' ],
                'capital'  => [ 'Capital & Reserves', 'EQUITY' ],
                'sales'    => [ 'Sales Revenue', 'OPERATING_REVENUE' ],
                'cogs'     => [ 'Cost of Sales', 'DIRECT_EXPENSE' ],
                'opex'     => [ 'Operating Expenses', 'OPERATING_EXPENSE' ],
                'overhead' => [ 'Overheads', 'OVERHEAD_EXPENSE' ],
            ];
        }

        /** @return array<int, array<string,mixed>> */
        private function accountDefinitions() : array
        {
            return [
                [ 'code' => '1010', 'name' => 'Cash Register 1', 'type' => 'BANK', 'cat' => 'bank' ],
                [ 'code' => '1020', 'name' => 'Business Savings', 'type' => 'BANK', 'cat' => 'bank' ],
                [ 'code' => '1015', 'name' => 'Petty Cash', 'type' => 'BANK', 'cat' => 'bank', 'petty' => TRUE ],
                [ 'code' => '1400', 'name' => 'Accounts Receivable', 'type' => 'RECEIVABLE', 'cat' => 'ar' ],
                [ 'code' => '1210', 'name' => 'Inventory', 'type' => 'INVENTORY', 'cat' => 'stock' ],
                [ 'code' => '1230', 'name' => 'Raw Materials Inventory', 'type' => 'INVENTORY', 'cat' => 'stock' ],
                [ 'code' => '1240', 'name' => 'Finished Goods Inventory', 'type' => 'INVENTORY', 'cat' => 'stock' ],
                [ 'code' => '1310', 'name' => 'Prepaid Expenses', 'type' => 'CURRENT_ASSET' ],
                [ 'code' => '1510', 'name' => 'Property, Plant & Equipment', 'type' => 'NON_CURRENT_ASSET', 'cat' => 'fixed' ],
                [ 'code' => '1520', 'name' => 'Accumulated Depreciation', 'type' => 'CONTRA_ASSET', 'cat' => 'fixed' ],
                [ 'code' => '2110', 'name' => 'Accounts Payable', 'type' => 'PAYABLE', 'cat' => 'ap' ],
                [ 'code' => '2210', 'name' => 'VAT Output Tax', 'type' => 'CONTROL', 'cat' => 'tax' ],
                [ 'code' => '2220', 'name' => 'VAT Input Tax', 'type' => 'CONTROL', 'cat' => 'tax' ],
                [ 'code' => '2230', 'name' => 'Withholding Tax Payable', 'type' => 'CONTROL', 'cat' => 'tax' ],
                [ 'code' => '2330', 'name' => 'Accrued Liabilities', 'type' => 'CURRENT_LIABILITY' ],
                [ 'code' => '2310', 'name' => 'Customer Wallets', 'type' => 'CURRENT_LIABILITY' ],
                [ 'code' => '2320', 'name' => 'Loyalty Points Liability', 'type' => 'CURRENT_LIABILITY' ],
                [ 'code' => '2510', 'name' => 'Bank Loan', 'type' => 'NON_CURRENT_LIABILITY' ],
                [ 'code' => '3010', 'name' => "Owner's Capital", 'type' => 'EQUITY', 'cat' => 'capital' ],
                [ 'code' => '3020', 'name' => 'Retained Earnings', 'type' => 'EQUITY', 'cat' => 'capital' ],
                [ 'code' => '4110', 'name' => 'Retail Sales', 'type' => 'OPERATING_REVENUE', 'cat' => 'sales' ],
                [ 'code' => '4120', 'name' => 'Wholesale Sales', 'type' => 'OPERATING_REVENUE', 'cat' => 'sales' ],
                [ 'code' => '4130', 'name' => 'Service Revenue', 'type' => 'OPERATING_REVENUE', 'cat' => 'sales' ],
                [ 'code' => '4810', 'name' => 'Stock Count Surplus', 'type' => 'NON_OPERATING_REVENUE' ],
                [ 'code' => '4820', 'name' => 'Gain/Loss on Asset Disposal', 'type' => 'NON_OPERATING_REVENUE' ],
                [ 'code' => '4830', 'name' => 'FX Gain/Loss', 'type' => 'NON_OPERATING_REVENUE' ],
                [ 'code' => '5010', 'name' => 'Cost of Goods Sold', 'type' => 'DIRECT_EXPENSE', 'cat' => 'cogs' ],
                [ 'code' => '5110', 'name' => 'Staff Salaries', 'type' => 'OPERATING_EXPENSE', 'cat' => 'opex' ],
                [ 'code' => '5120', 'name' => 'Sales Commissions', 'type' => 'OPERATING_EXPENSE', 'cat' => 'opex' ],
                [ 'code' => '5130', 'name' => 'Production Overhead', 'type' => 'OPERATING_EXPENSE', 'cat' => 'opex' ],
                [ 'code' => '5140', 'name' => 'Loyalty & Promotions', 'type' => 'OPERATING_EXPENSE', 'cat' => 'opex' ],
                [ 'code' => '5210', 'name' => 'Rent', 'type' => 'OVERHEAD_EXPENSE', 'cat' => 'overhead' ],
                [ 'code' => '5220', 'name' => 'Electricity & Water', 'type' => 'OVERHEAD_EXPENSE', 'cat' => 'overhead' ],
                [ 'code' => '5230', 'name' => 'Depreciation Expense', 'type' => 'OVERHEAD_EXPENSE', 'cat' => 'overhead' ],
                [ 'code' => '5310', 'name' => 'Inventory Loss & Write-offs', 'type' => 'OTHER_EXPENSE' ],
                [ 'code' => '5320', 'name' => 'Loan Interest Expense', 'type' => 'OTHER_EXPENSE' ],
            ];
        }

        /** @return array<string,string> default-account key => account code */
        private function defaultAccountCodes() : array
        {
            return [
                'bank'                    => '1010',
                'salesRevenue'            => '4110',
                'cogs'                    => '5010',
                'inventory'               => '1210',
                'rawMaterials'            => '1230',
                'finishedGoods'           => '1240',
                'receivable'              => '1400',
                'payable'                 => '2110',
                'vatOutput'               => '2210',
                'vatInput'                => '2220',
                'salaries'                => '5110',
                'commissions'             => '5120',
                'generalExpense'          => '5210',
                'inventoryLoss'           => '5310',
                'inventoryGain'           => '4810',
                'productionOverhead'      => '5130',
                'fixedAsset'              => '1510',
                'serviceRevenue'          => '4130',
                'customerWallet'          => '2310',
                'loyaltyLiability'        => '2320',
                'loyaltyExpense'          => '5140',
                'retainedEarnings'        => '3020',
                'depreciationExpense'     => '5230',
                'accumulatedDepreciation' => '1520',
                'disposalGainLoss'        => '4820',
                'loanInterestExpense'     => '5320',
                'fxGainLoss'              => '4830',
                'withholdingPayable'      => '2230',
                'accruedLiability'        => '2330',
                'prepaidExpense'          => '1310',
                'pettyCash'               => '1015',
            ];
        }

        /** @return array<int, array<string,mixed>> */
        private function vatDefinitions() : array
        {
            return [
                [ 'name' => 'VAT (Standard)', 'code' => 'V', 'rate' => 18, 'acc' => '2210', 'taxType' => 'VAT' ],
                [ 'name' => 'Zero Rated', 'code' => 'Z', 'rate' => 0, 'taxType' => 'VAT' ],
                [ 'name' => 'Exempt', 'code' => 'E', 'rate' => 0, 'taxType' => 'VAT' ],
                [ 'name' => 'Withholding Tax (6%)', 'code' => 'WHT', 'rate' => 6, 'acc' => '2230', 'taxType' => 'WITHHOLDING' ],
            ];
        }

        /** @return array{0:string,1:string} [currency_code, name] */
        private function reportingCurrency() : array
        {
            $code  = strtoupper( (string) ( Settings::group( 'company' )->get( 'currency_code' ) ?: 'UGX' ) );
            $names = [
                'UGX' => 'Ugandan Shilling',
                'KES' => 'Kenyan Shilling',
                'TZS' => 'Tanzanian Shilling',
                'RWF' => 'Rwandan Franc',
                'USD' => 'US Dollar',
            ];

            return [ $code, $names[ $code ] ?? $code ];
        }
    }

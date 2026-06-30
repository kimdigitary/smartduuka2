<?php

    namespace App\Console\Commands;

    use App\Services\Accounting\AccountingBootstrapService;
    use Illuminate\Console\Command;

    /**
     * Backfill the IFRS accounting setup for the current tenant.
     * Run across all tenants with:  php artisan tenants:run "accounting:bootstrap"
     */
    class AccountingBootstrap extends Command
    {
        protected $signature   = 'accounting:bootstrap';
        protected $description = 'Ensure the current tenant has an IFRS entity, reporting currency and an open reporting period.';

        public function handle(AccountingBootstrapService $service) : int
        {
            $entity = $service->bootstrap();
            $this->info( "Accounting bootstrapped for entity #{$entity->id} ({$entity->name})." );

            return self::SUCCESS;
        }
    }

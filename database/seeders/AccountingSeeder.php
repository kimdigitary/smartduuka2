<?php

    namespace Database\Seeders;

    use App\Services\Accounting\AccountingBootstrapService;
    use Illuminate\Database\Seeder;

    /**
     * Bootstraps the IFRS accounting setup for a tenant. Runs after the user
     * seeder so there is a user to share the entity with / authenticate as.
     */
    class AccountingSeeder extends Seeder
    {
        public function run() : void
        {
            app( AccountingBootstrapService::class )->bootstrap();
        }
    }

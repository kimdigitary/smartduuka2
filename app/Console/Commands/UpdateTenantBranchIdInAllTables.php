<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTenantBranchIdInAllTables extends Command
{
    protected $signature = 'tenant:update-branch-id
            {tenant_id : The tenant id to update}
            {branch_id : The branch_id value to set}
            {--dry-run : Show what would be changed without updating data}';

    protected $description = 'Update branch_id in all tenant tables that have or should have the column';

    public function handle(): int
    {
        $tenantId = (string)$this->argument('tenant_id');
        $branchId = (int)$this->argument('branch_id');

        if ($branchId < 1) {
            $this->error('The branch_id must be a positive integer.');

            return self::FAILURE;
        }

        $dryRun = (bool)$this->option('dry-run');

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant [{$tenantId}] was not found.");

            return self::FAILURE;
        }

        return (int)$tenant->run(fn() => $this->updateTenantTables($branchId, $dryRun));
    }

    private function updateTenantTables(int $branchId, bool $dryRun): int
    {
        $excludedTables = [
            'branches',
            'migrations',
            'failed_jobs',
            'password_resets',
            'password_reset_tokens',
            'personal_access_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'tenant_subscriptions',
        ];

        $tables = array_map(
            fn($table) => $table->tablename,
            DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'")
        );

        $this->info('Found ' . count($tables) . ' tables in the tenant database.');

        foreach ($tables as $table) {
            if (in_array($table, $excludedTables, TRUE)) {
                $this->line("<fg=yellow>Skipping excluded table:</> {$table}");
                continue;
            }

            if (!Schema::hasColumn($table, 'branch_id')) {
                if ($dryRun) {
                    $this->line("<fg=cyan>Would add branch_id to:</> {$table}");
                    $this->line("<fg=cyan>Would update:</> {$table} (" . DB::table($table)->count() . " rows)");
                    continue;
                }

                Schema::table($table, function (Blueprint $tableBlueprint) {
                    $tableBlueprint->unsignedBigInteger('branch_id')->nullable();
                });

                $this->info("Added branch_id to: {$table}");
            }

            if ($dryRun) {
                $this->line("<fg=cyan>Would update:</> {$table} (" . DB::table($table)->count() . " rows)");
                continue;
            }

            $updated = DB::table($table)->update(['branch_id' => $branchId]);

            $this->line("<fg=green>Updated:</> {$table} ({$updated} rows)");
        }

        $this->info($dryRun ? "Dry run complete for branch_id {$branchId}." : "Updated tenant tables to branch_id {$branchId}.");

        return self::SUCCESS;
    }
}

<?php

use App\Models\TenantBranch;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Schema::dropIfExists('tenant_branches');
    Schema::dropIfExists('tenants');

    Schema::create('tenants', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->timestamps();
        $table->json('data')->nullable();
    });

    Schema::create('tenant_branches', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable()->unique('tenant_branches_email_unique');
        $table->string('tenant_id');
        $table->string('website')->nullable()->unique('tenant_branches_website_unique');
        $table->string('zip_code')->nullable();
        $table->unsignedSmallInteger('country')->nullable();
        $table->unsignedSmallInteger('city')->nullable();
        $table->unsignedSmallInteger('state')->nullable();
        $table->string('address')->nullable();
        $table->string('phone')->nullable();
        $table->string('phone2')->nullable()->unique('tenant_branches_phone2_unique');
        $table->string('code')->nullable();
        $table->boolean('can_delete')->default(true);
        $table->unsignedTinyInteger('status')->default(5);
        $table->timestamps();
        $table->boolean('share_customers')->default(true);
        $table->boolean('share_wallets')->default(true);
        $table->boolean('share_loyalty')->default(true);
        $table->boolean('share_accounting')->default(true);
        $table->boolean('share_reports')->default(true);
        $table->boolean('share_procurement')->default(true);
        $table->unsignedBigInteger('branch_id')->default(1);

        $table->unique(['tenant_id', 'name'], 'tenant_branches_tenant_id_name_unique');
    });

    $migration = require database_path('migrations/2026_06_22_173942_scope_tenant_branch_contact_uniques_to_tenant.php');
    $migration->up();

    DB::table('tenants')->insert([
        ['id' => 'tenant-one', 'created_at' => now(), 'updated_at' => now()],
        ['id' => 'tenant-two', 'created_at' => now(), 'updated_at' => now()],
    ]);
});

afterEach(function () {
    Schema::dropIfExists('tenant_branches');
    Schema::dropIfExists('tenants');
});

test('tenant branch contact fields are unique per tenant', function (string $column, string $value) {
    TenantBranch::create(tenantBranchContactAttributes('tenant-one', 'Branch One', [
        $column => $value,
    ]));

    TenantBranch::create(tenantBranchContactAttributes('tenant-two', 'Branch Two', [
        $column => $value,
    ]));

    expect(TenantBranch::where($column, $value)->count())->toBe(2)
        ->and(fn() => TenantBranch::create(tenantBranchContactAttributes('tenant-one', 'Branch Three', [
            $column => $value,
        ])))->toThrow(QueryException::class);

})->with([
    'email'   => ['email', 'shared@example.com'],
    'phone'   => ['phone', '+256700000001'],
    'phone2'  => ['phone2', '+256700000002'],
    'website' => ['website', 'https://shared.example.com'],
]);

function tenantBranchContactAttributes(string $tenantId, string $name, array $overrides = []): array
{
    $suffix = strtolower(str_replace([' ', '.'], '-', $tenantId . '-' . $name));

    return array_merge([
        'name'              => $name,
        'email'             => $suffix . '@example.com',
        'tenant_id'         => $tenantId,
        'website'           => 'https://' . $suffix . '.example.com',
        'zip_code'          => '00000',
        'country'           => 1,
        'city'              => 1,
        'state'             => 1,
        'address'           => $name . ' Address',
        'phone'             => '+2567' . str_pad((string)crc32($suffix), 8, '0', STR_PAD_LEFT),
        'phone2'            => '+2568' . str_pad((string)crc32('secondary-' . $suffix), 8, '0', STR_PAD_LEFT),
        'status'            => 5,
        'share_customers'   => true,
        'share_wallets'     => true,
        'share_loyalty'     => true,
        'share_accounting'  => true,
        'share_reports'     => true,
        'share_procurement' => true,
    ], $overrides);
}

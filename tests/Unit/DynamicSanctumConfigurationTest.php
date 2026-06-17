<?php

use App\Http\Middleware\DynamicSanctumConfiguration;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

uses(TestCase::class);

function runDynamicSanctumConfiguration(Request $request): void
{
    app(DynamicSanctumConfiguration::class)->handle(
        $request,
        fn (): Response => response()->noContent()
    );
}

afterEach(function () {
    tenancy()->initialized = false;
});

test('tenant bearer token requests use tenant users and skip session guards', function () {
    tenancy()->initialized = true;

    runDynamicSanctumConfiguration(Request::create(
        '/api/admin/pos/register-details',
        'GET',
        server: ['HTTP_AUTHORIZATION' => 'Bearer 1|tenant-token']
    ));

    expect(config('auth.guards.sanctum.provider'))->toBe('users')
        ->and(config('sanctum.guard'))->toBe([]);
});

test('tenant session requests keep the web guard fallback', function () {
    tenancy()->initialized = true;

    runDynamicSanctumConfiguration(Request::create('/api/admin/pos/register-details'));

    expect(config('auth.guards.sanctum.provider'))->toBe('users')
        ->and(config('sanctum.guard'))->toBe(['web']);
});

test('central bearer token requests use central users and skip session guards', function () {
    tenancy()->initialized = false;

    runDynamicSanctumConfiguration(Request::create(
        '/api/user',
        'GET',
        server: ['HTTP_AUTHORIZATION' => 'Bearer 1|central-token']
    ));

    expect(config('auth.guards.sanctum.provider'))->toBe('central_users')
        ->and(config('sanctum.guard'))->toBe([]);
});

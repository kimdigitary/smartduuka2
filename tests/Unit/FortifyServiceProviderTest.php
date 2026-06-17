<?php

use App\Providers\FortifyServiceProvider;
use Illuminate\Http\Request;

function callFortifyProviderMethod(string $method, mixed ...$arguments): mixed
{
    $provider = new FortifyServiceProvider(app());
    $reflectionMethod = new ReflectionMethod($provider, $method);

    return $reflectionMethod->invoke($provider, ...$arguments);
}

test('it reads tenant ids as scalar strings', function () {
    $request = Request::create('/login', 'POST', [
        'tenant_id' => 'tenant-one',
    ]);

    expect(callFortifyProviderMethod('requestedTenantId', $request))->toBe('tenant-one');
});

test('it distinguishes reserved central apps from tenant ids', function () {
    expect(callFortifyProviderMethod('isCentralApp', 'admin'))->toBeTrue()
        ->and(callFortifyProviderMethod('isCentralApp', 'tenant-one'))->toBeFalse()
        ->and(callFortifyProviderMethod('isCentralApp', null))->toBeFalse();
});

test('it can read login values from email or phone input', function () {
    $emailRequest = Request::create('/login', 'POST', [
        'email' => 'user@example.com',
    ]);
    $phoneRequest = Request::create('/login', 'POST', [
        'phone' => '+256700000000',
    ]);

    expect(callFortifyProviderMethod('loginValue', $emailRequest))->toBe('user@example.com')
        ->and(callFortifyProviderMethod('loginValue', $phoneRequest))->toBe('+256700000000');
});

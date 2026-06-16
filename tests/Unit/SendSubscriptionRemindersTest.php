<?php

use App\Console\Commands\SendSubscriptionReminders;
use App\Enums\Role as RoleEnum;
use App\Jobs\SendEmailsJob;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Schema::dropIfExists('model_has_roles');
    Schema::dropIfExists('roles');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->uuid('global_id')->nullable();
        $table->timestamps();
    });

    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('guard_name');
        $table->timestamps();
    });

    Schema::create('model_has_roles', function (Blueprint $table) {
        $table->unsignedBigInteger('role_id');
        $table->string('model_type');
        $table->unsignedBigInteger('model_id');
    });

    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

afterEach(function () {
    Schema::dropIfExists('model_has_roles');
    Schema::dropIfExists('roles');
    Schema::dropIfExists('users');
});

test('subscription reminder recipients include tenant admin and admin users', function () {
    Role::create(['name' => RoleEnum::ADMIN, 'guard_name' => 'sanctum']);
    Role::create(['name' => RoleEnum::CUSTOMER, 'guard_name' => 'sanctum']);

    [$admin, $duplicateAdmin, $customer] = User::withoutEvents(fn () => [
        User::factory()->create(['email' => 'admin-user@example.com']),
        User::factory()->create(['email' => 'owner@example.com']),
        User::factory()->create(['email' => 'customer@example.com']),
    ]);

    $admin->assignRole(RoleEnum::ADMIN);
    $duplicateAdmin->assignRole(RoleEnum::ADMIN);
    $customer->assignRole(RoleEnum::CUSTOMER);

    $method = new ReflectionMethod(SendSubscriptionReminders::class, 'subscriptionReminderRecipients');

    $recipients = $method->invoke(new SendSubscriptionReminders, 'owner@example.com');

    expect($recipients)->toEqual([
        'owner@example.com',
        'admin-user@example.com',
    ]);
});

test('send emails job accepts multiple recipients', function () {
    Queue::fake();

    SendEmailsJob::dispatch(
        ['owner@example.com', 'admin-user@example.com'],
        'Subscription Reminder',
        'tenants.7_Days_reminder',
        []
    );

    Queue::assertPushed(SendEmailsJob::class, fn (SendEmailsJob $job): bool => $job->to === [
        'owner@example.com',
        'admin-user@example.com',
    ]);
});

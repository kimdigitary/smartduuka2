<?php

use App\Models\CentralUser;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config(['tenancy.database.central_connection' => config('database.default')]);

    Schema::dropIfExists('sessions');
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('global_id')->unique();
        $table->string('email')->nullable()->unique();
        $table->string('username')->unique();
        $table->string('password');
        $table->timestamps();
        $table->softDeletes();
    });

    Schema::create('sessions', function (Blueprint $table) {
        $table->string('id')->primary();
        $table->foreignId('user_id')->nullable()->index();
        $table->string('ip_address', 45)->nullable();
        $table->text('user_agent')->nullable();
        $table->longText('payload');
        $table->integer('last_activity')->index();
    });
});

afterEach(function () {
    Schema::dropIfExists('sessions');
    Schema::dropIfExists('users');
});

it('soft deletes users without removing associated data', function () {
    DB::table('users')->insert([
        'id' => 1,
        'name' => 'Soft Delete User',
        'global_id' => 'soft-delete-user-global-id',
        'email' => 'soft-delete-user@example.com',
        'username' => 'soft-delete-user',
        'password' => 'password',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('sessions')->insert([
        'id' => 'soft-delete-user-session',
        'user_id' => 1,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'test-session-payload',
        'last_activity' => now()->timestamp,
    ]);

    $user = User::query()->firstOrFail();

    $user->delete();

    $this->assertSoftDeleted($user);
    $this->assertDatabaseHas('sessions', [
        'id' => 'soft-delete-user-session',
        'user_id' => $user->id,
    ]);

    expect(User::query()->find($user->id))->toBeNull()
        ->and(User::withTrashed()->find($user->id))->not->toBeNull()
        ->and(CentralUser::query()->find($user->id))->toBeNull()
        ->and(CentralUser::withTrashed()->find($user->id))->not->toBeNull();
});

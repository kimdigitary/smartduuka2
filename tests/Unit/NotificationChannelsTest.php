<?php

use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Smartisan\Settings\Facades\Settings;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Settings::shouldReceive('group->get')
        ->with('events')
        ->andReturn(json_encode([
            [
                'id' => 'new_order',
                'channels' => [
                    'email' => false,
                    'sms' => false,
                    'whatsapp' => false,
                    'system' => true,
                ],
            ],
        ]));
});

test('system notifications are stored and broadcast for users', function () {
    expect(notificationChannels(new User(['email' => 'admin@example.com']), 'new_order'))
        ->toBe(['database', 'broadcast']);
});

test('anonymous notification routes do not use user bound system channels', function () {
    expect(notificationChannels(new AnonymousNotifiable, 'new_order'))->toBe([]);
});

test('missing event settings default to system notifications', function () {
    Settings::shouldReceive('group->get')
        ->with('events')
        ->andReturn('[]');

    expect(notificationChannels(new User(['email' => 'admin@example.com']), 'new_order'))
        ->toBe(['database', 'broadcast']);
});

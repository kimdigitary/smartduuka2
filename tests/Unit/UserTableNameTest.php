<?php

use App\Models\User;

test('tenant users keep the application users table when IFRS is enabled', function () {
    expect((new User)->getTable())->toBe('users');
});

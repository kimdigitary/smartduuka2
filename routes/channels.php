<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('business.{identifier}', function ($user, $identifier) {
    $tenant = tenant();
    if ($tenant) {
        return true;
    }

    return false;
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

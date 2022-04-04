<?php

use Illuminate\Foundation\Auth\User;

it('had right configuration', function () {
    $configValue = config('eloquent-modification.models.modifier');

    expect($configValue)->toBe([
        'submitter' => User::class,
        'applier' => User::class,
        'reviewer' => User::class,
    ]);
});

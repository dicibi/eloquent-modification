<?php

use Dicibi\EloquentModification\Tests\Models\NormalModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User;

use function PHPUnit\Framework\assertInstanceOf;

it('can retrieve normal models', function () {
    $normalModel = NormalModel::query()->get();

    assertInstanceOf(Collection::class, $normalModel);
});

it('can retrieve user model', function () {
    $userModel = User::query()->get();

    assertInstanceOf(Collection::class, $userModel);
});

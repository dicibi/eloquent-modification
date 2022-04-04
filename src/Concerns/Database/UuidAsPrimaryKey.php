<?php

namespace Dicibi\EloquentModification\Concerns\Database;

use Illuminate\Support\Str;

trait UuidAsPrimaryKey
{
    /** @noinspection PhpUnused */
    public static function bootUuidAsPrimaryKey(): void
    {
        self::creating(static fn (self $model) => $model->setAttribute('id', (string) Str::orderedUuid()));
    }
}

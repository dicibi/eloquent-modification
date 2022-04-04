<?php

namespace Dicibi\EloquentModification\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dicibi\EloquentModification\EloquentModification
 *
 * @method static string getSubmitterModel()
 * @method static string getApplierModel()
 * @method static string getReviewerModel()
 */
class EloquentModification extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'eloquent-modification';
    }
}

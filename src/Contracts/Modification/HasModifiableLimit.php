<?php

namespace Dicibi\EloquentModification\Contracts\Modification;

/**
 * Interface HasModifiableLimit.
 *
 * When model implement this interface will filter the captured attributes
 * and when the dirty attributes is empty the modifiable will not be created.
 */
interface HasModifiableLimit
{
    public function captureAttributes(): array;
}

<?php

namespace Dicibi\EloquentModification\Contracts\Modification;

use Dicibi\EloquentModification\Models\Modification;

interface PendingModifiable extends Modifiable
{
    public function saveLater(): bool;

    public function applyChangeFrom(Modification $modification): bool;

    public function rollbackChanges(): void;
}

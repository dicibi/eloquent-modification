<?php

namespace Dicibi\EloquentModification\Contracts\Modification;

use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Contracts\Auth\Authenticatable;

interface ModifiableSnapshot
{
    public function loadSnapshotFor(Authenticatable $authenticatable);

    public function getSnapshotModification(): ?Modification;

    public function getDifferenceState(): array;

    public function shouldMutateAttributeFromState(): bool;
}

<?php

namespace Dicibi\EloquentModification\Contracts\Modification;

interface ModifiableHasIdentifier
{
    public function getIdentifierForModifiable(): ?string;

    public function loadSnapshotByIdentifier(?string $identifier, ?string $modificationStatus = null);
}

<?php

namespace Dicibi\EloquentModification\Contracts\Modification;

use Illuminate\Database\Eloquent\Model;

interface ModifiableBag
{
    public function getPayloads(): array;

    public function getState(): array;

    public function castAttributesFromModifiable(Modifiable $modifiable): void;
}

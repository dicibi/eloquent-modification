<?php

namespace Dicibi\EloquentModification\Contracts\Modification;

interface ModifiableBag
{
    public function getPayloads(): array;

    public function getState(): array;
}

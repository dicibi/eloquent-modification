<?php

namespace Dicibi\EloquentModification\Contracts\Modification;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface Modifiable
{
    public function modifications(): MorphMany;

    public function modification(): MorphOne;

    public function getModifiableBag(): ModifiableBag;

    public function getKey();

    public function getWillRecordModification(): bool;

    public function saveWithoutModifiable(): void;
}

<?php

namespace Dicibi\EloquentModification\Contracts\Modification;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Modifiable
{
    public function modifications(): MorphMany;

    public function modification(): MorphOne;

    public function getModifiableBag(): ModifiableBag;

    public function castModifiableAttribute(string $key, mixed $value): mixed;

    public function getKey();
}

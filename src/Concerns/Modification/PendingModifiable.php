<?php

namespace Dicibi\EloquentModification\Concerns\Modification;

use Dicibi\EloquentModification\Models\Modification;
use Dicibi\EloquentModification\Jobs\Modification\ProceedModification;

trait PendingModifiable
{
    public function saveLater(): bool
    {
        if ($this->isDirty()) {
            dispatch(new ProceedModification(
                $this,
                auth()->user(),
                Modification::STATUS_PENDING,
            ));
        }

        return $this->save();
    }

    public function rollbackChanges(): void
    {
        $this->attributes = $this->getRawOriginal();

        $this->syncChanges();
    }

    public function applyChangeFrom(Modification $modification): bool
    {
        foreach ($modification->payloads as $attribute => $value) {
            $this->setAttribute($attribute, $value);
        }

        return $this->save();
    }
}

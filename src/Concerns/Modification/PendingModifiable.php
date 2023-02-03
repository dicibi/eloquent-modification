<?php

namespace Dicibi\EloquentModification\Concerns\Modification;

use Dicibi\EloquentModification\Jobs\Modification\ProceedModification;
use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Bus;

trait PendingModifiable
{
    public function saveLater(?Authenticatable $executor = null, ?Authenticatable $reviewer = null, ...$jobChains): bool
    {
        $proceedModificationJob = ProceedModification::make(
            modifiable: $this,
            executor: $executor ?? auth()->user(),
            reviewer: $reviewer,
            status: Modification::STATUS_PENDING,
        );

        if ($proceedModificationJob) {
            Bus::chain([
                $proceedModificationJob,
                ...$jobChains,
            ])->onConnection('sync')->dispatch();
        }

        // we do a save here because, maybe the model is implement HasModifiableLimit,
        // and we need to save the rest of the unfiltered columns
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

    public function pendingModification(): MorphOne
    {
        return $this->morphOne(Modification::class, 'modifiable')
            ->latestOfMany()
            ->where('status', Modification::STATUS_PENDING);
    }
}

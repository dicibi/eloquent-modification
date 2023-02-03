<?php

namespace Dicibi\EloquentModification\Concerns\Modification;

use Dicibi\EloquentModification\Contracts\Modification\Modifiable as ModifiableContract;
use Dicibi\EloquentModification\Contracts\Modification\ModifiableBag as ModifiableBagContract;
use Dicibi\EloquentModification\Jobs\Modification\ProceedModification;
use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Bus;

/**
 * @property  Collection|null modifications
 * @property  Modification|null modification
 */
trait Modifiable
{
    public static function bootModifiable(): void
    {
        self::updating(static function (ModifiableContract|Model $modifiable) {
            $proceedModificationJob = ProceedModification::make(
                modifiable: $modifiable,
                executor: auth()->user(),
            );

            if ($proceedModificationJob) {
                Bus::dispatchSync($proceedModificationJob);
            }
        });
    }

    private bool $willRecordModification = true;

    public function modifications(): MorphMany
    {
        return $this
            ->morphMany(
                Modification::class,
                'modifiable',
                'modifiable_type',
                'string' === $this->getKeyType() ? 'modifiable_uuid' : 'modifiable_id',
                'id',
            )
            ->orderByDesc('id');
    }

    public function modification(): MorphOne
    {
        return $this
            ->morphOne(
                Modification::class,
                'modifiable',
                'modifiable_type',
                'string' === $this->getKeyType() ? 'modifiable_uuid' : 'modifiable_id',
                'id',
            )
            ->latestOfMany();
    }

    public function getModifiableBag(): ModifiableBagContract
    {
        $payloads = $this->getDirty();

        return new Modifiable\Bag($payloads, $this->getOriginal());
    }

    public function getWillRecordModification(): bool
    {
        return $this->willRecordModification;
    }

    public function saveWithoutModifiable(): void
    {
        $this->willRecordModification = false;

        $this->save();
    }
}

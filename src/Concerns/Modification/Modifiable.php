<?php

namespace Dicibi\EloquentModification\Concerns\Modification;

use Dicibi\EloquentModification\Contracts\Modification\HasModifiableLimit;
use Dicibi\EloquentModification\Contracts\Modification\Modifiable as ModifiableContract;
use Dicibi\EloquentModification\Contracts\Modification\ModifiableBag as ModifiableBagContract;
use Dicibi\EloquentModification\Jobs\Modification\ProceedModification;
use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @property  \Illuminate\Database\Eloquent\Collection|null modifications
 * @property Modification|null modification
 */
trait Modifiable
{
    public static function bootModifiable(): void
    {
        self::updating(static function (ModifiableContract|Model $modifiable) {
            if ($modifiable->isDirty()) {
                $modifiableBag = $modifiable->getModifiableBag();
                $payloads = $modifiableBag->getPayloads();

                if ($modifiable instanceof HasModifiableLimit) {
                    $captureAttributes = $modifiable->captureAttributes();

                    $payloads = array_filter($payloads, static function ($value, $key) use ($captureAttributes) {
                        return in_array($key, $captureAttributes, true);
                    }, ARRAY_FILTER_USE_BOTH);

                    // prevent saving modifications if there are no changes
                    if (empty($payloads)) {
                        return;
                    }

                    $modifiableBag->setPayloads($payloads);
                }

                dispatch(new ProceedModification(
                    $modifiable,
                    auth()->user(),
                    Modification::STATUS_APPLIED,
                    modifiableBag: $modifiableBag,
                ));
            }
        });
    }

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
        return new Modifiable\Bag($this->getDirty(), $this->getRawOriginal());
    }

    public function castModifiableAttribute(string $key, mixed $value): mixed
    {
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        return $value;
    }
}

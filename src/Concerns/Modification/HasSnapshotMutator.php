<?php

namespace Dicibi\EloquentModification\Concerns\Modification;

use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Arr;

/**
 * @property Modification|null pendingModification
 */
trait HasSnapshotMutator
{
    protected Authenticatable $scopedUserForSnapshot;

    protected bool $mutateAttributeToModificationState = false;

    /**
     * @var int[]|string[]
     */
    protected array $loadedRelationsBeforeMutate;

    public function shouldMutateAttributeFromState(): bool
    {
        return $this->mutateAttributeToModificationState;
    }

    public function loadSnapshotFor(Authenticatable $authenticatable): self
    {
        $this->scopedUserForSnapshot = $authenticatable;

        $modification = $this->modifications()
            ->where('submitted_by', $authenticatable->getAuthIdentifier())
            ->first();

        if ($modification) {
            $this->usingModification($modification);
        }

        return $this;
    }

    protected function usingModification(Modification|Model $modification): void
    {
        if ($modification->modifiable_type !== Model::getActualClassNameForMorph(self::class)
            && $modification->modifiable_id !== $this->getKey()) {
            throw new \RuntimeException('Given modification is not match with this modifiable.');
        }

        $relationName = 'modification';

        // If the relation is already loaded, we will not re-load it after we mutate the attributes
        $this->loadedRelationsBeforeMutate = array_keys(Arr::except($this->getRelations(), [$relationName]));
        $this->unsetRelations();

        // activate flag to mutate attribute from modification
        $this->mutateAttributeToModificationState = true;
        $this->setRelation($relationName, $modification);

        // reload relations after modify data
        $this->load($this->loadedRelationsBeforeMutate);
    }

    public function getSnapshotModification(): ?Modification
    {
        return $this->relationLoaded('modification') ? $this->getRelation('modification') : null;
    }

    public function pendingModification(): MorphOne
    {
        return $this->morphOne(Modification::class, 'modifiable')
            ->latestOfMany()
            ->where('status', Modification::STATUS_PENDING);
    }

    public function hasGetMutator($key): bool
    {
        if ($this->mutateAttributeToModificationState) {
            $modification = $this->getSnapshotModification();

            if ($modification) {
                return property_exists($modification->payloads, $key) || property_exists($modification->status, $key);
            }
        }

        return parent::hasGetMutator($key);
    }

    protected function mutateAttribute($key, $value)
    {
        if ($this->mutateAttributeToModificationState) {
            $modification = $this->getSnapshotModification();

            $modificationValue = null;
            $modificationPropertyFound = false;
            if ($modification) {
                if (property_exists($modification->state, $key)) {
                    $modificationPropertyFound = true;
                    $modificationValue = $modification->state->{$key};
                }

                if (property_exists($modification->payloads, $key)) {
                    $modificationPropertyFound = true;
                    $modificationValue = $modification->payloads->{$key};
                }
            }

            if ($modificationPropertyFound) {
                if (parent::hasCast($key)) {
                    $modificationValue = parent::castAttribute($key, $modificationValue);
                }

                if (parent::hasGetMutator($key)) {
                    return parent::mutateAttribute($key, $modificationValue);
                }

                return $modificationValue;
            }
        }

        return parent::mutateAttribute($key, $value);
    }

    public function getDifferenceState(): array
    {
        $snapshotModification = $this->getSnapshotModification();
        $stateFromModification = $snapshotModification?->state ?? new \stdClass();
        $payloadFromModification = $snapshotModification?->payloads ?? new \stdClass();
        $difference = [];

        $mutateAttributeIsEquivalentWithState = function ($key) use ($stateFromModification): bool {
            if (! property_exists($stateFromModification, $key)) {
                return true;
            }

            $attribute = Arr::get($this->attributes, $key);
            $snapshotAttribute = $stateFromModification->{$key};

            return $attribute === $snapshotAttribute;
        };

        foreach ($this->getAttributes() as $key => $value) {
            if (property_exists($payloadFromModification, $key)) {
                $difference[$key] = $payloadFromModification->{$key};

                continue;
            }
            if (! $mutateAttributeIsEquivalentWithState($key)) {
                $difference[$key] = $stateFromModification->{$key};
            }
        }

        return $difference;
    }

    /**
     * Get the mutated attributes for a given instance.
     *
     * @return array
     */
    public function getMutatedAttributes(): array
    {
        $originalMutatedAttributes = parent::getMutatedAttributes();

        $snapshotMutatorAttributes = [];
        if ($this->shouldMutateAttributeFromState()) {
            $snapshotMutatorAttributes = $this->getDifferenceState();
        }

        return [
            ...$originalMutatedAttributes,
            ...array_keys($snapshotMutatorAttributes),
        ];
    }
}

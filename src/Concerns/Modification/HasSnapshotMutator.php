<?php

namespace Dicibi\EloquentModification\Concerns\Modification;

use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;

/**
 * @property Modification|null pendingModification
 */
trait HasSnapshotMutator
{
    use CastAndAttributeMutatorOverrider;

    protected Authenticatable $scopedUserForSnapshot;

    protected bool $mutateAttributeToModificationState = false;

    public function shouldMutateAttributeFromState(): bool
    {
        return $this->mutateAttributeToModificationState;
    }

    public function loadSnapshotFor(Authenticatable $authenticatable, ?string $modificationStatus = null): void
    {
        $this->scopedUserForSnapshot = $authenticatable;

        $modificationQuery = $this->modifications()
            ->where('submitted_by', $authenticatable->getAuthIdentifier());

        if ($modificationStatus && in_array($modificationStatus, Modification::getStatuses(), true)) {
            $modificationQuery->where('status', $modificationStatus);
        }

        $modification = $modificationQuery->first();

        if ($modification) {
            $this->usingModification($modification);
        }
    }

    public function usingModification(Modification|Model $modification): self
    {
        if ($modification->modifiable_type !== Model::getActualClassNameForMorph(self::class)
            && $modification->modifiable_id !== $this->getKey()) {
            throw new \RuntimeException('Given modification is not match with this modifiable.');
        }

        $relationName = 'modification';

        $loadedRelations = $this->getRelations();

        // relation that need reloaded are relation with self foreign key such as belongs to.
        $excludedRelations = [$relationName, 'modifications'];

        foreach ($loadedRelations as $loadedRelationName => $modelValue) {
            $relation = $this->{$loadedRelationName}();

            if (! $relation instanceof BelongsTo) {
                $excludedRelations[] = $loadedRelationName;
            }
        }

        $watchedRelations = array_keys(Arr::except($loadedRelations, $excludedRelations));

        // If the relations is already loaded, we will re-load it all except for modifications and modification.
        // Reload relations will occur after we mutate the attributes
        foreach ($watchedRelations as $watchedRelationName) {
            $this->unsetRelation($watchedRelationName);
        }

        // activate flag to mutate attribute from modification
        $this->mutateAttributeToModificationState = true;
        $this->setRelation($relationName, $modification);

        // reload relations after modify data later
        $this->load($watchedRelations);

        return $this;
    }

    public function getSnapshotModification(): ?Modification
    {
        return $this->relationLoaded('modification') ? $this->getRelation('modification') : null;
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

            $attribute = parent::getOriginal($key);
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

<?php

namespace Dicibi\EloquentModification\Concerns\Modification;

trait CastAndAttributeMutatorOverrider
{
    public function hasGetMutator($key): bool
    {
        if ($this->existOnSnapshot($key)) {
            return true;
        }

        return parent::hasGetMutator($key);
    }

    protected function mutateAttribute($key, $value)
    {
        if ($valueFromSnapshot = $this->getValueFromSnapshot($key)) {
            return $valueFromSnapshot;
        }

        if (! parent::hasGetMutator($key)) {
            return $value;
        }

        return parent::mutateAttribute($key, $value);
    }

    protected function mutateAttributeMarkedAttribute($key, $value): mixed
    {
        if ($valueFromSnapshot = $this->getValueFromSnapshot($key)) {
            return $valueFromSnapshot;
        }

        return parent::mutateAttributeMarkedAttribute($key, $value);
    }

    private function existOnSnapshot($key): bool
    {
        if ($this->mutateAttributeToModificationState) {
            $modification = $this->getSnapshotModification();

            if ($modification) {
                return property_exists($modification->payloads, $key) || property_exists($modification->state, $key);
            }
        }

        return false;
    }

    private function getValueFromSnapshot($key): mixed
    {
        if ($this->mutateAttributeToModificationState) {
            $modification = $this->getSnapshotModification();

            $modificationValue = null;
            if ($modification) {
                if (property_exists($modification->state, $key)) {
                    $modificationValue = $modification->state->{$key};

                    // transform only date because it is not part of the cast
                    if (null !== $modificationValue && \in_array($key, $this->getDates(), false)) {
                        $modificationValue = $this->asDateTime($modificationValue);
                    }
                }

                if (property_exists($modification->payloads, $key)) {
                    $modificationValue = $modification->payloads->{$key};

                    /*
                     * @see \Illuminate\Database\Eloquent\Concerns\HasAttributes
                     * method : transformModelValue
                     */
                    if (parent::hasGetMutator($key)) {
                        $modificationValue = parent::mutateAttribute($key, $modificationValue);
                    } elseif (parent::hasAttributeGetMutator($key)) {
                        $modificationValue = parent::mutateAttributeMarkedAttribute($key, $modificationValue);
                    }

                    if (parent::hasCast($key)) {
                        $modificationValue = parent::castAttribute($key, $modificationValue);
                    } elseif (null !== $modificationValue && \in_array($key, parent::getDates(), false)) {
                        $modificationValue = parent::asDateTime($modificationValue);
                    }
                }
            }

            if (! empty($modificationValue)) {
                return $modificationValue;
            }
        }

        return null;
    }
}

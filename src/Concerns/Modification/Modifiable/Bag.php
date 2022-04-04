<?php

namespace Dicibi\EloquentModification\Concerns\Modification\Modifiable;

use Dicibi\EloquentModification\Contracts\Modification\Modifiable;
use Dicibi\EloquentModification\Contracts\Modification\ModifiableBag;

class Bag implements ModifiableBag
{
    public function __construct(
        protected array $payloads = [],
        protected array $state = [],
    ) {
    }

    /**
     * @return array
     */
    public function getPayloads(): array
    {
        return $this->payloads;
    }

    /**
     * @param array $payloads
     */
    public function setPayloads(array $payloads): void
    {
        $this->payloads = $payloads;
    }

    /**
     * @param array $state
     *
     * @return Bag
     */
    public function setState(array $state): self
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return array
     */
    public function getState(): array
    {
        return $this->state;
    }

    /**
     * Casting attribute according to modifiable
     * @param \Dicibi\EloquentModification\Contracts\Modification\Modifiable $modifiable
     * @return void
     * @see \Dicibi\EloquentModification\Jobs\Modification\ProceedModification
     *
     */
    public function castAttributesFromModifiable(Modifiable $modifiable): void
    {
        foreach ($this->payloads as $key => $value) {
            $this->payloads[$key] = $modifiable->castModifiableAttribute($key, $value);
        }

        foreach ($this->state as $key => $value) {
            $this->state[$key] = $modifiable->castModifiableAttribute($key, $value);
        }
    }
}

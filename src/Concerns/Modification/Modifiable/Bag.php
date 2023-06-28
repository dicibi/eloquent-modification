<?php

namespace Dicibi\EloquentModification\Concerns\Modification\Modifiable;

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
}

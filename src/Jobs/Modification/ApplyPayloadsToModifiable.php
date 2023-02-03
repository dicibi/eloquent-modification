<?php

namespace Dicibi\EloquentModification\Jobs\Modification;

use Dicibi\EloquentModification\Contracts\Modification\Modifiable;
use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ApplyPayloadsToModifiable implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use Batchable;

    public function __construct(
        public Modification $modification,
        public Authenticatable $executor,
    ) {
    }

    /**
     * @throws \JsonException
     */
    public function handle(): void
    {
        if (Modification::STATUS_PENDING !== $this->modification->status) {
            throw new \RuntimeException('Modification must be pending to apply');
        }

        /** @var Model|Modifiable $modifiable */
        $modifiable = $this->modification->modifiable;

        foreach ($this->modification->payloads as $key => $value) {
            // we give special treatment for object and array because raw data for getter and setter are different
            // setter need raw data as object/array but getter need raw data to be string(json)
            if ($modifiable->hasCast($key, 'object')) {
                $modifiable->{$key} = json_decode($value, false, 512, JSON_THROW_ON_ERROR);
            } elseif ($modifiable->hasCast($key, 'array')) {
                $modifiable->{$key} = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } else {
                $modifiable->{$key} = $value;
            }
        }

        $modifiable->saveWithoutModifiable();

        $this->modification->status = Modification::STATUS_APPLIED;

        $this->modification->applied_at = now();
        $this->modification->applier()->associate($this->executor);

        $this->modification->save();
    }
}

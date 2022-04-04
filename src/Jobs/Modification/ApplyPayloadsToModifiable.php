<?php

namespace Dicibi\EloquentModification\Jobs\Modification;

use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;

class ApplyPayloadsToModifiable implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Modification $modification,
        public Authenticatable $executor,
    ) {
    }

    public function handle(): void
    {
        if (Modification::STATUS_PENDING !== $this->modification->status) {
            throw new RuntimeException('Modification must be pending to apply');
        }

        $modifiable = $this->modification->modifiable;

        foreach ($this->modification->payloads as $key => $value) {
            $modifiable->{$key} = $value;
        }

        $modifiable->save();

        $this->modification->status = Modification::STATUS_APPLIED;

        $this->modification->applied_at = now();
        $this->modification->applied_by = $this->executor->getAuthIdentifier();

        $this->modification->save();
    }
}

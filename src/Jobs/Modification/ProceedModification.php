<?php

namespace Dicibi\EloquentModification\Jobs\Modification;

use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\InteractsWithQueue;
use Dicibi\EloquentModification\Contracts\Modification\Modifiable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Auth\Authenticatable;
use Dicibi\EloquentModification\Contracts\Modification\PendingModifiable;
use Dicibi\EloquentModification\Contracts\Modification\ModifiableBag as ModifiableBagContract;

class ProceedModification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected Model|Modifiable|PendingModifiable $modifiable,
        protected ?Authenticatable $executor = null,
        protected string $status = Modification::STATUS_APPLIED,
        protected ?string $action = Modification::ACTION_UPDATE,
        protected ?string $info = null,
        private ?ModifiableBagContract $modifiableBag = null,
    ) {
        if (! $this->modifiableBag) {
            $this->modifiableBag = $this->modifiable->getModifiableBag();
        }

        if (Modification::STATUS_PENDING === $this->status && $this->modifiable instanceof PendingModifiable) {
            $this->modifiable->rollbackChanges();
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        /** @var Modification $modification */
        $modification = $this->getModification();

        $modification->setModifiable($this->modifiable);

        $modification->status = $this->status;
        $modification->action = $this->action;

        $this->modifiableBag->castAttributesFromModifiable($this->modifiable);

        $modification->payloads = $this->modifiableBag->getPayloads() ?: new \stdClass();
        $modification->state = $this->modifiableBag->getState() ?: new \stdClass();

        if ($this->info) {
            $modification->info = $this->info;
        }

        if ($this->executor) {
            $modification->submitter()->associate($this->executor);
        }

        if (Modification::STATUS_APPLIED === $modification->status) {
            $modification->applied_at = now();

            if ($this->executor) {
                $modification->applier()->associate($this->executor);
            }
        }

        $modification->save();
    }

    private function getModification(): Modification|Model
    {
        return $this->modifiable->modification()
            ->whereNotNull('submitted_by')
            ->where('submitted_by', $this->executor?->getAuthIdentifier())
            ->where('status', Modification::STATUS_PENDING)
            ->firstOrNew();
    }
}

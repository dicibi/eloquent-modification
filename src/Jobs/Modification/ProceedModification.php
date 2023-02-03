<?php

namespace Dicibi\EloquentModification\Jobs\Modification;

use Dicibi\EloquentModification\Contracts\Modification\HasModifiableLimit;
use Dicibi\EloquentModification\Contracts\Modification\Modifiable;
use Dicibi\EloquentModification\Contracts\Modification\ModifiableBag as ModifiableBagContract;
use Dicibi\EloquentModification\Contracts\Modification\ModifiableHasIdentifier;
use Dicibi\EloquentModification\Contracts\Modification\PendingModifiable;
use Dicibi\EloquentModification\Models\Modification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use stdClass;

class ProceedModification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Model|Modifiable|PendingModifiable|ModifiableHasIdentifier $modifiable,
        protected ModifiableBagContract                                      $modifiableBag,
        protected ?Authenticatable                                           $executor = null,
        protected ?Authenticatable                                           $reviewer = null,
        protected string                                                     $status = Modification::STATUS_APPLIED,
        protected string                                                     $action = Modification::ACTION_UPDATE,
        protected ?string                                                    $info = null,
    )
    {
    }

    public function handle(): void
    {
        /** @var Modification $modification */
        $modification = $this->getModification();

        $modification->setModifiable($this->modifiable);

        if ($this->modifiable instanceof ModifiableHasIdentifier) {
            $modification->identifier = $this->modifiable->getIdentifierForModifiable();
        }

        $modification->status = $this->status;
        $modification->action = $this->action;

        $modification->state = $this->modifiableBag->getState() ?: new stdClass();

        if ($modification->exists && $modification->payloads instanceof stdClass) {
            $payloads = $modification->payloads;

            foreach ($this->modifiableBag->getPayloads() as $key => $value) {
                $payloads->{$key} = $value;
            }

            $modification->payloads = $payloads;
        } else {
            $modification->payloads = $this->modifiableBag->getPayloads() ?: new stdClass();
        }

        if ($this->info) {
            $modification->info = $this->info;
        }

        if ($this->executor) {
            $modification->submitter()->associate($this->executor);
        }

        if ($this->reviewer) {
            $modification->reviewer()->associate($this->reviewer);
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

    public static function make(
        Model|Modifiable|PendingModifiable|ModifiableHasIdentifier $modifiable,
        ?Authenticatable                                           $executor = null,
        ?Authenticatable                                           $reviewer = null,
        string                                                     $status = Modification::STATUS_APPLIED,
        string                                                     $action = Modification::ACTION_UPDATE,
        ?string                                                    $info = null,
    ): self|null
    {
        if (!$modifiable->isDirty()) {
            return null;
        }

        if (!$modifiable->getWillRecordModification()) {
            return null;
        }

        $modifiableBag = $modifiable->getModifiableBag();
        $payloads = $modifiableBag->getPayloads();

        if ($modifiable instanceof HasModifiableLimit) {
            $captureAttributes = $modifiable->captureAttributes();

            $payloads = array_filter($payloads, static function ($value, $key) use ($captureAttributes) {
                return in_array($key, $captureAttributes, true);
            }, ARRAY_FILTER_USE_BOTH);

            // prevent saving modifications if there are no changes
            if (empty($payloads)) {
                return null;
            }

            $modifiableBag->setPayloads($payloads);
        }

        if (Modification::STATUS_PENDING === $status && $modifiable instanceof PendingModifiable) {
            $modifiable->rollbackChanges();
        }

        return new static(
            modifiable: $modifiable,
            modifiableBag: $modifiableBag,
            executor: $executor,
            reviewer: $reviewer,
            status: $status,
            action: $action,
            info: $info,
        );
    }
}

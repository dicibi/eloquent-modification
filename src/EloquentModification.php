<?php

namespace Dicibi\EloquentModification;

use Closure;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

class EloquentModification
{
    protected ConfigRepository $config;

    public function __construct(Closure $resolver)
    {
        [$config] = $resolver();

        $this->config = $config;
    }

    public function getSubmitterModel(): string
    {
        return $this->config->get('eloquent-modification.models.modifier.submitter');
    }

    public function getApplierModel(): string
    {
        return $this->config->get('eloquent-modification.models.modifier.applier');
    }

    public function getReviewerModel(): string
    {
        return $this->config->get('eloquent-modification.models.modifier.reviewer');
    }
}

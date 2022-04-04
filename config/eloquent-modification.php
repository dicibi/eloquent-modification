<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Eloquent Models
    |--------------------------------------------------------------------------
    |
    | This is the list of the models that used by Modification model to notice
    | who is the submitter, applier and reviewer of the changes. Example:
    | - Submitter : The user who submit the modification.
    | - Applier : The user who apply the modification.
    | - Reviewer : The user who review the modification.
    */
    'models' => [
        'modification' => Dicibi\EloquentModification\Models\Modification::class,

        'modifier' => [
            'submitter' => App\Models\User::class,
            'applier' => App\Models\User::class,
            'reviewer' => App\Models\User::class,
        ],
    ],
];

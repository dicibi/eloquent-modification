<?php

namespace Dicibi\EloquentModification\Tests\Models;

use Dicibi\EloquentModification\Concerns\Modification\HasSnapshotMutator;
use Dicibi\EloquentModification\Concerns\Modification\Modifiable;
use Dicibi\EloquentModification\Concerns\Modification\PendingModifiable;
use Dicibi\EloquentModification\Contracts\Modification\HasModifiableLimit;
use Dicibi\EloquentModification\Contracts\Modification\Modifiable as ModifiableContract;
use Dicibi\EloquentModification\Contracts\Modification\ModifiableSnapshot;
use Dicibi\EloquentModification\Contracts\Modification\PendingModifiable as PendingModifiableContract;
use Illuminate\Database\Eloquent\Model;

class NormalModel extends Model implements ModifiableContract, ModifiableSnapshot, HasModifiableLimit, PendingModifiableContract
{
    use Modifiable;
    use HasSnapshotMutator;
    use PendingModifiable;

    protected $fillable = [
        'name',
        'description',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function captureAttributes(): array
    {
        return [
            'name',
            'description',
            'data',
        ];
    }
}

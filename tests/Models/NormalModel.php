<?php

namespace Dicibi\EloquentModification\Tests\Models;

use Dicibi\EloquentModification\Concerns\Modification\HasSnapshotMutator;
use Dicibi\EloquentModification\Concerns\Modification\Modifiable;
use Dicibi\EloquentModification\Contracts\Modification\Modifiable as ModifiableContract;
use Dicibi\EloquentModification\Contracts\Modification\ModifiableSnapshot;
use Illuminate\Database\Eloquent\Model;

class NormalModel extends Model implements ModifiableContract, ModifiableSnapshot
{
    use Modifiable;
    use HasSnapshotMutator;

    protected $fillable = [
        'name',
        'description',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}

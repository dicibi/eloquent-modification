<?php

namespace Dicibi\EloquentModification\Models;

use Dicibi\EloquentModification\Contracts\Modification\Modifiable;
use Dicibi\EloquentModification\Facades\EloquentModification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Carbon;

/**
 * @property string $action
 * @property object $state
 * @property object $payloads
 * @property string $status
 * @property Carbon|null $applied_at
 * @property string|null $info
 * @property int $modifiable_id
 * @property string $modifiable_type
 * @property int $submitted_by
 * @property Authenticatable $user                            $submitter
 * @property Modifiable $modifiable
 * @property int|mixed $applied_by
 * @property string|null $identifier
 */
class Modification extends Model
{
    use HasFactory;
    use HasUuids;

    public const STATUS_REJECT = 'reject';
    public const STATUS_CANCEL = 'cancel';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPLIED = 'applied';

    public const ACTION_UPDATE = 'update';
    public const ACTION_DELETE = 'delete';

    protected $keyType = 'string';

    protected $fillable = [
        'action',
        'state',
        'payloads',
        'status',
        'condition',
        'submitted_by',
        'reviewed_by',
        'applied_by',
        'applied_at',
        'info',
    ];

    protected $casts = [
        'state' => 'object',
        'payloads' => 'object',
        'applied_at' => 'datetime',
    ];

    protected $hidden = [
        'modifiable_type',
        'modifiable_uuid',
        'modifiable_id',
        'submitted_by',
        'reviewed_by',
        'applied_by',
    ];

    public static function getStatuses(): array
    {
        return [
            self::STATUS_APPLIED,
            self::STATUS_CANCEL,
            self::STATUS_PENDING,
            self::STATUS_REJECT,
        ];
    }

    public function modifiable(): Relations\MorphTo
    {
        return $this->morphTo('modifiable', 'modifiable_type', $this->getModifiableId(), 'id');
    }

    public function submitter(): Relations\BelongsTo
    {
        return $this->belongsTo(EloquentModification::getSubmitterModel(), 'submitted_by', 'id', 'users');
    }

    public function reviewer(): Relations\BelongsTo
    {
        return $this->belongsTo(EloquentModification::getReviewerModel(), 'reviewed_by', 'id', 'users');
    }

    public function applier(): Relations\BelongsTo
    {
        return $this->belongsTo(EloquentModification::getApplierModel(), 'applied_by', 'id', 'users');
    }

    public function setModifiable(Model|Modifiable $modifiable): Model
    {
        $this->modifiable_type = $modifiable::class;

        return $this->modifiable()->associate($modifiable);
    }

    private function getModifiableId(): string
    {
        if (! empty($this->modifiable_type) && class_exists($this->modifiable_type)) {
            $modifiableType = $this->modifiable_type;

            /** @var Model $model */
            $model = new $modifiableType();

            if ($model instanceof Model && 'string' === $model->getKeyType()) {
                return 'modifiable_uuid';
            }
        }

        return 'modifiable_id';
    }
}

<?php

namespace Metafori\Archeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Metafori\Core\Models\User;

class ActivityImport extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_FAILED = 'failed';

    public const STATUS_COMPLETE = 'complete';

    public const STATUS_VALUES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_FAILED,
        self::STATUS_COMPLETE,
    ];

    protected $table = 'archeo_activity_imports';

    protected $fillable = [
        'path',
        'disk',
        'file_name',
        'user_id',
        'status',
        'job_id',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'import_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

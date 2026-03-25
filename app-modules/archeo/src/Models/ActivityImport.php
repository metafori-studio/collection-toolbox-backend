<?php

namespace Metafori\Archeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Core\Models\User;

use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityImport extends Model
{
    protected $fillable = [
        'job_id',
        'file_name',
        'user_id',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'import_id');
    }
}

<?php

namespace Metafori\Archeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Core\Models\User;

class ActivityAssignment extends Model
{
    protected $table = 'archeo_activity_assignments';


    protected $fillable = [
        'activity_id',
        'user_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

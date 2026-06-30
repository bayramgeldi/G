<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppealVote extends Model
{
    protected $fillable = [
        'appeal_id',
        'user_id',
        'vote',
    ];

    public function appeal(): BelongsTo
    {
        return $this->belongsTo(Appeal::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

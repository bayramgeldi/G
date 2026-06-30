<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Appeal extends Model
{
    protected $fillable = [
        'user_id',
        'appealable_type',
        'appealable_id',
        'statement',
        'status',
        'restore_votes_count',
        'keep_hidden_votes_count',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appealable(): MorphTo
    {
        return $this->morphTo();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(AppealVote::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}

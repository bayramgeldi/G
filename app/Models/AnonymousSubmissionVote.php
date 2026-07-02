<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnonymousSubmissionVote extends Model
{
    protected $fillable = [
        'anonymous_submission_id',
        'user_id',
        'vote',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(AnonymousSubmission::class, 'anonymous_submission_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

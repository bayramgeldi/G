<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Definition extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_id',
        'user_id',
        'meaning',
        'example',
        'votes_count',
        'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
        ];
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function reports()
    {
        return $this->morphMany(ModerationReport::class, 'reportable');
    }

    public function appeals()
    {
        return $this->morphMany(Appeal::class, 'appealable');
    }

    public function openAppeal()
    {
        return $this->morphOne(Appeal::class, 'appealable')->where('status', 'open');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function entries()
    {
        return $this->hasMany(Entry::class);
    }

    public function definitions()
    {
        return $this->hasMany(Definition::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function isModerationEligible(): bool
    {
        if (! $this->created_at || $this->created_at->gt(now()->subDays(config('moderation.active_account_days')))) {
            return false;
        }

        return $this->entries()->exists()
            || $this->definitions()->exists()
            || $this->votes()->count() >= config('moderation.active_vote_count');
    }
}

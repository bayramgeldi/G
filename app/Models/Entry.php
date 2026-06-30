<?php

namespace App\Models;

use App\Support\NormalizesTurkmenText;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'term',
        'slug',
        'normalized_term',
        'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public static function uniqueSlug(string $term): string
    {
        $base = NormalizesTurkmenText::slug($term);
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function definitions(): HasMany
    {
        return $this->hasMany(Definition::class);
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

    public function visibleDefinitions(): HasMany
    {
        return $this->definitions()->where('is_hidden', false);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        $normalized = NormalizesTurkmenText::normalize($term);

        return $query->where(function ($query) use ($term, $normalized) {
            $query->where('term', 'like', '%'.$term.'%')
                ->orWhere('normalized_term', 'like', '%'.$normalized.'%');
        });
    }
}

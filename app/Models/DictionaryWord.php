<?php

namespace App\Models;

use App\Support\NormalizesTurkmenText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class DictionaryWord extends Model
{
    protected $fillable = [
        'headword',
        'normalized_headword',
        'meaning',
        'source',
    ];

    public function aliases(): HasMany
    {
        return $this->hasMany(DictionaryAlias::class);
    }

    public static function lookup(string $word): ?self
    {
        $normalized = NormalizesTurkmenText::normalize($word);

        $exact = static::where('normalized_headword', $normalized)->first();

        if ($exact) {
            return $exact;
        }

        $alias = DictionaryAlias::with('dictionaryWord')
            ->where('normalized_alias', $normalized)
            ->first();

        return $alias?->dictionaryWord;
    }

    public static function suggestions(string $query, int $limit = 8): Collection
    {
        $normalized = NormalizesTurkmenText::normalize($query);

        if (mb_strlen($normalized, 'UTF-8') < 2) {
            return new Collection;
        }

        $words = static::query()
            ->where(function ($query) use ($normalized) {
                $query->where('normalized_headword', 'like', $normalized.'%')
                    ->orWhere('headword', 'like', $normalized.'%');
            })
            ->orderBy('headword')
            ->limit($limit)
            ->get();

        if ($words->count() >= $limit) {
            return $words;
        }

        $existingIds = $words->pluck('id');
        $aliasWords = DictionaryAlias::query()
            ->with('dictionaryWord')
            ->where(function ($query) use ($normalized) {
                $query->where('normalized_alias', 'like', $normalized.'%')
                    ->orWhere('alias', 'like', $normalized.'%');
            })
            ->when($existingIds->isNotEmpty(), fn ($query) => $query->whereNotIn('dictionary_word_id', $existingIds))
            ->orderBy('alias')
            ->limit(($limit - $words->count()) * 2)
            ->get()
            ->unique('dictionary_word_id')
            ->take($limit - $words->count())
            ->map(function (DictionaryAlias $alias) {
                $word = $alias->dictionaryWord;
                $word->setAttribute('matched_alias', $alias->alias);

                return $word;
            })
            ->filter();

        return $words->concat($aliasWords)->values();
    }
}

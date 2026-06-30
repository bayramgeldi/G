<?php

namespace App\Models;

use App\Support\NormalizesTurkmenText;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}

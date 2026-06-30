<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DictionaryAlias extends Model
{
    protected $fillable = [
        'dictionary_word_id',
        'alias',
        'normalized_alias',
    ];

    public function dictionaryWord(): BelongsTo
    {
        return $this->belongsTo(DictionaryWord::class);
    }
}

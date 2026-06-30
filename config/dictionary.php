<?php

return [
    'sqlite' => [
        'table' => env('DICTIONARY_SQLITE_TABLE'),
        'word_column' => env('DICTIONARY_SQLITE_WORD_COLUMN', 'word'),
        'meaning_column' => env('DICTIONARY_SQLITE_MEANING_COLUMN', 'meaning'),
    ],
];

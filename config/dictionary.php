<?php

return [
    'sqlite' => [
        'path' => base_path(env('DICTIONARY_SQLITE_PATH', 'storage/app/turkmen.sqlite')),
        'table' => env('DICTIONARY_SQLITE_TABLE', 'words'),
        'word_column' => env('DICTIONARY_SQLITE_WORD_COLUMN', 'word'),
        'meaning_column' => env('DICTIONARY_SQLITE_MEANING_COLUMN', 'definitions'),
    ],
];

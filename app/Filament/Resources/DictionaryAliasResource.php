<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DictionaryAliasResource\Pages;
use App\Models\DictionaryAlias;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DictionaryAliasResource extends ReadOnlyResource
{
    protected static ?string $model = DictionaryAlias::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Dictionary';

    protected static ?string $recordTitleAttribute = 'alias';

    public static function table(Table $table): Table
    {
        return static::readOnlyTable($table, [
            TextColumn::make('id')->sortable(),
            TextColumn::make('alias')->searchable()->sortable(),
            TextColumn::make('normalized_alias')->searchable(),
            TextColumn::make('dictionaryWord.headword')->label('Headword')->searchable()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('alias'),
            TextEntry::make('normalized_alias'),
            TextEntry::make('dictionaryWord.headword')->label('Headword'),
            TextEntry::make('dictionaryWord.meaning')->label('Meaning')->columnSpanFull(),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDictionaryAliases::route('/'),
            'view' => Pages\ViewDictionaryAlias::route('/{record}'),
        ];
    }
}

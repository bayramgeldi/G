<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DictionaryWordResource\Pages;
use App\Models\DictionaryWord;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DictionaryWordResource extends ReadOnlyResource
{
    protected static ?string $model = DictionaryWord::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Dictionary';

    protected static ?string $recordTitleAttribute = 'headword';

    public static function table(Table $table): Table
    {
        return static::readOnlyTable($table, [
            TextColumn::make('id')->sortable(),
            TextColumn::make('headword')->searchable()->sortable(),
            TextColumn::make('normalized_headword')->searchable(),
            TextColumn::make('meaning')->searchable()->limit(70)->wrap(),
            TextColumn::make('source')->searchable()->sortable(),
            TextColumn::make('aliases_count')->counts('aliases')->label('Aliases')->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('headword'),
            TextEntry::make('normalized_headword'),
            TextEntry::make('meaning')->columnSpanFull(),
            TextEntry::make('source'),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDictionaryWords::route('/'),
            'view' => Pages\ViewDictionaryWord::route('/{record}'),
        ];
    }
}

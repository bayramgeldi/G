<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DefinitionResource\Pages;
use App\Models\Definition;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DefinitionResource extends ReadOnlyResource
{
    protected static ?string $model = Definition::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $recordTitleAttribute = 'meaning';

    public static function table(Table $table): Table
    {
        return static::readOnlyTable($table, [
            TextColumn::make('id')->sortable(),
            TextColumn::make('entry.term')->label('Entry')->searchable()->sortable(),
            TextColumn::make('user.email')->label('Author')->searchable(),
            TextColumn::make('meaning')->searchable()->limit(60)->wrap(),
            TextColumn::make('votes_count')->sortable(),
            TextColumn::make('is_hidden')->label('Visibility')->formatStateUsing(fn (bool $state): string => $state ? 'Hidden' : 'Visible')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('entry.term')->label('Entry'),
            TextEntry::make('user.email')->label('Author'),
            TextEntry::make('meaning')->columnSpanFull(),
            TextEntry::make('example')->columnSpanFull(),
            TextEntry::make('votes_count'),
            TextEntry::make('is_hidden')->label('Visibility')->formatStateUsing(fn (bool $state): string => $state ? 'Hidden' : 'Visible')->badge(),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDefinitions::route('/'),
            'view' => Pages\ViewDefinition::route('/{record}'),
        ];
    }
}

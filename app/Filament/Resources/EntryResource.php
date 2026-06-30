<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntryResource\Pages;
use App\Models\Entry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EntryResource extends ReadOnlyResource
{
    protected static ?string $model = Entry::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $recordTitleAttribute = 'term';

    public static function table(Table $table): Table
    {
        return static::readOnlyTable($table, [
            TextColumn::make('id')->sortable(),
            TextColumn::make('term')->searchable()->sortable(),
            TextColumn::make('slug')->searchable(),
            TextColumn::make('user.email')->label('Author')->searchable(),
            TextColumn::make('definitions_count')->counts('definitions')->label('Definitions')->sortable(),
            TextColumn::make('is_hidden')->label('Visibility')->formatStateUsing(fn (bool $state): string => $state ? 'Hidden' : 'Visible')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('term'),
            TextEntry::make('slug'),
            TextEntry::make('normalized_term'),
            TextEntry::make('user.email')->label('Author'),
            TextEntry::make('is_hidden')->label('Visibility')->formatStateUsing(fn (bool $state): string => $state ? 'Hidden' : 'Visible')->badge(),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEntries::route('/'),
            'view' => Pages\ViewEntry::route('/{record}'),
        ];
    }
}

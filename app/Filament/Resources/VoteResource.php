<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoteResource\Pages;
use App\Models\Vote;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VoteResource extends ReadOnlyResource
{
    protected static ?string $model = Vote::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function table(Table $table): Table
    {
        return static::readOnlyTable($table, [
            TextColumn::make('id')->sortable(),
            TextColumn::make('definition.entry.term')->label('Entry')->searchable(),
            TextColumn::make('definition_id')->sortable(),
            TextColumn::make('user.email')->label('Voter')->searchable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('definition_id'),
            TextEntry::make('definition.entry.term')->label('Entry'),
            TextEntry::make('definition.meaning')->label('Definition')->columnSpanFull(),
            TextEntry::make('user.email')->label('Voter'),
            TextEntry::make('created_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVotes::route('/'),
            'view' => Pages\ViewVote::route('/{record}'),
        ];
    }
}

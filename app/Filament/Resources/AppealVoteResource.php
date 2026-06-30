<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppealVoteResource\Pages;
use App\Models\AppealVote;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppealVoteResource extends ReadOnlyResource
{
    protected static ?string $model = AppealVote::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Governance';

    public static function table(Table $table): Table
    {
        return static::readOnlyTable($table, [
            TextColumn::make('id')->sortable(),
            TextColumn::make('appeal_id')->sortable(),
            TextColumn::make('user.email')->label('Voter')->searchable(),
            TextColumn::make('vote')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('appeal_id'),
            TextEntry::make('appeal.status')->label('Appeal status')->badge(),
            TextEntry::make('user.email')->label('Voter'),
            TextEntry::make('vote')->badge(),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppealVotes::route('/'),
            'view' => Pages\ViewAppealVote::route('/{record}'),
        ];
    }
}

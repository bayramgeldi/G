<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppealResource\Pages;
use App\Models\Appeal;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AppealResource extends ReadOnlyResource
{
    protected static ?string $model = Appeal::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Governance';

    public static function table(Table $table): Table
    {
        return static::readOnlyTable($table, [
            TextColumn::make('id')->sortable(),
            TextColumn::make('user.email')->label('Author')->searchable(),
            TextColumn::make('appealable_type')->label('Type')->searchable(),
            TextColumn::make('appealable_id')->label('Subject ID')->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('restore_votes_count')->sortable(),
            TextColumn::make('keep_hidden_votes_count')->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('user.email')->label('Author'),
            TextEntry::make('appealable_type')->label('Subject type'),
            TextEntry::make('appealable_id')->label('Subject ID'),
            TextEntry::make('status')->badge(),
            TextEntry::make('restore_votes_count'),
            TextEntry::make('keep_hidden_votes_count'),
            TextEntry::make('statement')->columnSpanFull(),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppeals::route('/'),
            'view' => Pages\ViewAppeal::route('/{record}'),
        ];
    }
}

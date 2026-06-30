<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModerationEventResource\Pages;
use App\Models\ModerationEvent;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModerationEventResource extends ReadOnlyResource
{
    protected static ?string $model = ModerationEvent::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Governance';

    public static function table(Table $table): Table
    {
        return static::readOnlyTable($table, [
            TextColumn::make('id')->sortable(),
            TextColumn::make('event_type')->searchable()->badge(),
            TextColumn::make('actor.email')->label('Actor')->searchable(),
            TextColumn::make('actor_type')->badge(),
            TextColumn::make('subject_type')->label('Subject type')->searchable(),
            TextColumn::make('subject_id')->label('Subject ID')->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('event_type')->badge(),
            TextEntry::make('actor.email')->label('Actor'),
            TextEntry::make('actor_type'),
            TextEntry::make('subject_type')->label('Subject type'),
            TextEntry::make('subject_id')->label('Subject ID'),
            TextEntry::make('reason'),
            KeyValueEntry::make('details')->columnSpanFull(),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModerationEvents::route('/'),
            'view' => Pages\ViewModerationEvent::route('/{record}'),
        ];
    }
}

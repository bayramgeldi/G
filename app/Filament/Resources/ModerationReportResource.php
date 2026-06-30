<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModerationReportResource\Pages;
use App\Models\ModerationReport;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModerationReportResource extends ReadOnlyResource
{
    protected static ?string $model = ModerationReport::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Governance';

    public static function table(Table $table): Table
    {
        return static::readOnlyTable($table, [
            TextColumn::make('id')->sortable(),
            TextColumn::make('user.email')->label('Reporter')->searchable(),
            TextColumn::make('reportable_type')->label('Type')->searchable(),
            TextColumn::make('reportable_id')->label('Subject ID')->sortable(),
            TextColumn::make('reason')->searchable()->badge(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('id'),
            TextEntry::make('user.email')->label('Reporter'),
            TextEntry::make('reportable_type')->label('Subject type'),
            TextEntry::make('reportable_id')->label('Subject ID'),
            TextEntry::make('reason')->badge(),
            TextEntry::make('note')->columnSpanFull(),
            TextEntry::make('created_at')->dateTime(),
            TextEntry::make('updated_at')->dateTime(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModerationReports::route('/'),
            'view' => Pages\ViewModerationReport::route('/{record}'),
        ];
    }
}

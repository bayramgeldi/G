<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EntryResource\Pages;
use App\Models\Entry;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Js;

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
            ImageColumn::make('social_image_preview')
                ->label('Social image')
                ->state(fn (Entry $record): ?string => $record->socialImageUrl())
                ->imageWidth(120)
                ->imageHeight(63)
                ->checkFileExistence(false),
            TextColumn::make('og_image_generated_at')->label('Social image generated')->dateTime()->sortable(),
            TextColumn::make('og_image_error')->label('Social image error')->limit(40)->wrap(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([
            static::copyPublicUrlAction(),
            static::shareOnXAction(),
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
            ImageEntry::make('social_image_preview')
                ->label('Social image preview')
                ->state(fn (Entry $record): ?string => $record->socialImageUrl())
                ->imageWidth(600)
                ->imageHeight(315)
                ->checkFileExistence(false)
                ->columnSpanFull(),
            TextEntry::make('og_image_path')->label('Social image path')->copyable(),
            TextEntry::make('social_image_url')
                ->label('Social image URL')
                ->state(fn (Entry $record): ?string => $record->socialImageUrl())
                ->copyable()
                ->url(fn (Entry $record): ?string => $record->socialImageUrl(), shouldOpenInNewTab: true),
            TextEntry::make('og_image_generated_at')->label('Social image generated')->dateTime(),
            TextEntry::make('og_image_error')->label('Social image error')->columnSpanFull(),
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

    public static function copyPublicUrlAction(): Action
    {
        return Action::make('copyPublicUrl')
            ->label(__('app.copy_link'))
            ->icon(Heroicon::OutlinedClipboard)
            ->color('gray')
            ->hidden(fn (Entry $record): bool => $record->is_hidden)
            ->alpineClickHandler(fn (Entry $record): string => sprintf(
                'navigator.clipboard.writeText(%s).then(() => new FilamentNotification().title(%s).success().send())',
                Js::from($record->publicUrl()),
                Js::from(__('app.link_copied')),
            ));
    }

    public static function shareOnXAction(): Action
    {
        return Action::make('shareOnX')
            ->label(__('app.share_on_x'))
            ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
            ->hidden(fn (Entry $record): bool => $record->is_hidden)
            ->url(fn (Entry $record): string => $record->xShareUrl(), shouldOpenInNewTab: true);
    }
}

<?php

namespace App\Filament\Resources\EntryResource\Pages;

use App\Filament\Resources\EntryResource;
use App\Models\Entry;
use App\Services\EntrySocialImageGenerator;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewEntry extends ViewRecord
{
    protected static string $resource = EntryResource::class;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('regenerateSocialImage')
                ->label('Regenerate social image')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('warning')
                ->requiresConfirmation()
                ->hidden(fn (Entry $record): bool => $record->is_hidden)
                ->disabled(fn (Entry $record): bool => ! $record->visibleDefinitions()->exists())
                ->tooltip(fn (Entry $record): ?string => $record->visibleDefinitions()->exists()
                    ? null
                    : 'A visible definition is required before generating a social image.')
                ->action(function (EntrySocialImageGenerator $generator): void {
                    $generator->generate($this->record);
                    $this->record->refresh();

                    if ($this->record->og_image_error) {
                        Notification::make()
                            ->title('Social image could not be regenerated')
                            ->body($this->record->og_image_error)
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Social image regenerated')
                        ->success()
                        ->send();
                }),
            EntryResource::copyPublicUrlAction(),
            EntryResource::shareOnXAction(),
        ];
    }
}

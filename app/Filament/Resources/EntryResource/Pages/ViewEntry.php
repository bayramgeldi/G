<?php

namespace App\Filament\Resources\EntryResource\Pages;

use App\Filament\Resources\EntryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewEntry extends ViewRecord
{
    protected static string $resource = EntryResource::class;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            EntryResource::copyPublicUrlAction(),
            EntryResource::shareOnXAction(),
        ];
    }
}

<?php

namespace Utyemma\SaasPro\Filament\Resources\Plans\TimelineResource\Pages;

use Utyemma\SaasPro\Filament\Resources\Plans\TimelineResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTimelines extends ListRecords
{
    protected static string $resource = TimelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

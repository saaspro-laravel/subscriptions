<?php

namespace Utyemma\SaasPro\Filament\Resources\Plans\PlanResource\Pages;

use Utyemma\SaasPro\Filament\Resources\Plans\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

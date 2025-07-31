<?php

namespace SaasPro\Subscriptions\Filament\Resources\Plans\PlanResource\Pages;

use SaasPro\Subscriptions\Filament\Resources\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

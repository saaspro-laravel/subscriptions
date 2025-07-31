<?php

namespace SaasPro\Subscriptions\Filament\PlanResource\Pages;

use SaasPro\Subscriptions\Filament\Resources\Plans\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePlan extends CreateRecord
{
    protected static string $resource = PlanResource::class;
}

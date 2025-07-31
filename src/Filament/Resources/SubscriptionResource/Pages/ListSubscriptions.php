<?php

namespace SaasPro\Subscriptions\Filament\Resources\Billing\SubscriptionResource\Pages;

use SaasPro\Subscriptions\Filament\Resources\Billing\SubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

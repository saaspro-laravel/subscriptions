<?php

namespace SaasPro\Subscriptions\Filament\Resources\Billing\SubscriptionResource\Pages;

use SaasPro\Subscriptions\Filament\Resources\Billing\SubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

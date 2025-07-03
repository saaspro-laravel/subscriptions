<?php

namespace Utyemma\SaasPro\Filament\Resources\Billing\SubscriptionResource\Pages;

use Utyemma\SaasPro\Filament\Resources\Billing\SubscriptionResource;
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

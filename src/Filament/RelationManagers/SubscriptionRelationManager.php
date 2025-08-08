<?php

namespace SaasPro\Subscriptions\Filament\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use SaasPro\Subscriptions\DataObjects\SubscriptionData;
use SaasPro\Subscriptions\Models\Plan;
use SaasPro\Subscriptions\Models\PlanPrice;
use SaasPro\Subscriptions\Models\Subscription;

class SubscriptionRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    public function form(Form $form): Form {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->disabled(fn(string $operation) => $operation === 'edit')
                    ->default('default'),
                Forms\Components\Select::make('plan_id')
                    ->relationship('plan', 'name')
                    ->afterStateUpdated(function (Set $set, $state) {
                        if($plan = Plan::find($state)) {
                            $set('grace_period', $plan->grace_period);    
                            $set('trial_period', $plan->trial_period);    
                        }
                    })
                    ->live()->native()
                    ->searchable()->preload()->required(),
                Forms\Components\Select::make('price_id')
                    ->label('Timeline')
                    ->options(function ($record, Get $get) {
                        if(!$plan_id = $get('plan_id')) return [];
                        return PlanPrice::where('plan_id', $plan_id)->get()->mapWithKeys(function ($price) {
                            return [ $price->id => $price->timeline->label()];
                        });
                    })
                    ->getOptionLabelUsing(fn($timeline) => $timeline->label())
                    ->native()
                    ->searchable()
                    ->preload()
                    ->required(),
                DateTimePicker::make('starts_at')
                    ->default(now())
                    ->nullable(),
                Forms\Components\TextInput::make('grace_period'),
                Forms\Components\TextInput::make('trial_period'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(''),
                Tables\Columns\TextColumn::make('reference')
                    ->copyable(),
                Tables\Columns\TextColumn::make('plan')
                    ->getStateUsing(fn($record) => "{$record->plan->name} {$record->price->timeline->label()}"),
                IconColumn::make('auto_renews')
                    ->boolean(),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->dateTime()
                    ->searchable(),
                Tables\Columns\TextColumn::make('grace_ends_at')
                    ->dateTime()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn($record) => $record->status->color()),
            ])
            ->modifyQueryUsing(fn(Builder $query) => $query->latest())
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->action(function (Set $set, array $data) {
                        $subscriptionData = SubscriptionData::make($data);
                        $subscriptionData->setPlan($data['plan_id'], $data['price_id']);
                        $subscriptionData->setSubscriber($this->ownerRecord);
                        $subscriptionData->newSubscription()->save();
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->ended()),
                Action::make('Cancel')
                    ->modalWidth('md')
                    ->visible(fn($record) => $record->active())
                    ->form([
                        Checkbox::make('cancel_immediately')
                            ->helperText('If checked, the subscription will be cancelled immediately and will be marked as expired. If unchecked, it will expire at the end of the current period.')
                    ])
                    ->action(function (Subscription $subscription, array $data) {
                        $subscription->cancel($data['cancel_immediately']);
                    }),
                Action::make('Resume')
                    ->modalWidth('md')
                    ->visible(fn($record) => $record->canResume())
                    ->action(function (Subscription $subscription) {
                        $subscription->resume();
                    }),
                ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace SaasPro\Subscriptions\Filament\Resources;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Get;
use SaasPro\Filament\RelationManagers\HistoryRelationManager;
use SaasPro\Subscriptions\Filament\Resources\SubscriptionResource\Pages;
use SaasPro\Subscriptions\Models\PlanPrice;
use SaasPro\Subscriptions\Models\Subscription;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use SaasPro\Filament\Tables\Columns\StatusColumn;
use SaasPro\Subscriptions\Models\Plan;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Subscriptions';

    public static function form(Form $form): Form
    {
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(''),
                Tables\Columns\TextColumn::make('subscriber_title')
                    ->label('Subscriber'),
                Tables\Columns\TextColumn::make('reference')
                    ->copyable(),
                Tables\Columns\TextColumn::make('plan')
                    ->getStateUsing(fn($record) => "{$record->plan->name} {$record->price->timeline->label()}"),
                IconColumn::make('auto_renews')
                    ->boolean(),
                StatusColumn::make('status'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->searchable(),
                TextColumn::make('trial_ends_at')
                    ->dateTime()
                    ->searchable(),
                TextColumn::make('grace_ends_at')
                    ->dateTime()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            HistoryRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}

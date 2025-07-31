<?php

namespace SaasPro\Subscriptions\Filament\Resources\Billing;

use SaasPro\Subscriptions\Filament\Resources\Billing\SubscriptionResource\Pages;
use SaasPro\Subscriptions\Filament\Resources\Billing\SubscriptionResource\RelationManagers;
use SaasPro\Subscriptions\Models\Subscription;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Utyemma\SaasPro\Filament\Tables\Columns\StatusColumn;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationGroup = 'Billing';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable('users.name'),
                TextColumn::make('plan.name')
                    ->searchable('plan.name'),
                TextColumn::make('planPrice.timeline.name')
                    ->searchable('planPrice.timeline.name'),
                IconColumn::make('auto_renews')
                    ->boolean(),
                StatusColumn::make('status'),
                TextColumn::make('expires_at')
                    ->formatStateUsing(fn($state) => $state->format('jS F Y'))
                    ->searchable(),
                TextColumn::make('trial_ends_at')
                    ->formatStateUsing(fn($state) => $state->format('jS F Y'))
                    ->searchable(),
                TextColumn::make('created_at')
                    ->formatStateUsing(fn($state) => $state->format('jS F Y'))
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
            //
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

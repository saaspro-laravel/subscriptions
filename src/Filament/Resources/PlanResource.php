<?php

namespace SaasPro\Subscriptions\Filament\Resources;

use SaasPro\Filament\Tables\Columns\StatusColumn;
use SaasPro\Subscriptions\Filament\PlanResource\Pages\CreatePlan;
use SaasPro\Subscriptions\Filament\PlanResource\Pages\ListPlans;
use SaasPro\Subscriptions\Filament\Resources\PlanResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use SaasPro\Subscriptions\Filament\Resources\Plans\PlanResource\Pages\EditPlan;
use SaasPro\Subscriptions\Models\Plan;

class PlanResource extends Resource {
    
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Subscriptions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->columnSpanFull()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('trial_period')
                    ->numeric()
                    ->maxLength(255)
                    ->default(7),
                Forms\Components\TextInput::make('grace_period')
                    ->numeric()
                    ->maxLength(255)
                    ->default(7),
                Forms\Components\TextInput::make('sort')
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\Toggle::make('status')
                    ->default(true)
                    ->required(),
                Forms\Components\Toggle::make('is_popular')
                        ->required(),
                Forms\Components\Toggle::make('is_default')
                    ->required(),
                Forms\Components\Toggle::make('is_free')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextInputColumn::make('sort')
                    ->width('5px')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_popular')
                    ->boolean(),
                Tables\Columns\TextColumn::make('trial_period')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grace_period')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_free')
                    ->boolean(),
                ToggleColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            RelationManagers\PricesRelationManager::class,
            RelationManagers\FeaturesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlans::route('/'),
            'create' => CreatePlan::route('/create'),
            'edit' => EditPlan::route('/{record}/edit'),
        ];
    }
}

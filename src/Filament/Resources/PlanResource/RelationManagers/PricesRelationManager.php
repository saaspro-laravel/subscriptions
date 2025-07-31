<?php

namespace SaasPro\Subscriptions\Filament\Resources\Plans\PlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use SaasPro\Filament\Forms\Components\SelectStatus;
use Utyemma\SaasPro\Filament\Tables\Columns\StatusColumn;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('timeline')
                    ->relationship('timeline', 'name')
                    ->native(false),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\TextInput::make('provider_id')
                    ->required()
                    ->maxLength(255),
                SelectStatus::make('status'),
                Repeater::make('countries')
                    ->relationship('prices')
                    ->label("Country Specific Prices")
                    ->columnSpanFull()
                    ->columns([
                        'md' => 3,
                    ])
                    ->schema([
                        Forms\Components\Select::make('country_id')
                            ->required()
                            ->relationship('country', 'name'),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('provider_id')
                            ->required()
                            ->maxLength(255),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // ->recordTitleAttribute('plan_id')
            ->columns([
                Tables\Columns\TextColumn::make('plan.name'),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric(),
                Tables\Columns\TextColumn::make('provider_id')
                    ->numeric(),
                Tables\Columns\TextColumn::make('timeline.name'),
                StatusColumn::make('status')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

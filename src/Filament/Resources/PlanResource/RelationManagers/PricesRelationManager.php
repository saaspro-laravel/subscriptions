<?php

namespace SaasPro\Subscriptions\Filament\Resources\PlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use SaasPro\Enums\Timelines;
use SaasPro\Filament\Forms\Components\SelectStatus;
use SaasPro\Filament\Tables\Columns\StatusColumn;

class PricesRelationManager extends RelationManager
{
    protected static string $relationship = 'prices';

    public function form(Form $form): Form
    {
        // dd($form->getRecord()->prices);
        return $form
            ->schema([
                Forms\Components\Select::make('timeline')
                    ->required()
                    ->options(Timelines::options())
                    ->native(false),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->maxLength(255),
                Forms\Components\TextInput::make('provider_id')
                    ->maxLength(255),
                SelectStatus::make('status')
                    ->native(false)
                    ->required(),
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
                            ->nullable()
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
                Tables\Columns\TextColumn::make('timeline')
                    ->formatStateUsing(fn($state) => $state->label()),
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

<?php

namespace SaasPro\Subscriptions\Filament\Resources\PlanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use SaasPro\Enums\Timelines;
use SaasPro\Features\Models\Feature;

class FeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'features';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('feature_id')
                    ->relationship('feature', 'name')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\TextInput::make('limit')
                    ->numeric(),
                Forms\Components\TextInput::make('reset_period'),
                Forms\Components\Select::make('reset_interval')
                    ->native(false)
                    ->options(Timelines::options()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('feature_id')
            ->columns([
                Tables\Columns\TextColumn::make('feature.name'),
                Tables\Columns\TextColumn::make('limit'),
                Tables\Columns\TextColumn::make('reset_period'),
                Tables\Columns\TextColumn::make('reset_interval')
                    ->formatStateUsing(fn($state) => $state->label()),
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

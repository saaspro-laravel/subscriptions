<?php

namespace SaasPro\Subscriptions\Filament\Resources\PlanResource\RelationManagers;

use Carbon\CarbonInterval;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
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
                Tables\Columns\TextColumn::make('resets')
                            ->state(function(Model $record) {
                                $period = $record->reset_period;
                                $interval = $record->reset_interval;

                                if(!$period || !$interval) return '';
                                return "Every ".CarbonInterval::make($period, $interval->value);
                            }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label("Add Feature"),
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

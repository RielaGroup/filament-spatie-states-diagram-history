<?php

namespace RielaGroup\FilamentSpatieStatesDiagramHistory\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use RielaGroup\FilamentSpatieStatesDiagramHistory\Models\ModelState;
use RielaGroup\FilamentSpatieStatesDiagramHistory\Support\StateLabelResolver;

class StateHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'stateHistory';

    protected static ?string $recordTitleAttribute = 'state_to';

    public static function getBadge($ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->stateHistory->count();

        return $count > 0 ? (string) $count : null;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(6)
            ->components([
                \Filament\Forms\Components\Select::make('user_id')
                    ->label(__('Changed By'))
                    ->relationship('user', 'name')
                    ->disabled()
                    ->columnSpan(3),
                \Filament\Forms\Components\DateTimePicker::make('created_at')
                    ->columnSpan(3)
                    ->disabled(),
                \Filament\Forms\Components\TextInput::make('state_from')
                    ->disabled()
                    ->columnSpan(3),
                \Filament\Forms\Components\TextInput::make('state_to')
                    ->disabled()
                    ->columnSpan(3),
                \Filament\Forms\Components\Textarea::make('comment')
                    ->disabled()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                TextColumn::make('user.name')
                    ->label(__('Changed By')),
                TextColumn::make('state_from')
                    ->label(__('From'))
                    ->formatStateUsing(fn (ModelState $record) => $record->getStateFromLabel())
                    ->badge()
                    ->color(fn (ModelState $record) => StateLabelResolver::toFilamentColor($record->getStateFromColor())),
                TextColumn::make('state_to')
                    ->label(__('To'))
                    ->formatStateUsing(fn (ModelState $record) => $record->getStateToLabel())
                    ->badge()
                    ->color(fn (ModelState $record) => StateLabelResolver::toFilamentColor($record->getStateToColor())),
                TextColumn::make('comment')
                    ->label(__('Comment'))
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label(__('Date')),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

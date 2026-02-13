<?php

namespace RielaGroup\FilamentSpatieStatesDiagramHistory\Actions;

use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

class StateHistoryAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'stateHistory';
    }

    /**
     * Table or header action that opens a slideover with state history timeline.
     */
    public static function makeStateHistory(): static
    {
        return static::make()
            ->label(__('History'))
            ->modalDescription(__('The changed states for this record'))
            ->icon('heroicon-o-clock')
            ->slideOver()
            ->modalSubmitAction(false)
            ->modalIcon('heroicon-o-clock')
            ->disabled(fn ($record) => ! method_exists($record, 'stateHistory') || $record->stateHistory->count() === 0)
            ->badge(fn ($record) => method_exists($record, 'stateHistory') && $record->stateHistory->count() > 0 ? $record->stateHistory->count() : null)
            ->modalWidth('4xl')
            ->modalContent(function ($record) {
                if (method_exists($record, 'load') && method_exists($record, 'stateHistory')) {
                    $record->load('stateHistory');
                }
                $records = method_exists($record, 'stateHistory') ? $record->stateHistory()->orderBy('created_at')->get() : collect();

                return new HtmlString(
                    Blade::render('filament-spatie-states::state-history', [
                        'records' => $records,
                    ])
                );
            });
    }
}

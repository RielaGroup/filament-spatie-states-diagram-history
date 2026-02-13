<?php

namespace RielaGroup\FilamentSpatieStatesDiagramHistory;

use Filament\Actions\Action;
use Illuminate\Contracts\View\View;
use Spatie\ModelStates\State;

class StateDiagramAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'stateDiagram';
    }

    /**
     * Configure a state diagram action for use on Edit/View record pages.
     *
     * @param  string  $stateField  The model attribute that holds the state (default: 'state')
     */
    public static function makeForRecord(string $stateField = 'state'): static
    {
        return static::make()
            ->label('State diagram')
            ->icon('heroicon-o-squares-2x2')
            ->color('gray')
            ->modalCancelAction(false)
            ->modalWidth('5xl')
            ->slideOver()
            ->modalSubmitAction(false)
            ->modalHeading(fn ($record) => 'State diagram'.($record && method_exists($record, 'getKey') ? ' for record' : ''))
            ->visible(fn ($record) => static::recordHasStateCast($record, $stateField))
            ->modalContent(function ($record) use ($stateField): View {
                if (method_exists($record, 'load') && method_exists($record, 'stateHistory')) {
                    $record->load('stateHistory');
                }
                $service = app(StateDiagramService::class);
                $diagram = $service->build($record, $stateField);

                return view('filament-spatie-states::state-diagram', [
                    'record' => $record,
                    'diagram' => $diagram,
                ]);
            });
    }

    protected static function recordHasStateCast(?object $record, string $stateField): bool
    {
        if ($record === null) {
            return false;
        }

        if (! method_exists($record, 'getCasts')) {
            return false;
        }

        $casts = $record->getCasts();

        if (! isset($casts[$stateField]) || ! is_string($casts[$stateField])) {
            return false;
        }

        return is_subclass_of($casts[$stateField], State::class);
    }
}

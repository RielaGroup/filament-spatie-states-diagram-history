<?php

namespace RielaGroup\FilamentSpatieStatesDiagramHistory\Listeners;

use Illuminate\Support\Facades\Auth;
use RielaGroup\FilamentSpatieStatesDiagramHistory\Models\ModelState;
use Spatie\ModelStates\Events\StateChanged;

class StoreModelStateListener
{
    public function handle(StateChanged $event): void
    {
        $model = $event->model;
        $modelId = $model->getKey();
        $modelClass = get_class($model);

        if ($modelId === null) {
            return;
        }

        $userId = $this->resolveUserId($event);

        $modelStateClass = config('filament-spatie-states.model_state_class', ModelState::class);

        $modelStateClass::create([
            'user_id' => $userId,
            'state_from' => get_class($event->initialState),
            'state_to' => get_class($event->finalState),
            'comment' => method_exists($event->transition, 'getComment') ? $event->transition->getComment() : '',
            'model_type' => $modelClass,
            'model_id' => (string) $modelId,
        ]);
    }

    protected function resolveUserId(StateChanged $event): ?int
    {
        $resolver = config('filament-spatie-states.user_id_resolver');
        if (is_callable($resolver)) {
            $id = $resolver($event);
            if ($id !== null) {
                return (int) $id;
            }
        }

        $id = Auth::id();

        return $id !== null ? (int) $id : null;
    }
}

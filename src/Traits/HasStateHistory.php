<?php

namespace RielaGroup\FilamentSpatieStatesDiagramHistory\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use RielaGroup\FilamentSpatieStatesDiagramHistory\Models\ModelState;

trait HasStateHistory
{
    public function stateHistory(): HasMany
    {
        $modelClass = config('filament-spatie-states.model_state_class', ModelState::class);

        return $this->hasMany($modelClass, 'model_id', $this->getKeyName())
            ->where('model_type', static::class)
            ->orderByDesc('created_at');
    }
}

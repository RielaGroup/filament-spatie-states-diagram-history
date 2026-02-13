<?php

namespace RielaGroup\FilamentSpatieStatesDiagramHistory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RielaGroup\FilamentSpatieStatesDiagramHistory\Support\StateLabelResolver;

class ModelState extends Model
{
    protected $fillable = [
        'user_id',
        'state_from',
        'state_to',
        'comment',
        'model_type',
        'model_id',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = config('filament-spatie-states.table_name', 'model_states');
        parent::__construct($attributes);
    }

    public function user(): BelongsTo
    {
        $userClass = config('filament-spatie-states.user_model', \App\Models\User::class);

        return $this->belongsTo($userClass, 'user_id');
    }

    public function modelRecord(): BelongsTo
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    /**
     * Resolve the "state_to" as a Spatie State instance for label/color (if applicable).
     */
    public function getStateToInstance(): ?object
    {
        $stateClass = '\\'.ltrim($this->state_to, '\\');

        if (! class_exists($stateClass)) {
            return null;
        }

        if (! is_subclass_of($stateClass, \Spatie\ModelStates\State::class)) {
            return null;
        }

        try {
            return new $stateClass($this);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Human-readable label for state_to (uses StateLabelResolver).
     */
    public function getStateToLabel(): string
    {
        return StateLabelResolver::getLabel($this->state_to, $this->model_type);
    }

    /**
     * Filament badge color for state_to (uses StateLabelResolver).
     */
    public function getStateToColor(): string
    {
        return StateLabelResolver::getColor($this->state_to, $this->model_type);
    }

    public function getStateFromLabel(): string
    {
        return StateLabelResolver::getLabel($this->state_from, $this->model_type);
    }

    public function getStateFromColor(): string
    {
        return StateLabelResolver::getColor($this->state_from, $this->model_type);
    }
}

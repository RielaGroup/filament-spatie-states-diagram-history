<?php

namespace RielaGroup\FilamentSpatieStatesDiagramHistory\Support;

use Spatie\ModelStates\State;

class StateLabelResolver
{
    public static function getLabel(string $stateClass, string $modelClass): string
    {
        $stateClass = '\\'.ltrim($stateClass, '\\');

        if (! class_exists($stateClass)) {
            return class_basename($stateClass);
        }

        try {
            $model = class_exists($modelClass) ? new $modelClass : null;
            $instance = $model ? new $stateClass($model) : new $stateClass(new \stdClass);
            if (method_exists($instance, 'label')) {
                return $instance->label();
            }
        } catch (\Throwable) {
            // fall through to basename
        }

        return class_basename($stateClass);
    }

    public static function getColor(string $stateClass, string $modelClass): string
    {
        $stateClass = '\\'.ltrim($stateClass, '\\');

        if (! class_exists($stateClass)) {
            return 'gray';
        }

        try {
            $model = class_exists($modelClass) ? new $modelClass : null;
            $instance = $model ? new $stateClass($model) : new $stateClass(new \stdClass);
            if (method_exists($instance, 'color')) {
                return $instance->color();
            }
        } catch (\Throwable) {
            // fall through
        }

        return 'gray';
    }

    /**
     * Map state color names to Filament badge colors.
     */
    public static function toFilamentColor(string $colorName): string
    {
        $map = [
            'gray' => 'gray',
            'success' => 'success',
            'danger' => 'danger',
            'warning' => 'warning',
            'green' => 'success',
            'primary' => 'primary',
            'info' => 'info',
        ];

        return $map[strtolower($colorName)] ?? 'gray';
    }
}

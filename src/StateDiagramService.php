<?php

namespace RielaGroup\FilamentSpatieStatesDiagramHistory;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\State;

class StateDiagramService
{
    /**
     * Build diagram data from a model's Spatie state field.
     *
     * @return array{nodes: array<int, array{id: string, morphClass: string, label: string, color: string}>, edges: array<int, array{from: string, to: string}>, currentState: string|null, pathNodeIds: array<int, string>, historyCount: int, historyLoaded: bool, mermaidCode: string}
     */
    public function build(Model $model, string $stateField = 'state'): array
    {
        $stateClass = $this->resolveStateCastClass($model, $stateField);

        if ($stateClass === null) {
            return [
                'nodes' => [],
                'edges' => [],
                'currentState' => null,
                'pathNodeIds' => [],
                'historyCount' => 0,
                'historyLoaded' => false,
                'mermaidCode' => '',
            ];
        }

        $config = $stateClass::config();
        $stateMapping = $stateClass::getStateMapping();

        $morphToShortId = [];
        $nodes = [];

        foreach ($stateMapping as $morphClass => $class) {
            if (! is_string($class) || ! class_exists($class) || ! is_subclass_of($class, $stateClass)) {
                continue;
            }

            $shortId = $this->morphToShortId($morphClass, $class);
            $morphToShortId[$morphClass] = $shortId;

            $label = $shortId;
            $color = 'gray';

            try {
                $instance = new $class($model);
                if (method_exists($instance, 'label')) {
                    $label = $instance->label();
                }
                if (method_exists($instance, 'color')) {
                    $color = $instance->color();
                }
            } catch (\Throwable) {
                // Keep defaults if instantiation fails
            }

            $nodes[] = [
                'id' => $shortId,
                'morphClass' => $morphClass,
                'label' => $label,
                'color' => $color,
            ];
        }

        $defaultStateShortId = $config->defaultStateClass !== null
            ? class_basename($config->defaultStateClass)
            : null;

        $edges = [];
        foreach ($config->allowedTransitions as $transitionKey => $value) {
            if (! str_contains($transitionKey, '->')) {
                continue;
            }
            [$fromMorph, $toMorph] = explode('->', $transitionKey, 2);
            $fromId = $morphToShortId[$fromMorph] ?? $this->morphToShortIdFromClass($fromMorph);
            $toId = $morphToShortId[$toMorph] ?? $this->morphToShortIdFromClass($toMorph);
            if ($fromId === null || $toId === null) {
                continue;
            }
            if ($defaultStateShortId !== null && $toId === $defaultStateShortId) {
                continue;
            }
            $edges[] = ['from' => $fromId, 'to' => $toId];
        }

        $currentState = null;
        try {
            $stateValue = $model->{$stateField};
            if ($stateValue instanceof State) {
                $currentState = $stateValue->getValue();
            }
        } catch (\Throwable) {
            // Leave currentState null
        }

        $currentShortId = $morphToShortId[$currentState] ?? ($currentState !== null ? $this->morphToShortIdFromClass($currentState) : null);

        $pathResult = $this->resolvePathNodeIds($model);
        $pathNodeIds = $pathResult['pathNodeIds'];
        $historyCount = $pathResult['historyCount'];
        $historyLoaded = $pathResult['historyLoaded'];

        $mermaidCode = $this->buildMermaid($nodes, $edges, $currentShortId, $pathNodeIds);

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'currentState' => $currentShortId,
            'pathNodeIds' => $pathNodeIds,
            'historyCount' => $historyCount,
            'historyLoaded' => $historyLoaded,
            'mermaidCode' => $mermaidCode,
        ];
    }

    /**
     * @return array{pathNodeIds: array<int, string>, historyCount: int, historyLoaded: bool}
     */
    protected function resolvePathNodeIds(Model $model): array
    {
        $empty = [
            'pathNodeIds' => [],
            'historyCount' => 0,
            'historyLoaded' => false,
        ];

        if (! method_exists($model, 'stateHistory')) {
            return $empty;
        }

        try {
            $historyLoaded = true;
            $history = $model->relationLoaded('stateHistory')
                ? $model->stateHistory->sortBy('created_at')->values()
                : $model->stateHistory()->orderBy('created_at')->get();
        } catch (\Throwable) {
            return [
                'pathNodeIds' => [],
                'historyCount' => 0,
                'historyLoaded' => true,
            ];
        }

        $historyCount = $history->count();

        if ($history->isEmpty()) {
            return [
                'pathNodeIds' => [],
                'historyCount' => 0,
                'historyLoaded' => $historyLoaded,
            ];
        }

        $pathNodeIds = [];

        foreach ($history as $index => $historyRecord) {
            $fromClass = $this->normalizeStateClass($historyRecord->state_from);
            $toClass = $this->normalizeStateClass($historyRecord->state_to);
            $fromShortId = $this->morphToShortIdFromClass($fromClass);
            $toShortId = $this->morphToShortIdFromClass($toClass);

            if ($index === 0 && $fromShortId !== null) {
                $pathNodeIds[] = $fromShortId;
            }
            if ($toShortId !== null) {
                $pathNodeIds[] = $toShortId;
            }
        }

        return [
            'pathNodeIds' => array_values(array_unique($pathNodeIds)),
            'historyCount' => $historyCount,
            'historyLoaded' => $historyLoaded,
        ];
    }

    /**
     * @return class-string<State>|null
     */
    protected function resolveStateCastClass(Model $model, string $stateField): ?string
    {
        $casts = $model->getCasts();

        if (! isset($casts[$stateField])) {
            return null;
        }

        $cast = $casts[$stateField];

        if (! is_string($cast) || ! is_subclass_of($cast, State::class)) {
            return null;
        }

        return $cast;
    }

    protected function morphToShortId(string $morphClass, string $stateClass): string
    {
        if (class_exists($morphClass)) {
            return class_basename($morphClass);
        }

        return class_basename($stateClass);
    }

    protected function morphToShortIdFromClass(?string $morphOrClass): ?string
    {
        if ($morphOrClass === null || $morphOrClass === '') {
            return null;
        }

        $normalized = $this->normalizeStateClass($morphOrClass);
        if (class_exists($normalized)) {
            return class_basename($normalized);
        }

        return class_basename(str_contains($normalized, '\\') ? substr($normalized, strrpos($normalized, '\\') + 1) : $normalized);
    }

    protected function normalizeStateClass(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);
        $value = ltrim($value, '\\');

        return str_replace('\\\\', '\\', $value);
    }

    /**
     * @return array{fill: string, stroke: string}
     */
    protected function stateColorToMermaidStyle(string $colorName): array
    {
        $map = [
            'gray' => ['fill' => '#e5e7eb', 'stroke' => '#9ca3af'],
            'success' => ['fill' => '#d1fae5', 'stroke' => '#10b981'],
            'danger' => ['fill' => '#fee2e2', 'stroke' => '#ef4444'],
            'warning' => ['fill' => '#fef3c7', 'stroke' => '#f59e0b'],
            'green' => ['fill' => '#d1fae5', 'stroke' => '#059669'],
            'primary' => ['fill' => '#dbeafe', 'stroke' => '#3b82f6'],
            'info' => ['fill' => '#e0e7ff', 'stroke' => '#6366f1'],
        ];

        return $map[strtolower($colorName)] ?? $map['gray'];
    }

    /**
     * @param  array<int, string>  $pathNodeIds
     * @return array<int, array{from: string, to: string}>
     */
    protected function pathEdgesFromPathNodeIds(array $pathNodeIds): array
    {
        $pathEdges = [];
        for ($i = 0; $i < count($pathNodeIds) - 1; $i++) {
            $pathEdges[] = ['from' => $pathNodeIds[$i], 'to' => $pathNodeIds[$i + 1]];
        }

        return $pathEdges;
    }

    /**
     * @param  array<int, array{id: string, label: string, color: string}>  $nodes
     * @param  array<int, array{from: string, to: string}>  $edges
     * @param  array<int, string>  $pathNodeIds
     */
    protected function buildMermaid(array $nodes, array $edges, ?string $currentStateId, array $pathNodeIds = []): string
    {
        $pathColor = config('filament-spatie-states.path_stroke_color', '#3CB5E3');
        $pathWidth = config('filament-spatie-states.path_stroke_width', 4);
        $nonPathColor = config('filament-spatie-states.non_path_stroke_color', '#9ca3af');
        $nonPathWidth = config('filament-spatie-states.non_path_stroke_width', 1);

        $lines = ['flowchart TB'];

        $safeId = static function (string $id): string {
            return preg_replace('/[^a-zA-Z0-9_]/', '_', $id);
        };

        foreach ($nodes as $node) {
            $id = $safeId($node['id']);
            $label = str_replace('"', '#quot;', $node['label']);
            $lines[] = sprintf('    %s["%s"]', $id, $label);
        }

        $pathEdges = $this->pathEdgesFromPathNodeIds($pathNodeIds);
        $pathEdgeKeys = [];
        foreach ($pathEdges as $e) {
            $pathEdgeKeys[$e['from'].'->'.$e['to']] = true;
        }

        $otherEdges = [];
        foreach ($edges as $edge) {
            $key = $edge['from'].'->'.$edge['to'];
            if (! isset($pathEdgeKeys[$key])) {
                $otherEdges[] = $edge;
            }
        }

        foreach ($pathEdges as $edge) {
            $from = $safeId($edge['from']);
            $to = $safeId($edge['to']);
            $lines[] = sprintf('    %s --> %s', $from, $to);
        }
        foreach ($otherEdges as $edge) {
            $from = $safeId($edge['from']);
            $to = $safeId($edge['to']);
            $lines[] = sprintf('    %s --> %s', $from, $to);
        }

        $pathEdgeCount = count($pathEdges);
        $otherEdgeCount = count($otherEdges);
        if ($pathEdgeCount > 0) {
            $lines[] = '';
            for ($i = 0; $i < $pathEdgeCount; $i++) {
                $lines[] = sprintf('    linkStyle %d stroke:%s,stroke-width:%dpx', $i, $pathColor, $pathWidth);
            }
        }
        if ($otherEdgeCount > 0) {
            $lines[] = '';
            for ($i = 0; $i < $otherEdgeCount; $i++) {
                $lines[] = sprintf('    linkStyle %d stroke:%s,stroke-width:%dpx', $pathEdgeCount + $i, $nonPathColor, $nonPathWidth);
            }
        }

        $uniqueColors = array_unique(array_map(fn (array $n): string => $n['color'], $nodes));
        foreach ($uniqueColors as $colorName) {
            $style = $this->stateColorToMermaidStyle($colorName);
            $className = 'stateColor'.ucfirst(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $colorName)));
            $lines[] = sprintf('    classDef %s fill:%s,stroke:%s,stroke-width:2px', $className, $style['fill'], $style['stroke']);
        }

        foreach ($nodes as $node) {
            $id = $safeId($node['id']);
            $colorName = $node['color'];
            $className = 'stateColor'.ucfirst(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $colorName)));
            $lines[] = sprintf('    class %s %s', $id, $className);
        }

        if ($pathNodeIds !== []) {
            $lines[] = '';
            $lines[] = sprintf('    classDef pathNode stroke:%s,stroke-width:%dpx', $pathColor, $pathWidth);
            foreach ($pathNodeIds as $pathId) {
                $id = $safeId($pathId);
                $lines[] = sprintf('    class %s pathNode', $id);
            }
        }

        if ($currentStateId !== null && $currentStateId !== '') {
            $currentId = $safeId($currentStateId);
            $lines[] = '';
            $lines[] = sprintf('    classDef currentState stroke:%s,stroke-width:%dpx', $pathColor, $pathWidth + 1);
            $lines[] = sprintf('    class %s currentState', $currentId);
        }

        return implode("\n", $lines);
    }
}

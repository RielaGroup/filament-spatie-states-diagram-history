# Filament Spatie States Diagram & History

Filament plugin that adds a **state diagram** (Mermaid flowchart) and **state history** UI for models using [Spatie Laravel Model States](https://github.com/spatie/laravel-model-states).

- **State diagram**: Renders the state machine and highlights the path the current record took (thick blue lines). Non-path transitions are thin grey lines.
- **State history**: Timeline of state changes (who, when, from/to, comment) and an optional relation manager.

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12 (within the versions supported by your chosen Filament release)
- Filament 4 or 5 (`filament/filament` ^4.0 or ^5.0). Filament 5 needs Laravel 11.28+, Livewire 4, and Tailwind 4 in the host app—see the [Filament v5 upgrade guide](https://filamentphp.com/docs/5.x/upgrade-guide).
- `spatie/laravel-model-states` ^2.0

Filament 3 is no longer declared compatible: this package targets the Filament v4+ schema APIs (`Filament\Schemas\Schema` on relation managers). Pin an older package release if you still run Filament 3.

## Installation

```bash
composer require rielagroup/filament-spatie-states-diagram-history
```

Publish config, migration, and optionally the Blade views (so you can customize them):

```bash
php artisan vendor:publish --tag=filament-spatie-states-config
php artisan vendor:publish --tag=filament-spatie-states-migrations
# Optional: publish Blade views to resources/views/vendor/filament-spatie-states/
php artisan vendor:publish --tag=filament-spatie-states-views
```

Run migrations:

```bash
php artisan migrate
```

## Configuration

In `config/filament-spatie-states.php` you can set:

- **register_listener** – Whether to listen to `StateChanged` and store history (default `true`). Set `false` if you record history yourself.
- **table_name** – Table for state history (default `model_states`).
- **model_state_class** – Eloquent model for history records.
- **user_id_resolver** – Callable to resolve `user_id` when there is no authenticated user (e.g. for queue/jobs).
- **path_stroke_color**, **path_stroke_width**, **non_path_stroke_color**, **non_path_stroke_width** – Diagram line styling.
- **mermaid_js_url** – CDN URL for Mermaid.js.
- **user_model** – User model for the "changed by" relation.

## Model setup

1. Use the **HasStateHistory** trait and ensure your model has a Spatie State cast (e.g. `state`).

```php
use RielaGroup\FilamentSpatieStatesDiagramHistory\Traits\HasStateHistory;

class CrewDataFormInstance extends Model
{
    use HasStateHistory;

    protected $casts = [
        'state' => YourStateClass::class,
    ];
}
```

2. The package’s listener will store each transition in `model_states` (or your configured table). If you already have a custom listener, set `register_listener` to `false` and keep using your own `ModelState` (or equivalent) and ensure your model has a `stateHistory()` relation that returns history ordered by `created_at`.

## Filament usage

### State diagram (Edit/View pages)

Add a header action that opens a slide-over with the diagram and path:

```php
use RielaGroup\FilamentSpatieStatesDiagramHistory\StateDiagramAction;

// In getHeaderActions() of your Edit or View page:
StateDiagramAction::makeForRecord('state'),  // 'state' = attribute name
```

### State history action (table or header)

Add a table or header action that opens a slide-over with the state history timeline:

```php
use RielaGroup\FilamentSpatieStatesDiagramHistory\Actions\StateHistoryAction;

// Table actions:
StateHistoryAction::makeStateHistory(),

// Or with a custom label:
StateHistoryAction::makeStateHistory()->label('State History'),
```

### State history relation manager

Add a relation manager to a Resource so state history appears as a relation tab:

```php
use RielaGroup\FilamentSpatieStatesDiagramHistory\RelationManagers\StateHistoryRelationManager;

public static function getRelations(): array
{
    return [
        StateHistoryRelationManager::class,
    ];
}
```

## Existing apps (migration from in-app code)

If you already have:

- A `model_states` table and a custom `ModelStates` model  
- A `StoreModelState` listener  
- `stateHistory()` on models  
- `StateHelper`, `StateDiagramService`, `StateDiagramAction`, `RielaTableActionHelpers::stateHistoryAction()`, `ModelStatesRelationManager`, and Livewire state history view  

you can:

1. Install the package and run the new migration **or** keep your table and set `table_name` to your table name and `model_state_class` to your existing model (your model must implement the same interface: `state_from`, `state_to`, `model_type`, `model_id`, and provide `getStateToLabel()` / `getStateToColor()` or the package’s `ModelState` behaviour).
2. Set `register_listener` to `false` and keep your existing `StoreModelState` listener.
3. Replace in-app usage with the package’s:
   - `StateDiagramAction::makeForRecord('state')`
   - `StateHistoryAction::makeStateHistory()`
   - `StateHistoryRelationManager`
4. Use the `HasStateHistory` trait pointing at your existing `stateHistory()` implementation, or keep your own relation and only use the package for the diagram and actions (the diagram service only needs `stateHistory()` to exist and return records with `state_from` / `state_to` and `created_at`).

## Pushing this package to GitHub

This package is intended to live at [github.com/RielaGroup/filament-spatie-states-diagram-history](https://github.com/RielaGroup/filament-spatie-states-diagram-history). To push it there:

```bash
cd filament-spatie-states-diagram-history
git init
git add .
git commit -m "Initial package: state diagram and state history for Filament + Spatie"
git branch -M main
git remote add origin https://github.com/RielaGroup/filament-spatie-states-diagram-history.git
git push -u origin main
```

To install from the repo in another project (before publishing to Packagist):

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/RielaGroup/filament-spatie-states-diagram-history"
    }
  ],
  "require": {
    "rielagroup/filament-spatie-states-diagram-history": "dev-main"
  }
}
```

## License

MIT.

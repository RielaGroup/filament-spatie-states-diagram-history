<?php

namespace RielaGroup\FilamentSpatieStatesDiagramHistory;

use Illuminate\Support\Facades\Event;
use Spatie\ModelStates\Events\StateChanged;

class FilamentSpatieStatesDiagramHistoryServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-spatie-states.php',
            'filament-spatie-states'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/filament-spatie-states.php' => config_path('filament-spatie-states.php'),
        ], 'filament-spatie-states-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'filament-spatie-states-migrations');

        $publishedViews = resource_path('views/vendor/filament-spatie-states');
        if (is_dir($publishedViews)) {
            $this->loadViewsFrom($publishedViews, 'filament-spatie-states');
        }
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-spatie-states');

        $this->publishes([
            __DIR__.'/../resources/views' => $publishedViews,
        ], 'filament-spatie-states-views');

        if (config('filament-spatie-states.register_listener', true)) {
            Event::listen(StateChanged::class, Listeners\StoreModelStateListener::class);
        }
    }
}

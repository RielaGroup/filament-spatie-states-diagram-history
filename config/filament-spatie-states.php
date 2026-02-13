<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Register StoreModelState listener for Spatie StateChanged event
    |--------------------------------------------------------------------------
    | Set to false if you record state history yourself.
    */
    'register_listener' => true,

    /*
    |--------------------------------------------------------------------------
    | Model state table
    |--------------------------------------------------------------------------
    */
    'table_name' => env('FILAMENT_SPATIE_STATES_TABLE', 'model_states'),

    /*
    |--------------------------------------------------------------------------
    | Model for state history records
    |--------------------------------------------------------------------------
    */
    'model_state_class' => \RielaGroup\FilamentSpatieStatesDiagramHistory\Models\ModelState::class,

    /*
    |--------------------------------------------------------------------------
    | User model for state history "changed by" relation
    |--------------------------------------------------------------------------
    */
    'user_model' => config('auth.providers.users.model', \App\Models\User::class),

    /*
    |--------------------------------------------------------------------------
    | User ID resolver for state history (when no authenticated user)
    |--------------------------------------------------------------------------
    | Callable signature: (StateChanged $event): ?int
    | Return null to leave user_id null.
    */
    'user_id_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | State diagram path line color (hex)
    |--------------------------------------------------------------------------
    */
    'path_stroke_color' => '#3CB5E3',

    'path_stroke_width' => 4,

    'non_path_stroke_width' => 1,

    'non_path_stroke_color' => '#9ca3af',

    /*
    |--------------------------------------------------------------------------
    | Mermaid CDN URL
    |--------------------------------------------------------------------------
    */
    'mermaid_js_url' => 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js',
];

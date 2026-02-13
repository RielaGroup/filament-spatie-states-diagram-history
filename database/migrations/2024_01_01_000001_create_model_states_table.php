<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('filament-spatie-states.table_name', 'model_states');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->string('state_from');
            $table->string('state_to');
            $table->text('comment')->nullable();
            $table->string('model_type');
            $table->string('model_id');
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        $tableName = config('filament-spatie-states.table_name', 'model_states');
        Schema::dropIfExists($tableName);
    }
};

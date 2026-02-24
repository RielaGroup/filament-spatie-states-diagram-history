<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('filament-spatie-states.user_id_type', 'int') !== 'uuid') {
            return;
        }

        $tableName = config('filament-spatie-states.table_name', 'model_states');
        $userTable = (new (config('filament-spatie-states.user_model', \App\Models\User::class)))->getTable();

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('user_id');
        });

        Schema::table($tableName, function (Blueprint $table) use ($userTable) {
            $table->uuid('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on($userTable)->nullOnDelete();
        });
    }

    public function down(): void
    {
        $tableName = config('filament-spatie-states.table_name', 'model_states');
        $userTable = (new (config('filament-spatie-states.user_model', \App\Models\User::class)))->getTable();

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table($tableName, function (Blueprint $table) use ($userTable) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained($userTable)->nullOnDelete();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('filament-spatie-states.table_name', 'model_states');

        // Blueprint has no way to remove only AUTO_INCREMENT; raw MODIFY avoids adding doctrine/dbal
        DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL");

        Schema::table($table, function (Blueprint $schema) {
            $schema->dropPrimary();
            $schema->dropColumn('id');
        });

        Schema::table($table, function (Blueprint $schema) {
            $schema->uuid('id')->primary()->first();
        });
    }

    public function down(): void
    {
        $table = config('filament-spatie-states.table_name', 'model_states');

        if (Schema::getColumnType($table, 'id') === 'bigint') {
            DB::statement("ALTER TABLE `{$table}` MODIFY `id` BIGINT UNSIGNED NOT NULL");
        }

        Schema::table($table, function (Blueprint $schema) {
            $schema->dropPrimary();
            $schema->dropColumn('id');
        });

        Schema::table($table, function (Blueprint $schema) {
            $schema->id()->first();
        });
    }
};

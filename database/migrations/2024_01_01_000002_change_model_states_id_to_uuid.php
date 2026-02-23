<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('filament-spatie-states.table_name', 'model_states');

        Schema::table($table, function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id');
        });

        DB::table($table)->select('id')->chunkById(100, function ($rows) use ($table) {
            foreach ($rows as $row) {
                DB::table($table)
                    ->where('id', $row->id)
                    ->update(['uuid' => (string) Str::uuid()]);
            }
        });

        Schema::table($table, function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
        });

        Schema::table($table, function (Blueprint $table) {
            $table->renameColumn('uuid', 'id');
            $table->primary('id');
        });
    }

    public function down(): void
    {
        $table = config('filament-spatie-states.table_name', 'model_states');

        Schema::table($table, function (Blueprint $table) {
            $table->dropPrimary();
            $table->dropColumn('id');
            $table->bigIncrements('id');
        });
    }
};
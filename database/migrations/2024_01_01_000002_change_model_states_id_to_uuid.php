<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('filament-spatie-states.table_name', 'model_states');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->upSqlite($tableName);
        } elseif ($driver === 'mysql') {
            $this->upMysql($tableName);
        } else {
            $this->upPgsql($tableName);
        }
    }

    protected function upSqlite(string $tableName): void
    {
        $userIdType = config('filament-spatie-states.user_id_type', 'int');
        $tempTable = $tableName.'_uuid_tmp';

        Schema::create($tempTable, function (Blueprint $table) use ($userIdType) {
            $table->uuid('id')->primary();
            $userIdType === 'uuid'
                ? $table->foreignUuid('user_id')->nullable()
                : $table->foreignId('user_id')->nullable();
            $table->string('state_from');
            $table->string('state_to');
            $table->text('comment')->nullable();
            $table->string('model_type');
            $table->string('model_id');
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });

        $rows = DB::table($tableName)->get();
        foreach ($rows as $row) {
            DB::table($tempTable)->insert([
                'id' => (string) Str::uuid(),
                'user_id' => $row->user_id,
                'state_from' => $row->state_from,
                'state_to' => $row->state_to,
                'comment' => $row->comment,
                'model_type' => $row->model_type,
                'model_id' => $row->model_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop($tableName);
        Schema::rename($tempTable, $tableName);
    }

    protected function upMysql(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) {
            $table->uuid('id_new')->nullable();
        });

        $rows = DB::table($tableName)->get();
        foreach ($rows as $row) {
            DB::table($tableName)->where('id', $row->id)->update(['id_new' => (string) Str::uuid()]);
        }

        DB::statement("ALTER TABLE `{$tableName}` DROP PRIMARY KEY");
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('id');
        });
        DB::statement("ALTER TABLE `{$tableName}` CHANGE COLUMN id_new id CHAR(36) NOT NULL PRIMARY KEY");
    }

    protected function upPgsql(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) {
            $table->uuid('id_new')->nullable();
        });

        $rows = DB::table($tableName)->get();
        foreach ($rows as $row) {
            DB::table($tableName)->where('id', $row->id)->update(['id_new' => (string) Str::uuid()]);
        }

        $pkName = $tableName.'_pkey';
        DB::statement("ALTER TABLE \"{$tableName}\" DROP CONSTRAINT \"{$pkName}\"");
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('id');
        });
        DB::statement("ALTER TABLE \"{$tableName}\" RENAME COLUMN id_new TO id");
        DB::statement("ALTER TABLE \"{$tableName}\" ALTER COLUMN id SET NOT NULL");
        DB::statement("ALTER TABLE \"{$tableName}\" ADD PRIMARY KEY (id)");
    }

    public function down(): void
    {
        $tableName = config('filament-spatie-states.table_name', 'model_states');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->downSqlite($tableName);
        } elseif ($driver === 'mysql') {
            $this->downMysql($tableName);
        } else {
            $this->downPgsql($tableName);
        }
    }

    protected function downSqlite(string $tableName): void
    {
        $userIdType = config('filament-spatie-states.user_id_type', 'int');
        $tempTable = $tableName.'_int_tmp';

        Schema::create($tempTable, function (Blueprint $table) use ($userIdType) {
            $table->id();
            $userIdType === 'uuid'
                ? $table->foreignUuid('user_id')->nullable()
                : $table->foreignId('user_id')->nullable();
            $table->string('state_from');
            $table->string('state_to');
            $table->text('comment')->nullable();
            $table->string('model_type');
            $table->string('model_id');
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });

        $rows = DB::table($tableName)->orderBy('created_at')->get();
        $id = 1;
        foreach ($rows as $row) {
            DB::table($tempTable)->insert([
                'id' => $id++,
                'user_id' => $row->user_id,
                'state_from' => $row->state_from,
                'state_to' => $row->state_to,
                'comment' => $row->comment,
                'model_type' => $row->model_type,
                'model_id' => $row->model_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::drop($tableName);
        Schema::rename($tempTable, $tableName);
    }

    protected function downMysql(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) {
            $table->unsignedBigInteger('id_old')->nullable();
        });

        $rows = DB::table($tableName)->orderBy('created_at')->get();
        $id = 1;
        foreach ($rows as $row) {
            DB::table($tableName)->where('id', $row->id)->update(['id_old' => $id++]);
        }

        DB::statement("ALTER TABLE `{$tableName}` DROP PRIMARY KEY");
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('id');
        });
        DB::statement("ALTER TABLE `{$tableName}` CHANGE COLUMN id_old id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY");
    }

    protected function downPgsql(string $tableName): void
    {
        Schema::table($tableName, function (Blueprint $table) {
            $table->unsignedBigInteger('id_old')->nullable();
        });

        $rows = DB::table($tableName)->orderBy('created_at')->get();
        $id = 1;
        foreach ($rows as $row) {
            DB::table($tableName)->where('id', $row->id)->update(['id_old' => $id++]);
        }

        $pkName = $tableName.'_pkey';
        DB::statement("ALTER TABLE \"{$tableName}\" DROP CONSTRAINT \"{$pkName}\"");
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn('id');
        });
        DB::statement("ALTER TABLE \"{$tableName}\" RENAME COLUMN id_old TO id");
        DB::statement("ALTER TABLE \"{$tableName}\" ALTER COLUMN id SET NOT NULL");
        DB::statement("ALTER TABLE \"{$tableName}\" ADD PRIMARY KEY (id)");
        DB::statement("CREATE SEQUENCE {$tableName}_id_seq OWNED BY \"{$tableName}\".id");
        DB::statement("ALTER TABLE \"{$tableName}\" ALTER COLUMN id SET DEFAULT nextval('{$tableName}_id_seq')");
    }
};

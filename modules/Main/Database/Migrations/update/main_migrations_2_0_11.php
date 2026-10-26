<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('migrations')) {
            return;
        }

        if (!Schema::hasColumn('migrations', 'migration')) {
            Schema::table('migrations', function (Blueprint $table) {
                $table->text('migration');
            });
        }
        if (!Schema::hasColumn('migrations', 'batch')) {
            Schema::table('migrations', function (Blueprint $table) {
                $table->integer('batch');
            });
        }

        // no index changes detected
    }

    public function down()
    {
        // 该文件由开发者助手生成，请按实际业务继续补充或裁剪增量变更。
    }

        private function hasIndex(string $table, string $indexName): bool
    {
        return Schema::hasIndex($table, $indexName);
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};

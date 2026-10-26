<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_auth_group_user')) {
            return;
        }

        if (!Schema::hasColumn('module_auth_group_user', 'uid')) {
            Schema::table('module_auth_group_user', function (Blueprint $table) {
                $table->integer('uid');
            });
        }
        if (!Schema::hasColumn('module_auth_group_user', 'group_id')) {
            Schema::table('module_auth_group_user', function (Blueprint $table) {
                $table->integer('group_id');
            });
        }
        if (!Schema::hasColumn('module_auth_group_user', 'created_at')) {
            Schema::table('module_auth_group_user', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_auth_group_user', 'updated_at')) {
            Schema::table('module_auth_group_user', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
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

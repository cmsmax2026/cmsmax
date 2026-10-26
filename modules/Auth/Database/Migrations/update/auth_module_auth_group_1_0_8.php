<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_auth_group')) {
            return;
        }

        if (!Schema::hasColumn('module_auth_group', 'type')) {
            Schema::table('module_auth_group', function (Blueprint $table) {
                $table->string('type', 255);
            });
        }
        if (!Schema::hasColumn('module_auth_group', 'group_name')) {
            Schema::table('module_auth_group', function (Blueprint $table) {
                $table->string('group_name', 255);
            });
        }
        if (!Schema::hasColumn('module_auth_group', 'role_json')) {
            Schema::table('module_auth_group', function (Blueprint $table) {
                $table->text('role_json')->nullable();
            });
        }
        if (!Schema::hasColumn('module_auth_group', 'created_at')) {
            Schema::table('module_auth_group', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_auth_group', 'updated_at')) {
            Schema::table('module_auth_group', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_auth_group', 'auth_group_type_idx')) {
            Schema::table('module_auth_group', function (Blueprint $table) {
                $table->index([
    'type',
], 'auth_group_type_idx');
            });
        }
        if (!$this->hasIndex('module_auth_group', 'auth_group_group_name_uk')) {
            Schema::table('module_auth_group', function (Blueprint $table) {
                $table->unique([
    'group_name',
], 'auth_group_group_name_uk');
            });
        }
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

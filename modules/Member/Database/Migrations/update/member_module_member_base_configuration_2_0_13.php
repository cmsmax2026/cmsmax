<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_member_base_configuration')) {
            return;
        }

        if (!Schema::hasColumn('module_member_base_configuration', 'name')) {
            Schema::table('module_member_base_configuration', function (Blueprint $table) {
                $table->string('name', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_base_configuration', 'json_str')) {
            Schema::table('module_member_base_configuration', function (Blueprint $table) {
                $table->text('json_str')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_base_configuration', 'updated_at')) {
            Schema::table('module_member_base_configuration', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_member_base_configuration', 'member_base_configuration_name_uk')) {
            Schema::table('module_member_base_configuration', function (Blueprint $table) {
                $table->unique([
    'name',
], 'member_base_configuration_name_uk');
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

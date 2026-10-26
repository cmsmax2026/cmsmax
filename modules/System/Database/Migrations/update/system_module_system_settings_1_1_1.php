<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_system_settings')) {
            return;
        }

        if (!Schema::hasColumn('module_system_settings', 'module')) {
            Schema::table('module_system_settings', function (Blueprint $table) {
                $table->string('module', 255);
            });
        }
        if (!Schema::hasColumn('module_system_settings', 'type')) {
            Schema::table('module_system_settings', function (Blueprint $table) {
                $table->string('type', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_settings', 'key')) {
            Schema::table('module_system_settings', function (Blueprint $table) {
                $table->string('key', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_settings', 'value')) {
            Schema::table('module_system_settings', function (Blueprint $table) {
                $table->text('value')->nullable();
            });
        }

        if (!$this->hasIndex('module_system_settings', 'system_settings_module_type_key_idx')) {
            Schema::table('module_system_settings', function (Blueprint $table) {
                $table->index([
    'module',
    'type',
    'key',
], 'system_settings_module_type_key_idx');
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

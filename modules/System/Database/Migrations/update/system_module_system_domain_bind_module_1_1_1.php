<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_system_domain_bind_module')) {
            return;
        }

        if (!Schema::hasColumn('module_system_domain_bind_module', 'module_id')) {
            Schema::table('module_system_domain_bind_module', function (Blueprint $table) {
                $table->integer('module_id');
            });
        }
        if (!Schema::hasColumn('module_system_domain_bind_module', 'module')) {
            Schema::table('module_system_domain_bind_module', function (Blueprint $table) {
                $table->string('module', 255);
            });
        }
        if (!Schema::hasColumn('module_system_domain_bind_module', 'domain')) {
            Schema::table('module_system_domain_bind_module', function (Blueprint $table) {
                $table->text('domain')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_domain_bind_module', 'num')) {
            Schema::table('module_system_domain_bind_module', function (Blueprint $table) {
                $table->integer('num')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_system_domain_bind_module', 'created_at')) {
            Schema::table('module_system_domain_bind_module', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_domain_bind_module', 'updated_at')) {
            Schema::table('module_system_domain_bind_module', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_system_domain_bind_module', 'system_domain_bind_module_module_uk')) {
            Schema::table('module_system_domain_bind_module', function (Blueprint $table) {
                $table->unique([
    'module',
], 'system_domain_bind_module_module_uk');
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

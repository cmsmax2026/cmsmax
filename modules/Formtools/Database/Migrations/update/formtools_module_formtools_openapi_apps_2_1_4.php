<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_openapi_apps')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_openapi_apps', 'model_id')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->string('model_id', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'app_name')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->string('app_name', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'app_id')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->string('app_id', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'app_secret')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->text('app_secret')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'secret_hint')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->string('secret_hint', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'status')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->integer('status')->default(1);
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'rate_limit')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->integer('rate_limit')->default(60);
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'ip_whitelist')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->text('ip_whitelist')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'remark')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->text('remark')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'last_used_at')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->text('last_used_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'last_ip')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->string('last_ip', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'created_at')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_apps', 'updated_at')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_formtools_openapi_apps', 'formtools_openapi_apps_app_id_uk')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->unique([
    'app_id',
], 'formtools_openapi_apps_app_id_uk');
            });
        }
        if (!$this->hasIndex('module_formtools_openapi_apps', 'formtools_openapi_apps_model_id_status_idx')) {
            Schema::table('module_formtools_openapi_apps', function (Blueprint $table) {
                $table->index([
    'model_id',
    'status',
], 'formtools_openapi_apps_model_id_status_idx');
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_openapi_logs')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_openapi_logs', 'model_id')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('model_id', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'app_id')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('app_id', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'access_identification')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('access_identification', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'request_id')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('request_id', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'channel')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('channel', 255)->default('openapi');
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'http_method')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('http_method', 255)->default('POST');
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'status_code')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->integer('status_code')->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'status_text')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('status_text', 255)->default('failed');
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'message')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->text('message')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'client_ip')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('client_ip', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'nonce')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('nonce', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'idempotency_key')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('idempotency_key', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'payload_hash')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->string('payload_hash', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'request_payload')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->text('request_payload')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'response_payload')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->text('response_payload')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_openapi_logs', 'created_at')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_formtools_openapi_logs', 'formtools_openapi_logs_model_id_idx')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->index([
    'model_id',
], 'formtools_openapi_logs_model_id_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_openapi_logs', 'formtools_openapi_logs_app_id_idx')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->index([
    'app_id',
], 'formtools_openapi_logs_app_id_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_openapi_logs', 'formtools_openapi_logs_request_id_idx')) {
            Schema::table('module_formtools_openapi_logs', function (Blueprint $table) {
                $table->index([
    'request_id',
], 'formtools_openapi_logs_request_id_idx');
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

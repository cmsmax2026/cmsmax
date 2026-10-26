<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('system_ip_block_logs')) {
            return;
        }

        if (!Schema::hasColumn('system_ip_block_logs', 'ip')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->string('ip', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_block_logs', 'scope')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->string('scope', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_block_logs', 'source')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->string('source', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_block_logs', 'rule_id')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->integer('rule_id')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('system_ip_block_logs', 'rule_value')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->string('rule_value', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_block_logs', 'request_path')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->text('request_path')->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_block_logs', 'user_agent')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->text('user_agent')->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_block_logs', 'reason')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->text('reason')->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_block_logs', 'created_at')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->string('created_at', 255)->nullable();
            });
        }

        if (!$this->hasIndex('system_ip_block_logs', 'ip_block_logs_scope_source_idx')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->index([
    'scope',
    'source',
], 'ip_block_logs_scope_source_idx');
            });
        }
        if (!$this->hasIndex('system_ip_block_logs', 'ip_block_logs_created_at_idx')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->index([
    'created_at',
], 'ip_block_logs_created_at_idx');
            });
        }
        if (!$this->hasIndex('system_ip_block_logs', 'ip_block_logs_ip_idx')) {
            Schema::table('system_ip_block_logs', function (Blueprint $table) {
                $table->index([
    'ip',
], 'ip_block_logs_ip_idx');
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

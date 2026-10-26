<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('system_ip_access_rules')) {
            return;
        }

        if (!Schema::hasColumn('system_ip_access_rules', 'scope')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->string('scope', 255);
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'rule_type')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->string('rule_type', 255);
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'match_type')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->text('match_type');
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'rule_value')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->string('rule_value', 255);
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'source_type')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->text('source_type');
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'status')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->integer('status')->default(1);
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'reason')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->text('reason')->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'expires_at')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->string('expires_at', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'hit_count')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->integer('hit_count')->default(0);
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'last_hit_at')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->text('last_hit_at')->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'created_at')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('system_ip_access_rules', 'updated_at')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('system_ip_access_rules', 'ip_access_rules_scope_status_rule_type_idx')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->index([
    'scope',
    'status',
    'rule_type',
], 'ip_access_rules_scope_status_rule_type_idx');
            });
        }
        if (!$this->hasIndex('system_ip_access_rules', 'ip_access_rules_rule_value_idx')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->index([
    'rule_value',
], 'ip_access_rules_rule_value_idx');
            });
        }
        if (!$this->hasIndex('system_ip_access_rules', 'ip_access_rules_expires_at_idx')) {
            Schema::table('system_ip_access_rules', function (Blueprint $table) {
                $table->index([
    'expires_at',
], 'ip_access_rules_expires_at_idx');
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

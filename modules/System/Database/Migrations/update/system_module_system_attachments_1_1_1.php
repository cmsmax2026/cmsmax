<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_system_attachments')) {
            return;
        }

        if (!Schema::hasColumn('module_system_attachments', 'path')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->string('path', 255);
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'path_md5')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->string('path_md5', 255);
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'drive')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('drive');
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'create_at')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('create_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'update_at')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('update_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'deleted_at')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('deleted_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'deleted_by')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('deleted_by');
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'is_star')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('is_star');
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'share_token')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('share_token');
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'share_expire_at')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('share_expire_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'is_private')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('is_private');
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'access_key')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('access_key');
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'share_visit_total')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->integer('share_visit_total')->default(0);
            });
        }
        if (!Schema::hasColumn('module_system_attachments', 'share_last_visit_at')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->text('share_last_visit_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_system_attachments', 'system_attachments_path_md5_idx')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->index([
    'path_md5',
], 'system_attachments_path_md5_idx');
            });
        }
        if (!$this->hasIndex('module_system_attachments', 'system_attachments_path_idx')) {
            Schema::table('module_system_attachments', function (Blueprint $table) {
                $table->index([
    'path',
], 'system_attachments_path_idx');
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

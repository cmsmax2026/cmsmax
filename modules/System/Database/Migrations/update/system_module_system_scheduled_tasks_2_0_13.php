<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_system_scheduled_tasks')) {
            return;
        }

        if (!Schema::hasColumn('module_system_scheduled_tasks', 'module')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->text('module')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'task_type')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->integer('task_type')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'name')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->text('name')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'type')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->integer('type')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'day')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->integer('day')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'hour')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->integer('hour')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'minute')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->integer('minute')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'status')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'last_execution_time')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->text('last_execution_time')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'content')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->text('content')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'created_at')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'updated_at')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'module_class')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->text('module_class')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'module_class_method')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->text('module_class_method')->nullable();
            });
        }
        if (!Schema::hasColumn('module_system_scheduled_tasks', 'remark')) {
            Schema::table('module_system_scheduled_tasks', function (Blueprint $table) {
                $table->text('remark')->nullable();
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

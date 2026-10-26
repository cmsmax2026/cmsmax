<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('system_message')) {
            return;
        }

        if (!Schema::hasColumn('system_message', 'module')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->text('module')->nullable();
            });
        }
        if (!Schema::hasColumn('system_message', 'title')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->text('title')->nullable();
            });
        }
        if (!Schema::hasColumn('system_message', 'content')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->text('content')->nullable();
            });
        }
        if (!Schema::hasColumn('system_message', 'uid')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->integer('uid')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('system_message', 'receive_uid')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->integer('receive_uid')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('system_message', 'status')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('system_message', 'created_at')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('system_message', 'updated_at')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }
        if (!Schema::hasColumn('system_message', 'json_str')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->text('json_str')->nullable();
            });
        }

        if (!$this->hasIndex('system_message', 'message_status_idx')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->index([
    'status',
], 'message_status_idx');
            });
        }
        if (!$this->hasIndex('system_message', 'message_uid_idx')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->index([
    'uid',
], 'message_uid_idx');
            });
        }
        if (!$this->hasIndex('system_message', 'message_receive_uid_idx')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->index([
    'receive_uid',
], 'message_receive_uid_idx');
            });
        }
        if (!$this->hasIndex('system_message', 'message_receive_uid_idx')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->index([
    'receive_uid',
], 'message_receive_uid_idx');
            });
        }
        if (!$this->hasIndex('system_message', 'message_uid_idx')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->index([
    'uid',
], 'message_uid_idx');
            });
        }
        if (!$this->hasIndex('system_message', 'message_status_idx')) {
            Schema::table('system_message', function (Blueprint $table) {
                $table->index([
    'status',
], 'message_status_idx');
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

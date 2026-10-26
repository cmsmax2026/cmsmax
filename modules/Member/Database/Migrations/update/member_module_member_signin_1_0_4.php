<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_member_signin')) {
            return;
        }

        if (!Schema::hasColumn('module_member_signin', 'order_num')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->text('order_num')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_signin', 'uid')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->integer('uid');
            });
        }
        if (!Schema::hasColumn('module_member_signin', 'day')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->string('day', 255);
            });
        }
        if (!Schema::hasColumn('module_member_signin', 'tig')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->string('tig', 255);
            });
        }
        if (!Schema::hasColumn('module_member_signin', 'point')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->integer('point')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_signin', 'remark')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->text('remark')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_signin', 'created_at')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_signin', 'updated_at')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_member_signin', 'member_signin_tig_idx')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->index([
    'tig',
], 'member_signin_tig_idx');
            });
        }
        if (!$this->hasIndex('module_member_signin', 'member_signin_day_idx')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->index([
    'day',
], 'member_signin_day_idx');
            });
        }
        if (!$this->hasIndex('module_member_signin', 'member_signin_uid_idx')) {
            Schema::table('module_member_signin', function (Blueprint $table) {
                $table->index([
    'uid',
], 'member_signin_uid_idx');
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

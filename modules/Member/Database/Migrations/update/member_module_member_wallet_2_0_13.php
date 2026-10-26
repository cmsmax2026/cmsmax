<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_member_wallet')) {
            return;
        }

        if (!Schema::hasColumn('module_member_wallet', 'uid')) {
            Schema::table('module_member_wallet', function (Blueprint $table) {
                $table->integer('uid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_wallet', 'withdrawable')) {
            Schema::table('module_member_wallet', function (Blueprint $table) {
                $table->double('withdrawable')->nullable()->default(0.00);
            });
        }
        if (!Schema::hasColumn('module_member_wallet', 'balance')) {
            Schema::table('module_member_wallet', function (Blueprint $table) {
                $table->double('balance')->nullable()->default(0.00);
            });
        }
        if (!Schema::hasColumn('module_member_wallet', 'integral')) {
            Schema::table('module_member_wallet', function (Blueprint $table) {
                $table->double('integral')->nullable()->default(0.00);
            });
        }
        if (!Schema::hasColumn('module_member_wallet', 'vip_time')) {
            Schema::table('module_member_wallet', function (Blueprint $table) {
                $table->text('vip_time')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_wallet', 'created_at')) {
            Schema::table('module_member_wallet', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_wallet', 'updated_at')) {
            Schema::table('module_member_wallet', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
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

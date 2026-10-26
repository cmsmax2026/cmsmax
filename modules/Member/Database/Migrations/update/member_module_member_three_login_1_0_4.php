<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_member_three_login')) {
            return;
        }

        if (!Schema::hasColumn('module_member_three_login', 'uid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->integer('uid')->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'wx_unionid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->string('wx_unionid', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'wx_app_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('wx_app_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'wx_public_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('wx_public_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'wx_small_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->string('wx_small_openid', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'apple_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('apple_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'qq_app_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('qq_app_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'qq_public_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('qq_public_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'qq_small_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('qq_small_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'wb_app_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('wb_app_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'wb_public_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('wb_public_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'bd_app_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('bd_app_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'bd_public_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('bd_public_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'bd_small_openid')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('bd_small_openid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'created_at')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_three_login', 'update_at')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->integer('update_at')->nullable()->default(0);
            });
        }

        if (!$this->hasIndex('module_member_three_login', 'member_three_login_wx_small_openid_idx')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->index([
    'wx_small_openid',
], 'member_three_login_wx_small_openid_idx');
            });
        }
        if (!$this->hasIndex('module_member_three_login', 'member_three_login_wx_unionid_idx')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->index([
    'wx_unionid',
], 'member_three_login_wx_unionid_idx');
            });
        }
        if (!$this->hasIndex('module_member_three_login', 'member_three_login_uid_idx')) {
            Schema::table('module_member_three_login', function (Blueprint $table) {
                $table->index([
    'uid',
], 'member_three_login_uid_idx');
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

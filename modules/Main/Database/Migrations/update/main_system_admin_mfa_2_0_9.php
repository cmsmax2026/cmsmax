<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('system_admin_mfa')) {
            return;
        }

        if (!Schema::hasColumn('system_admin_mfa', 'uid')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->integer('uid')->default(0);
            });
        }
        if (!Schema::hasColumn('system_admin_mfa', 'provider')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->string('provider', 255)->default('google_authenticator');
            });
        }
        if (!Schema::hasColumn('system_admin_mfa', 'secret')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->text('secret')->nullable();
            });
        }
        if (!Schema::hasColumn('system_admin_mfa', 'status')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->integer('status')->default(1);
            });
        }
        if (!Schema::hasColumn('system_admin_mfa', 'bound_at')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->dateTime('bound_at')->nullable();
            });
        }
        if (!Schema::hasColumn('system_admin_mfa', 'last_verified_at')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->dateTime('last_verified_at')->nullable();
            });
        }
        if (!Schema::hasColumn('system_admin_mfa', 'created_at')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->dateTime('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('system_admin_mfa', 'updated_at')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->dateTime('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('system_admin_mfa', 'admin_mfa_status_provider_idx')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->index([
    'status',
    'provider',
], 'admin_mfa_status_provider_idx');
            });
        }
        if (!$this->hasIndex('system_admin_mfa', 'admin_mfa_uid_provider_uk')) {
            Schema::table('system_admin_mfa', function (Blueprint $table) {
                $table->unique([
    'uid',
    'provider',
], 'admin_mfa_uid_provider_uk');
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

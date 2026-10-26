<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('members')) {
            return;
        }

        if (!Schema::hasColumn('members', 'avatar')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('avatar')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'c_code')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('c_code')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'phone')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('phone', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'email')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('email')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'username')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('username', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'nickname')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('nickname')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'password')) {
            Schema::table('members', function (Blueprint $table) {
                $table->string('password', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'status')) {
            Schema::table('members', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('members', 'pid')) {
            Schema::table('members', function (Blueprint $table) {
                $table->integer('pid')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('members', 'pid_path')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('pid_path')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'male')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('male')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'birthday')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('birthday')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'phone_active')) {
            Schema::table('members', function (Blueprint $table) {
                $table->integer('phone_active')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'email_active')) {
            Schema::table('members', function (Blueprint $table) {
                $table->integer('email_active')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'signature')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('signature')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'created_at')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('members', 'updated_at')) {
            Schema::table('members', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('members', 'members_password_idx')) {
            Schema::table('members', function (Blueprint $table) {
                $table->index([
    'password',
], 'members_password_idx');
            });
        }
        if (!$this->hasIndex('members', 'members_username_idx')) {
            Schema::table('members', function (Blueprint $table) {
                $table->index([
    'username',
], 'members_username_idx');
            });
        }
        if (!$this->hasIndex('members', 'members_phone_idx')) {
            Schema::table('members', function (Blueprint $table) {
                $table->index([
    'phone',
], 'members_phone_idx');
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

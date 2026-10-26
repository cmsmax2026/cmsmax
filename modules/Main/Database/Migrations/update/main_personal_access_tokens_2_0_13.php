<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('personal_access_tokens')) {
            return;
        }

        if (!Schema::hasColumn('personal_access_tokens', 'tokenable_type')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->text('tokenable_type');
            });
        }
        if (!Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->integer('tokenable_id');
            });
        }
        if (!Schema::hasColumn('personal_access_tokens', 'name')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->text('name');
            });
        }
        if (!Schema::hasColumn('personal_access_tokens', 'token')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->text('token');
            });
        }
        if (!Schema::hasColumn('personal_access_tokens', 'abilities')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->text('abilities')->nullable();
            });
        }
        if (!Schema::hasColumn('personal_access_tokens', 'last_used_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->text('last_used_at')->nullable();
            });
        }
        if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->text('expires_at')->nullable();
            });
        }
        if (!Schema::hasColumn('personal_access_tokens', 'created_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('personal_access_tokens', 'updated_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
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

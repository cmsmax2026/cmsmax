<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('modules')) {
            return;
        }

        if (!Schema::hasColumn('modules', 'name')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->text('name')->nullable();
            });
        }
        if (!Schema::hasColumn('modules', 'identification')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->string('identification', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('modules', 'status')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('modules', 'is_index')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->integer('is_index')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('modules', 'is_backend')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->integer('is_backend')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('modules', 'cloud_type')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->text('cloud_type')->nullable();
            });
        }
        if (!Schema::hasColumn('modules', 'type')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->text('type')->nullable();
            });
        }
        if (!Schema::hasColumn('modules', 'domain')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->text('domain')->nullable();
            });
        }
        if (!Schema::hasColumn('modules', 'form')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->text('form')->nullable();
            });
        }
        if (!Schema::hasColumn('modules', 'order')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->integer('order')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('modules', 'created_at')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('modules', 'updated_at')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('modules', 'modules_identification_idx')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->index([
    'identification',
], 'modules_identification_idx');
            });
        }
        if (!$this->hasIndex('modules', 'modules_identification_idx')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->index([
    'identification',
], 'modules_identification_idx');
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

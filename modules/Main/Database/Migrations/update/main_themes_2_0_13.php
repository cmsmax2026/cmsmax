<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('themes')) {
            return;
        }

        if (!Schema::hasColumn('themes', 'name')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->text('name')->nullable();
            });
        }
        if (!Schema::hasColumn('themes', 'preview')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->text('preview')->nullable();
            });
        }
        if (!Schema::hasColumn('themes', 'identification')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->string('identification', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('themes', 'status')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(2);
            });
        }
        if (!Schema::hasColumn('themes', 'order')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->integer('order')->default(0);
            });
        }
        if (!Schema::hasColumn('themes', 'form')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->text('form')->nullable();
            });
        }
        if (!Schema::hasColumn('themes', 'created_at')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('themes', 'updated_at')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('themes', 'themes_identification_uk')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->unique([
    'identification',
], 'themes_identification_uk');
            });
        }
        if (!$this->hasIndex('themes', 'themes_order_status_idx')) {
            Schema::table('themes', function (Blueprint $table) {
                $table->index([
    'order',
    'status',
], 'themes_order_status_idx');
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

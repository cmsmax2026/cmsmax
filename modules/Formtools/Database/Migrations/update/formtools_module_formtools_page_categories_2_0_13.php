<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_page_categories')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_page_categories', 'name')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->text('name');
            });
        }
        if (!Schema::hasColumn('module_formtools_page_categories', 'identification')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->string('identification', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_page_categories', 'sort')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->integer('sort')->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_page_categories', 'status')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->integer('status')->default(1);
            });
        }
        if (!Schema::hasColumn('module_formtools_page_categories', 'remark')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->text('remark');
            });
        }
        if (!Schema::hasColumn('module_formtools_page_categories', 'created_at')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_page_categories', 'updated_at')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_formtools_page_categories', 'formtools_page_categories_identification_uk')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->unique([
    'identification',
], 'formtools_page_categories_identification_uk');
            });
        }
        if (!$this->hasIndex('module_formtools_page_categories', 'formtools_page_categories_sort_idx')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->index([
    'sort',
], 'formtools_page_categories_sort_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_page_categories', 'formtools_page_categories_status_idx')) {
            Schema::table('module_formtools_page_categories', function (Blueprint $table) {
                $table->index([
    'status',
], 'formtools_page_categories_status_idx');
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

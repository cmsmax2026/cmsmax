<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_pages')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_pages', 'name')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('name');
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'identification')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->string('identification', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'slug')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->string('slug', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'type')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('type');
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'category_id')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->integer('category_id')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'model_id')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->integer('model_id')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'template')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('template');
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'builder_type')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('builder_type');
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'status')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->integer('status')->default(1);
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'is_nav')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->integer('is_nav')->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'is_home')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->integer('is_home')->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'sort')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->integer('sort')->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'remark')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('remark');
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'seo_title')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('seo_title')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'seo_keywords')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('seo_keywords')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'seo_description')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('seo_description')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'layout_schema')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('layout_schema')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'page_html')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('page_html')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'custom_css')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('custom_css')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'custom_js')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('custom_js')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'created_at')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_pages', 'updated_at')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_formtools_pages', 'formtools_pages_identification_uk')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->unique([
    'identification',
], 'formtools_pages_identification_uk');
            });
        }
        if (!$this->hasIndex('module_formtools_pages', 'formtools_pages_slug_uk')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->unique([
    'slug',
], 'formtools_pages_slug_uk');
            });
        }
        if (!$this->hasIndex('module_formtools_pages', 'formtools_pages_model_id_idx')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->index([
    'model_id',
], 'formtools_pages_model_id_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_pages', 'formtools_pages_status_idx')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->index([
    'status',
], 'formtools_pages_status_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_pages', 'formtools_pages_sort_idx')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->index([
    'sort',
], 'formtools_pages_sort_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_pages', 'formtools_pages_category_id_idx')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->index([
    'category_id',
], 'formtools_pages_category_id_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_pages', 'formtools_pages_is_home_idx')) {
            Schema::table('module_formtools_pages', function (Blueprint $table) {
                $table->index([
    'is_home',
], 'formtools_pages_is_home_idx');
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

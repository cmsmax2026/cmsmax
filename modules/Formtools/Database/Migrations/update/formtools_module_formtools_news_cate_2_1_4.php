<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_news_cate')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_news_cate', 'cate_name')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->text('cate_name')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'created_at')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'updated_at')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'remark')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->text('remark')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'status')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'seo_title')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->text('seo_title')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'seo_keywords')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->text('seo_keywords')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'seo_description')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->text('seo_description')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'download_count')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->integer('download_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'comment_count')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->integer('comment_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'good_count')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->integer('good_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'access_count')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->integer('access_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'uid')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->integer('uid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'pid')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->integer('pid')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_news_cate', 'price')) {
            Schema::table('module_formtools_news_cate', function (Blueprint $table) {
                $table->float('price')->nullable()->default(0);
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

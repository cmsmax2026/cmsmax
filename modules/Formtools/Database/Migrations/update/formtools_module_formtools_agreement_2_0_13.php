<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_agreement')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_agreement', 'cate_id')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->integer('cate_id')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'name')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->text('name')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'content')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->text('content')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'seo_keywords')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->text('seo_keywords')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'seo_description')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->text('seo_description')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'status')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->integer('status')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'created_at')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'updated_at')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'remark')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->text('remark')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'seo_title')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->text('seo_title')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'download_count')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->integer('download_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'comment_count')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->integer('comment_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'good_count')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->integer('good_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'access_count')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->integer('access_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'uid')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
                $table->integer('uid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_agreement', 'price')) {
            Schema::table('module_formtools_agreement', function (Blueprint $table) {
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

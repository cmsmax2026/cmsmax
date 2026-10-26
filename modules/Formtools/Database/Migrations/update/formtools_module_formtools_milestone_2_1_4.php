<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_milestone')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_milestone', 'title')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->text('title')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'date')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->text('date')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'content')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->text('content')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'created_at')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'updated_at')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'remark')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->text('remark')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'status')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'seo_title')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->text('seo_title')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'seo_keywords')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->text('seo_keywords')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'seo_description')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->text('seo_description')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'download_count')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->integer('download_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'comment_count')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->integer('comment_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'good_count')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->integer('good_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'access_count')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->integer('access_count')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'uid')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
                $table->integer('uid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_milestone', 'price')) {
            Schema::table('module_formtools_milestone', function (Blueprint $table) {
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

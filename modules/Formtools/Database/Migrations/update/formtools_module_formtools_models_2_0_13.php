<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_models')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_models', 'name')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('name')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'identification')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->string('identification', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'access_identification')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->string('access_identification', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'menuname')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('menuname')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'module')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('module')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'fields')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('fields')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'icon')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('icon')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'type')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('type')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'supermodel')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->integer('supermodel')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'remark')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('remark')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'admin_config')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('admin_config')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'home_config')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('home_config')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'home_seo_config')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('home_seo_config')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'home_seo_detail_config')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('home_seo_detail_config')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'other_config')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('other_config')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'show_home_page')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('show_home_page')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'home_page_num')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->integer('home_page_num')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'home_page_sort')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->integer('home_page_sort')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'created_at')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_models', 'updated_at')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_formtools_models', 'formtools_models_identification_uk')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->unique([
    'identification',
], 'formtools_models_identification_uk');
            });
        }
        if (!$this->hasIndex('module_formtools_models', 'formtools_models_access_identification_idx')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->index([
    'access_identification',
], 'formtools_models_access_identification_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_models', 'formtools_models_supermodel_idx')) {
            Schema::table('module_formtools_models', function (Blueprint $table) {
                $table->index([
    'supermodel',
], 'formtools_models_supermodel_idx');
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

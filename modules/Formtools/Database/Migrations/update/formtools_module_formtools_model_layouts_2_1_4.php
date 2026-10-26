<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_model_layouts')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_model_layouts', 'model_id')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->string('model_id', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_model_layouts', 'scene')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->string('scene', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_model_layouts', 'status')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->integer('status')->default(1);
            });
        }
        if (!Schema::hasColumn('module_formtools_model_layouts', 'layout_schema')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->text('layout_schema')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_model_layouts', 'theme_tokens')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->text('theme_tokens')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_model_layouts', 'version')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->integer('version')->default(1);
            });
        }
        if (!Schema::hasColumn('module_formtools_model_layouts', 'published_at')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->text('published_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_model_layouts', 'created_at')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_model_layouts', 'updated_at')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_formtools_model_layouts', 'formtools_model_layouts_model_id_scene_uk')) {
            Schema::table('module_formtools_model_layouts', function (Blueprint $table) {
                $table->unique([
    'model_id',
    'scene',
], 'formtools_model_layouts_model_id_scene_uk');
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

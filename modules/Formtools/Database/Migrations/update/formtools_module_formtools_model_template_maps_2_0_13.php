<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_model_template_maps')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_model_template_maps', 'model_id')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->string('model_id', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_model_template_maps', 'scene')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->string('scene', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_model_template_maps', 'mode')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->string('mode', 255)->default('preset');
            });
        }
        if (!Schema::hasColumn('module_formtools_model_template_maps', 'template_key')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->string('template_key', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_model_template_maps', 'custom_template')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->text('custom_template')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_model_template_maps', 'field_map')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->text('field_map')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_model_template_maps', 'match_summary')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->text('match_summary')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_model_template_maps', 'created_at')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_model_template_maps', 'updated_at')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_formtools_model_template_maps', 'formtools_model_template_maps_model_id_scene_uk')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->unique([
    'model_id',
    'scene',
], 'formtools_model_template_maps_model_id_scene_uk');
            });
        }
        if (!$this->hasIndex('module_formtools_model_template_maps', 'formtools_model_template_maps_scene_mode_idx')) {
            Schema::table('module_formtools_model_template_maps', function (Blueprint $table) {
                $table->index([
    'scene',
    'mode',
], 'formtools_model_template_maps_scene_mode_idx');
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

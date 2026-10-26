<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_template_registry')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_template_registry', 'scene')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->string('scene', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'template_key')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->string('template_key', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'template_name')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->string('template_name', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'description')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->text('description')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'required_fields')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->text('required_fields')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'recommended_fields')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->text('recommended_fields')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'supported_model_type')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->text('supported_model_type')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'status')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->integer('status')->default(1);
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'sort')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->integer('sort')->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'created_at')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_template_registry', 'updated_at')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_formtools_template_registry', 'formtools_template_registry_scene_template_key_uk')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->unique([
    'scene',
    'template_key',
], 'formtools_template_registry_scene_template_key_uk');
            });
        }
        if (!$this->hasIndex('module_formtools_template_registry', 'formtools_template_registry_scene_status_idx')) {
            Schema::table('module_formtools_template_registry', function (Blueprint $table) {
                $table->index([
    'scene',
    'status',
], 'formtools_template_registry_scene_status_idx');
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

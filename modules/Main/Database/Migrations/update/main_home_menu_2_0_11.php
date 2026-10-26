<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('home_menu')) {
            return;
        }

        if (!Schema::hasColumn('home_menu', 'module')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('module')->nullable();
            });
        }
        if (!Schema::hasColumn('home_menu', 'position')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->string('position', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('home_menu', 'lang')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->string('lang', 255);
            });
        }
        if (!Schema::hasColumn('home_menu', 'pid')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->integer('pid')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('home_menu', 'name')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('name')->nullable();
            });
        }
        if (!Schema::hasColumn('home_menu', 'url')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('url')->nullable();
            });
        }
        if (!Schema::hasColumn('home_menu', 'target')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('target');
            });
        }
        if (!Schema::hasColumn('home_menu', 'icon')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('icon')->nullable();
            });
        }
        if (!Schema::hasColumn('home_menu', 'icon_character')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('icon_character')->nullable();
            });
        }
        if (!Schema::hasColumn('home_menu', 'menu_type')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('menu_type');
            });
        }
        if (!Schema::hasColumn('home_menu', 'source_module')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('source_module');
            });
        }
        if (!Schema::hasColumn('home_menu', 'source_value')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('source_value');
            });
        }
        if (!Schema::hasColumn('home_menu', 'cover')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('cover')->nullable();
            });
        }
        if (!Schema::hasColumn('home_menu', 'status')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('home_menu', 'sort')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->integer('sort')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('home_menu', 'created_at')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('home_menu', 'updated_at')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('home_menu', 'home_menu_position_lang_idx')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->index([
    'position',
    'lang',
], 'home_menu_position_lang_idx');
            });
        }
        if (!$this->hasIndex('home_menu', 'home_menu_position_idx')) {
            Schema::table('home_menu', function (Blueprint $table) {
                $table->index([
    'position',
], 'home_menu_position_idx');
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

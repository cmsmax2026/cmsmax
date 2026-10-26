<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_member_vip')) {
            return;
        }

        if (!Schema::hasColumn('module_member_vip', 'name')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->text('name')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'type')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->integer('type')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'number')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->integer('number')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'price')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->double('price')->nullable()->default(0.00);
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'discount_price')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->double('discount_price')->nullable()->default(0.00);
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'describe')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->text('describe')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'tig')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->text('tig')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'status')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'sort')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->integer('sort')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'created_at')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'updated_at')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip', 'is_only_buy_one')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->integer('is_only_buy_one')->nullable()->default(2);
            });
        }

        if (!$this->hasIndex('module_member_vip', 'member_vip_status_idx')) {
            Schema::table('module_member_vip', function (Blueprint $table) {
                $table->index([
    'status',
], 'member_vip_status_idx');
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

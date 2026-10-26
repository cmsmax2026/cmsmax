<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_member_vip_order')) {
            return;
        }

        if (!Schema::hasColumn('module_member_vip_order', 'payment_id')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->text('payment_id')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'order_num')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->string('order_num', 255);
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'uid')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->integer('uid');
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'price')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->double('price')->default(0.00);
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'vip_id')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->integer('vip_id')->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'type')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->integer('type')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'number')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->integer('number')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'pay_method')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->text('pay_method')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'pay_type')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->text('pay_type')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'pay_status')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->integer('pay_status')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'remark')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->text('remark')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'callback_msg')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->text('callback_msg')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'pay_at')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->text('pay_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'created_at')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->string('created_at', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'expire_at')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->text('expire_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_vip_order', 'updated_at')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_member_vip_order', 'member_vip_order_created_at_idx')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->index([
    'created_at',
], 'member_vip_order_created_at_idx');
            });
        }
        if (!$this->hasIndex('module_member_vip_order', 'member_vip_order_uid_idx')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->index([
    'uid',
], 'member_vip_order_uid_idx');
            });
        }
        if (!$this->hasIndex('module_member_vip_order', 'member_vip_order_order_num_idx')) {
            Schema::table('module_member_vip_order', function (Blueprint $table) {
                $table->index([
    'order_num',
], 'member_vip_order_order_num_idx');
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

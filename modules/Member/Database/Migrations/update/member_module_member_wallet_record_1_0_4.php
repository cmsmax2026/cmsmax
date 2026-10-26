<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_member_wallet_record')) {
            return;
        }

        if (!Schema::hasColumn('module_member_wallet_record', 'module')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->string('module', 255);
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'uid')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->integer('uid')->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'bill_order_num')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->string('bill_order_num', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'order_num')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->string('order_num', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'type')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->integer('type')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'amount_type')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->text('amount_type');
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'amount')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->double('amount')->nullable()->default(0.00);
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'unit')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->text('unit')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'remark')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->text('remark')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'extra')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->text('extra')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'created_at')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->string('created_at', 255)->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_wallet_record', 'updated_at')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_member_wallet_record', 'member_wallet_record_created_at_idx')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->index([
    'created_at',
], 'member_wallet_record_created_at_idx');
            });
        }
        if (!$this->hasIndex('module_member_wallet_record', 'member_wallet_record_order_num_idx')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->index([
    'order_num',
], 'member_wallet_record_order_num_idx');
            });
        }
        if (!$this->hasIndex('module_member_wallet_record', 'member_wallet_record_bill_order_num_idx')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->index([
    'bill_order_num',
], 'member_wallet_record_bill_order_num_idx');
            });
        }
        if (!$this->hasIndex('module_member_wallet_record', 'member_wallet_record_uid_idx')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->index([
    'uid',
], 'member_wallet_record_uid_idx');
            });
        }
        if (!$this->hasIndex('module_member_wallet_record', 'member_wallet_record_module_idx')) {
            Schema::table('module_member_wallet_record', function (Blueprint $table) {
                $table->index([
    'module',
], 'member_wallet_record_module_idx');
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

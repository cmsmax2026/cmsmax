<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_formtools_paid_records')) {
            return;
        }

        if (!Schema::hasColumn('module_formtools_paid_records', 'user_id')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->integer('user_id')->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'user_type')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->string('user_type', 255)->default('');
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'user_label')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->string('user_label', 255)->default('');
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'model_identification')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->string('model_identification', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'content_id')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->integer('content_id');
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'content_title')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->string('content_title', 255)->default('');
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'order_no')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->string('order_no', 255);
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'amount')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->decimal('amount', 10, 0)->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'pay_method')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->string('pay_method', 255)->default('');
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'pay_type')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->string('pay_type', 255)->default('');
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'status')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->integer('status')->default(0);
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'paid_at')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->dateTime('paid_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'expire_at')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->dateTime('expire_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'out_trade_no')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->string('out_trade_no', 255)->default('');
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'extra')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->text('extra')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'created_at')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->dateTime('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_formtools_paid_records', 'updated_at')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->dateTime('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_formtools_paid_records', 'formtools_paid_records_status_idx')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->index([
    'status',
], 'formtools_paid_records_status_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_paid_records', 'formtools_paid_records_user_id_user_type_idx')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->index([
    'user_id',
    'user_type',
], 'formtools_paid_records_user_id_user_type_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_paid_records', 'formtools_paid_records_model_identification_content_id_idx')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->index([
    'model_identification',
    'content_id',
], 'formtools_paid_records_model_identification_content_id_idx');
            });
        }
        if (!$this->hasIndex('module_formtools_paid_records', 'formtools_paid_records_order_no_uk')) {
            Schema::table('module_formtools_paid_records', function (Blueprint $table) {
                $table->unique([
    'order_no',
], 'formtools_paid_records_order_no_uk');
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

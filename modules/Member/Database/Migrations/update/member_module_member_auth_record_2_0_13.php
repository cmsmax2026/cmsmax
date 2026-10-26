<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('module_member_auth_record')) {
            return;
        }

        if (!Schema::hasColumn('module_member_auth_record', 'type')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->integer('type')->nullable()->default(1);
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'uid')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->integer('uid')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'real_name')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('real_name')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'id_card')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('id_card')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'id_card_positive_img')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('id_card_positive_img')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'id_card_back_img')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('id_card_back_img')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'id_card_hand_img')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('id_card_hand_img')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'company_name')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('company_name')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'unified_social_credit_code')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('unified_social_credit_code')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'business_license_img')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('business_license_img')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'legal_person')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('legal_person')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'legal_id_card')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('legal_id_card')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'status')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->integer('status')->nullable()->default(0);
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'remark')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('remark')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'created_at')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('created_at')->nullable();
            });
        }
        if (!Schema::hasColumn('module_member_auth_record', 'updated_at')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->text('updated_at')->nullable();
            });
        }

        if (!$this->hasIndex('module_member_auth_record', 'member_auth_record_uid_idx')) {
            Schema::table('module_member_auth_record', function (Blueprint $table) {
                $table->index([
    'uid',
], 'member_auth_record_uid_idx');
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

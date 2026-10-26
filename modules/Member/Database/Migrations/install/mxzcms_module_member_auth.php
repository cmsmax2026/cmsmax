<?php

use App\Support\Database\DatabaseCharsetResolver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('module_member_auth')) {
            return;
        }

        Schema::create('module_member_auth', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->integer('type')->nullable()->default(1);
            $table->integer('uid')->nullable();
            $table->text('real_name')->nullable();
            $table->text('id_card')->nullable();
            $table->text('id_card_positive_img')->nullable();
            $table->text('id_card_back_img')->nullable();
            $table->text('id_card_hand_img')->nullable();
            $table->text('company_name')->nullable();
            $table->text('unified_social_credit_code')->nullable();
            $table->text('business_license_img')->nullable();
            $table->text('legal_person')->nullable();
            $table->text('legal_id_card')->nullable();
            $table->integer('status')->nullable()->default(0);
            $table->text('remark')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->index([
    'uid',
], 'member_auth_uid_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_member_auth');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};

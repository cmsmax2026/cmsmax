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
        if (Schema::hasTable('module_member_three_login')) {
            return;
        }

        Schema::create('module_member_three_login', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->integer('uid')->default(0);
            $table->string('wx_unionid', 255)->nullable();
            $table->text('wx_app_openid')->nullable();
            $table->text('wx_public_openid')->nullable();
            $table->string('wx_small_openid', 255)->nullable();
            $table->text('apple_openid')->nullable();
            $table->text('qq_app_openid')->nullable();
            $table->text('qq_public_openid')->nullable();
            $table->text('qq_small_openid')->nullable();
            $table->text('wb_app_openid')->nullable();
            $table->text('wb_public_openid')->nullable();
            $table->text('bd_app_openid')->nullable();
            $table->text('bd_public_openid')->nullable();
            $table->text('bd_small_openid')->nullable();
            $table->text('created_at')->nullable();
            $table->integer('update_at')->nullable()->default(0);
            $table->index([
    'wx_small_openid',
], 'member_three_login_wx_small_openid_idx');
            $table->index([
    'wx_unionid',
], 'member_three_login_wx_unionid_idx');
            $table->index([
    'uid',
], 'member_three_login_uid_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_member_three_login');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};

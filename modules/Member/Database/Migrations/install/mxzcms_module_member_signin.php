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
        if (Schema::hasTable('module_member_signin')) {
            return;
        }

        Schema::create('module_member_signin', function (Blueprint $table) {
            if (DB::connection()->getDriverName() === 'mysql') {
                $charset = DatabaseCharsetResolver::resolveConfiguredCharset() ?: 'utf8mb4';
                $collation = DatabaseCharsetResolver::resolveRuntimeCollation() ?: DatabaseCharsetResolver::resolveConfigCollation('mysql', $charset, null);
                $table->charset = $charset;
                if (!empty($collation)) {
                    $table->collation = $collation;
                }
            }
            $table->increments('id');
            $table->text('order_num')->nullable();
            $table->integer('uid');
            $table->string('day', 255);
            $table->string('tig', 255);
            $table->integer('point')->nullable()->default(0);
            $table->text('remark')->nullable();
            $table->text('created_at')->nullable();
            $table->text('updated_at')->nullable();
            $table->index([
    'tig',
], 'member_signin_tig_idx');
            $table->index([
    'day',
], 'member_signin_day_idx');
            $table->index([
    'uid',
], 'member_signin_uid_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('module_member_signin');
    }

    private function supportsFullTextIndexes(): bool
    {
        return DB::connection()->getDriverName() === 'mysql';
    }
};
